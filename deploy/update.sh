#!/usr/bin/env bash
#
# SiedeStudios — deploy the latest code to an installed server.
#
#   sudo bash /var/www/siedestudios.co.uk/public_html/deploy/update.sh
#
# Puts the site into maintenance mode, pulls the branch, installs
# dependencies, rebuilds assets, migrates and re-warms caches.

set -Eeuo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
BRANCH="${BRANCH:-$(git -C "$APP_DIR" rev-parse --abbrev-ref HEAD)}"
WEB_USER="www-data"
PHP_VERSION="${PHP_VERSION:-$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')}"

[[ $EUID -eq 0 ]] || { echo "Please run as root (sudo)." >&2; exit 1; }

artisan() { sudo -u "$WEB_USER" php "$APP_DIR/artisan" "$@"; }
step() { printf '\n\e[38;5;161m==>\e[0m \e[1m%s\e[0m\n' "$*"; }

trap 'artisan up > /dev/null 2>&1 || true; echo "Update failed on line $LINENO — the site has been brought back up." >&2' ERR

step "Maintenance mode"
artisan down --retry=15 --refresh=15 > /dev/null || true

step "Pulling ${BRANCH}"
git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
sudo -u "$WEB_USER" git -C "$APP_DIR" fetch --quiet origin "$BRANCH"
sudo -u "$WEB_USER" git -C "$APP_DIR" reset --quiet --hard "origin/${BRANCH}"
git -C "$APP_DIR" log -1 --format='    %h %s'

step "Dependencies & assets"
sudo -u "$WEB_USER" HOME=/tmp composer install --working-dir="$APP_DIR" --no-dev --optimize-autoloader --no-interaction --no-progress --quiet
sudo -u "$WEB_USER" HOME=/tmp bash -c "cd '$APP_DIR' && npm ci --no-audit --no-fund --loglevel=error && npm run build --silent" > /dev/null

step "Database & caches"
artisan migrate --force --no-interaction
artisan optimize:clear > /dev/null
artisan optimize > /dev/null
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || true
artisan up > /dev/null

printf '\n\e[32m✓ Deployed %s\e[0m\n' "$(git -C "$APP_DIR" log -1 --format='%h')"
