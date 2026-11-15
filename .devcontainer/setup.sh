#!/usr/bin/env bash
# One-time setup for GitHub Codespaces / dev containers: installs dependencies,
# builds the front-end and loads demo content into the MySQL container.
set -euo pipefail
cd "$(dirname "$0")/.."

DB_HOST="${DB_HOST:-db}"
DEMO_ADMIN_ID="000000000000000001"

set_env() {
    if grep -qE "^#?\s*$1=" .env; then sed -i -E "s|^#?\s*$1=.*|$1=$2|" .env; else echo "$1=$2" >> .env; fi
}

[[ -f .env ]] || cp .env.example .env
if [[ -n "${CODESPACE_NAME:-}" ]]; then
    APP_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
else
    APP_URL="http://localhost:8000"
fi
set_env APP_ENV local
set_env APP_DEBUG true
set_env APP_URL "$APP_URL"
set_env DB_CONNECTION mysql
set_env DB_HOST "$DB_HOST"
set_env DB_PORT 3306
set_env DB_DATABASE siedestudios
set_env DB_USERNAME siedestudios
set_env DB_PASSWORD siedestudios
set_env ADMIN_DISCORD_IDS "$DEMO_ADMIN_ID"
set_env DEV_LOGIN true
set_env DISCORD_REDIRECT_URI "${APP_URL}/auth/discord/callback"

composer install --no-interaction --no-progress
npm ci --no-audit --no-fund
npm run build
grep -qE '^APP_KEY=.+' .env || php artisan key:generate --force

echo "Waiting for MySQL…"
for _ in $(seq 1 60); do
    mysqladmin ping -h"$DB_HOST" -usiedestudios -psiedestudios --silent 2>/dev/null && break
    sleep 2
done

php artisan migrate:fresh --seed --force
php artisan db:seed --class=DemoSeeder --force
php artisan storage:link --force > /dev/null 2>&1 || true
php artisan optimize:clear > /dev/null

echo
echo "Ready: ${APP_URL}  (admin: ${APP_URL}/admin → 'Demo sign-in')"
