#!/usr/bin/env bash
#
# Deploys the latest code. Run on the server as the app user:
#   bash /var/www/formsathi/current/deploy/deploy.sh          (after the first deploy)
#   bash deploy.sh                                            (first time, from a copy)
#
# Each release gets its own folder; the "current" symlink switches only
# after everything has built, so visitors never see a half-deployed site.
# If the health check fails afterwards, it switches straight back.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/formsathi}"
REPO="${REPO:-https://github.com/Kanthadey/FormSathi.git}"
BRANCH="${BRANCH:-master}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"

RELEASE="$(date +%Y%m%d%H%M%S)"
RELEASE_DIR="$APP_DIR/releases/$RELEASE"
SHARED="$APP_DIR/shared"
PREVIOUS="$(readlink -f "$APP_DIR/current" 2>/dev/null || true)"

step() { echo; echo "==> $*"; }
fail() { echo "!! $*" >&2; exit 1; }

[[ "$(id -un)" == "formsathi" ]] || fail "Run as the formsathi user: sudo -u formsathi bash deploy.sh"
[[ -f "$SHARED/.env" ]] || fail "$SHARED/.env is missing. Copy deploy/.env.production.example there and fill it in."
grep -q '^APP_KEY=base64:' "$SHARED/.env" || fail "APP_KEY is empty in $SHARED/.env (see DEPLOY.md, step 6)."

step "Fetching $BRANCH into releases/$RELEASE"
git clone --quiet --depth 1 --branch "$BRANCH" "$REPO" "$RELEASE_DIR"
cd "$RELEASE_DIR"
echo "Commit: $(git rev-parse --short HEAD) $(git log -1 --format=%s)"

step "Linking shared .env and storage (papers live there, outside every release)"
ln -s "$SHARED/.env" .env
rm -rf storage
ln -s "$SHARED/storage" storage

step "PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --quiet

step "Frontend build"
npm ci --no-audit --no-fund --silent
npm run build
rm -rf node_modules

step "Database migrations and form templates"
php artisan migrate --force
php artisan db:seed --class=FormTemplateSeeder --force

step "Caching config, routes and views"
php artisan optimize

step "Switching to the new release"
ln -sfn "$RELEASE_DIR" "$APP_DIR/current.tmp"
mv -Tf "$APP_DIR/current.tmp" "$APP_DIR/current"

sudo /usr/bin/systemctl reload php8.3-fpm
sudo /usr/bin/systemctl restart formsathi-queue.service
sudo /usr/bin/systemctl start formsathi-scheduler.timer

step "Health check"
APP_URL="$(grep -E '^APP_URL=' "$SHARED/.env" | cut -d= -f2- | tr -d '"')"
HOST="$(echo "$APP_URL" | sed -E 's#^https?://##; s#/.*$##')"
healthy=false
for _ in 1 2 3 4 5; do
    if curl -fsS --max-time 10 -o /dev/null --resolve "$HOST:443:127.0.0.1" "https://$HOST/up" \
        || curl -fsS --max-time 10 -o /dev/null -H "Host: $HOST" "http://127.0.0.1/up"; then
        healthy=true
        break
    fi
    sleep 3
done

if [[ "$healthy" != true ]]; then
    if [[ -n "$PREVIOUS" && -d "$PREVIOUS" ]]; then
        echo "!! Health check failed; switching back to $(basename "$PREVIOUS")." >&2
        ln -sfn "$PREVIOUS" "$APP_DIR/current.tmp"
        mv -Tf "$APP_DIR/current.tmp" "$APP_DIR/current"
        sudo /usr/bin/systemctl reload php8.3-fpm
        sudo /usr/bin/systemctl restart formsathi-queue.service
        echo "!! Database migrations from this release were NOT undone. Check storage/logs/laravel.log." >&2
    fi
    fail "Deploy of $RELEASE failed the health check."
fi

step "Removing old releases (keeping $KEEP_RELEASES)"
cd "$APP_DIR/releases"
ls -1t | tail -n +"$((KEEP_RELEASES + 1))" | xargs -r rm -rf

echo
echo "Deployed release $RELEASE."
