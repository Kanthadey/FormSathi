#!/usr/bin/env bash
#
# One-time setup of a fresh Ubuntu 24.04 LTS server for FormSathi.
# Run as root:   sudo bash server-setup.sh formsathi.example.com
#
# Installs Nginx, PHP 8.3-FPM, PostgreSQL 16, Node.js 22, Composer and
# Tesseract (Bangla + English), creates the app user, database and folders,
# and turns on the firewall. Safe to re-run: each step checks before acting.

set -euo pipefail

DOMAIN="${1:?Usage: server-setup.sh <domain>}"
APP_USER="formsathi"
APP_DIR="/var/www/formsathi"
DB_NAME="formsathi"
DB_USER="formsathi"

if [[ $EUID -ne 0 ]]; then
    echo "Run as root (sudo)." >&2
    exit 1
fi

step() { echo; echo "==> $*"; }

step "System packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get upgrade -yq
apt-get install -yq \
    nginx git unzip curl ufw cron gnupg ca-certificates \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl \
    php8.3-zip php8.3-intl php8.3-bcmath php8.3-gd php8.3-opcache \
    postgresql postgresql-contrib \
    tesseract-ocr tesseract-ocr-ben tesseract-ocr-eng \
    certbot python3-certbot-nginx

step "Node.js 22 (to build the frontend)"
if ! command -v node >/dev/null || [[ "$(node -v)" != v22* ]]; then
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
    apt-get install -yq nodejs
fi

step "Composer"
if ! command -v composer >/dev/null; then
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

step "App user and folders"
id "$APP_USER" >/dev/null 2>&1 || adduser --disabled-password --gecos "" "$APP_USER"
usermod -aG www-data "$APP_USER"
mkdir -p "$APP_DIR"/{releases,shared/storage}
mkdir -p "$APP_DIR"/shared/storage/{app/private,framework/{cache/data,sessions,views},logs}
chown -R "$APP_USER":www-data "$APP_DIR"
chmod -R 2775 "$APP_DIR"/shared/storage
# Papers live here, encrypted. Nobody but the app needs to read them.
chmod 2770 "$APP_DIR"/shared/storage/app/private

# The first deploy runs from here (the app user can't read /root);
# later deploys use the copy inside each release.
install -o "$APP_USER" -g "$APP_USER" -m 755 "$(dirname "$0")/deploy.sh" "/home/$APP_USER/deploy.sh"

step "PostgreSQL database"
if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    DB_PASSWORD="$(openssl rand -base64 30 | tr -d '/+=' | cut -c1-32)"
    sudo -u postgres psql -c "CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASSWORD';"
    sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;"
    echo "$DB_PASSWORD" > /root/formsathi-db-password
    chmod 600 /root/formsathi-db-password
    echo "Database password saved to /root/formsathi-db-password (put it in .env, then delete the file)."
else
    echo "Database user exists; leaving it alone."
fi

step "PHP settings and the app's own PHP-FPM pool"
cp "$(dirname "$0")/php/formsathi.ini" /etc/php/8.3/fpm/conf.d/99-formsathi.ini
cp "$(dirname "$0")/php/formsathi.ini" /etc/php/8.3/cli/conf.d/99-formsathi.ini
cp "$(dirname "$0")/php/formsathi-pool.conf" /etc/php/8.3/fpm/pool.d/formsathi.conf
php-fpm8.3 -t
systemctl restart php8.3-fpm

step "Nginx site"
sed "s/formsathi.example.com/$DOMAIN/g" "$(dirname "$0")/nginx/formsathi.conf" > /etc/nginx/sites-available/formsathi
ln -sf /etc/nginx/sites-available/formsathi /etc/nginx/sites-enabled/formsathi
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

step "Queue worker, scheduler and nightly backup"
cp "$(dirname "$0")"/systemd/formsathi-* /etc/systemd/system/
systemctl daemon-reload
# The worker and scheduler start with the first release (deploy.sh starts them).
systemctl enable formsathi-queue.service formsathi-scheduler.timer
systemctl enable --now formsathi-backup.timer

step "Let the app user restart its own services, nothing else"
cat > /etc/sudoers.d/formsathi <<SUDO
$APP_USER ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm, /usr/bin/systemctl restart formsathi-queue.service, /usr/bin/systemctl start formsathi-scheduler.timer
SUDO
chmod 440 /etc/sudoers.d/formsathi
visudo -cf /etc/sudoers.d/formsathi

step "Firewall"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo
echo "Server ready. Next:"
echo "  1. Point DNS for $DOMAIN to this server."
echo "  2. sudo certbot --nginx -d $DOMAIN        (HTTPS)"
echo "  3. As $APP_USER: create $APP_DIR/shared/.env from deploy/.env.production.example"
echo "  4. sudo -u $APP_USER bash /home/$APP_USER/deploy.sh   (first release)"
