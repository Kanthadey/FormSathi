#!/usr/bin/env bash
#
# Nightly backup of the database and the (already encrypted) paper files,
# run by formsathi-backup.timer as the app user.
#
# Each backup is encrypted again with a passphrase kept apart from the app.
# Backups older than 30 days are deleted, which keeps the privacy-policy
# promise that a deleted account is gone from backups within 30 days.
#
# This only writes to BACKUP_DIR on the same server. Copy that folder off
# the machine every night (see DEPLOY.md, "Backups"), to storage inside
# Bangladesh, or a dead disk takes the backups with it.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/formsathi}"
BACKUP_DIR="${BACKUP_DIR:-/home/formsathi/backups}"
PASSPHRASE_FILE="${PASSPHRASE_FILE:-/home/formsathi/.backup-passphrase}"
RETENTION_DAYS=30

ENV_FILE="$APP_DIR/shared/.env"
env_value() { grep -E "^$1=" "$ENV_FILE" | cut -d= -f2- | tr -d '"'; }

[[ -f "$PASSPHRASE_FILE" ]] || { echo "Missing $PASSPHRASE_FILE (see DEPLOY.md, Backups)." >&2; exit 1; }

STAMP="$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"
umask 077

encrypt() {
    gpg --batch --yes --quiet --symmetric --cipher-algo AES256 \
        --passphrase-file "$PASSPHRASE_FILE" --output "$1"
}

echo "Database..."
PGPASSWORD="$(env_value DB_PASSWORD)" pg_dump \
    --host="$(env_value DB_HOST)" --port="$(env_value DB_PORT)" \
    --username="$(env_value DB_USERNAME)" --format=custom "$(env_value DB_DATABASE)" \
    | encrypt "$BACKUP_DIR/db-$STAMP.dump.gpg"

echo "Paper files..."
tar -C "$APP_DIR/shared/storage/app" -cz private \
    | encrypt "$BACKUP_DIR/papers-$STAMP.tar.gz.gpg"

echo "Removing backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -type f -name '*.gpg' -mtime +"$RETENTION_DAYS" -delete

echo "Backup $STAMP done."
