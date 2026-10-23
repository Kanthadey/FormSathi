#!/usr/bin/env bash
#
# Switches back to the release before the current one. Run as the app user:
#   bash /var/www/formsathi/current/deploy/rollback.sh
#
# Code only: database migrations are not undone. If the bad release added a
# migration the old code can't cope with, run `php artisan migrate:rollback`
# from the bad release's folder first.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/formsathi}"
CURRENT="$(readlink -f "$APP_DIR/current")"
TARGET="$(ls -1dt "$APP_DIR"/releases/*/ | sed 's#/$##' | grep -vx "$CURRENT" | head -n 1)"

if [[ -z "$TARGET" ]]; then
    echo "No earlier release to roll back to." >&2
    exit 1
fi

echo "Rolling back: $(basename "$CURRENT") -> $(basename "$TARGET")"
ln -sfn "$TARGET" "$APP_DIR/current.tmp"
mv -Tf "$APP_DIR/current.tmp" "$APP_DIR/current"

sudo /usr/bin/systemctl reload php8.3-fpm
sudo /usr/bin/systemctl restart formsathi-queue.service

echo "Done. Now serving $(basename "$TARGET")."
