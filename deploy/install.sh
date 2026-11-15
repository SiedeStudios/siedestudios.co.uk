#!/usr/bin/env bash
#
# SiedeStudios — server installer
#
# Installs everything the site needs on a fresh Ubuntu (22.04/24.04) or
# Debian (12/13) server, clones the repository into
# /var/www/siedestudios.co.uk/public_html, configures Nginx + PHP-FPM,
# MariaDB, the Laravel scheduler, and issues a Let's Encrypt SSL
# certificate with automatic renewal.
#
# Safe to re-run: existing .env values, database and certificates are kept.
#
# Usage (as root):
#   git clone https://github.com/SiedeStudios/siedestudios.co.uk.git /var/www/siedestudios.co.uk/public_html
#   bash /var/www/siedestudios.co.uk/public_html/deploy/install.sh
#
# Every prompt can be answered up-front with environment variables, e.g.
#   DOMAIN=siedestudios.co.uk LE_EMAIL=you@example.com bash deploy/install.sh
#
# Variables: DOMAIN, WWW (yes|no), LE_EMAIL, APP_DIR, REPO_URL, BRANCH,
#            DB_DRIVER (mysql|sqlite), DB_NAME, DB_USER, DB_PASS,
#            DISCORD_CLIENT_ID, DISCORD_CLIENT_SECRET, ADMIN_DISCORD_IDS,
#            PHP_VERSION (default 8.3), SKIP_SSL=1, NONINTERACTIVE=1

set -Eeuo pipefail

# ---------------------------------------------------------------------------
# Defaults
# ---------------------------------------------------------------------------
DOMAIN="${DOMAIN:-siedestudios.co.uk}"
APP_DIR="${APP_DIR:-/var/www/siedestudios.co.uk/public_html}"
REPO_URL="${REPO_URL:-https://github.com/SiedeStudios/siedestudios.co.uk.git}"
BRANCH="${BRANCH:-main}"
PHP_VERSION="${PHP_VERSION:-8.3}"
NODE_MAJOR="${NODE_MAJOR:-22}"
DB_DRIVER="${DB_DRIVER:-mysql}"
DB_NAME="${DB_NAME:-siedestudios}"
DB_USER="${DB_USER:-siedestudios}"
WEB_USER="www-data"
NONINTERACTIVE="${NONINTERACTIVE:-0}"

# ---------------------------------------------------------------------------
# Output helpers
# ---------------------------------------------------------------------------
if [[ -t 1 ]]; then
    C_RED=$'\e[31m'; C_GRN=$'\e[32m'; C_YLW=$'\e[33m'; C_CRM=$'\e[38;5;161m'; C_DIM=$'\e[2m'; C_B=$'\e[1m'; C_0=$'\e[0m'
else
    C_RED=''; C_GRN=''; C_YLW=''; C_CRM=''; C_DIM=''; C_B=''; C_0=''
fi

step() { printf '\n%s==>%s %s%s%s\n' "$C_CRM" "$C_0" "$C_B" "$*" "$C_0"; }
info() { printf '    %s\n' "$*"; }
ok()   { printf '    %s✓%s %s\n' "$C_GRN" "$C_0" "$*"; }
warn() { printf '    %s!%s %s\n' "$C_YLW" "$C_0" "$*"; }
die()  { printf '\n%s✗ %s%s\n' "$C_RED" "$*" "$C_0" >&2; exit 1; }

trap 'die "Installer failed on line $LINENO. Fix the error above and re-run — completed steps are skipped."' ERR

ask() {
    # ask VAR "Question" "default" [secret]
    local __var="$1" __q="$2" __def="${3:-}" __secret="${4:-}" __ans
    if [[ -n "${!__var:-}" ]]; then return; fi
    if [[ "$NONINTERACTIVE" == "1" || ! -t 0 ]]; then
        printf -v "$__var" '%s' "$__def"
        return
    fi
    if [[ -n "$__secret" ]]; then
        read -r -s -p "    $__q${__def:+ [$__def]}: " __ans; echo
    else
        read -r -p "    $__q${__def:+ [$__def]}: " __ans
    fi
    printf -v "$__var" '%s' "${__ans:-$__def}"
}

set_env() {
    # set_env KEY VALUE — add or replace a key in .env (value is quoted when needed)
    local key="$1" value="$2" file="$APP_DIR/.env" line
    if [[ "$value" =~ [[:space:]\#\"\$] ]]; then
        value="\"${value//\"/\\\"}\""
    fi
    line="${key}=${value}"
    if grep -qE "^#?\s*${key}=" "$file"; then
        local escaped
        escaped=$(printf '%s' "$line" | sed -e 's/[\/&|]/\\&/g')
        sed -i -E "s|^#?\s*${key}=.*|${escaped}|" "$file"
    else
        printf '%s\n' "$line" >> "$file"
    fi
}

get_env() {
    grep -E "^${1}=" "$APP_DIR/.env" 2>/dev/null | head -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

artisan() { sudo -u "$WEB_USER" php "$APP_DIR/artisan" "$@"; }

# ---------------------------------------------------------------------------
# Pre-flight
# ---------------------------------------------------------------------------
[[ $EUID -eq 0 ]] || die "Please run as root (sudo bash deploy/install.sh)."
[[ -r /etc/os-release ]] || die "Unsupported system: /etc/os-release not found."
# shellcheck disable=SC1091
. /etc/os-release
case "${ID:-}" in
    ubuntu|debian) ;;
    *) die "This installer supports Ubuntu and Debian. Detected: ${PRETTY_NAME:-unknown}." ;;
esac

cat <<BANNER

${C_CRM}   ███████╗${C_0}  SiedeStudios
${C_CRM}   ██╔════╝${C_0}  Server installer
${C_CRM}   ███████╗${C_0}
${C_CRM}   ╚════██║${C_0}  ${C_DIM}${PRETTY_NAME}${C_0}
${C_CRM}   ███████║${C_0}  ${C_DIM}Target: ${APP_DIR}${C_0}
${C_CRM}   ╚══════╝${C_0}

BANNER

step "Configuration"
ask DOMAIN "Domain name" "$DOMAIN"
ask WWW "Also serve www.${DOMAIN}? (yes/no)" "yes"
ask LE_EMAIL "Email for Let's Encrypt expiry notices" ""
ask DB_DRIVER "Database (mysql/sqlite)" "$DB_DRIVER"

EXISTING_ENV=0
[[ -f "$APP_DIR/.env" ]] && EXISTING_ENV=1

if [[ $EXISTING_ENV -eq 0 ]]; then
    info "${C_DIM}Discord login: create an application at https://discord.com/developers/applications${C_0}"
    info "${C_DIM}and add the redirect URI https://${DOMAIN}/auth/discord/callback under OAuth2.${C_0}"
    ask DISCORD_CLIENT_ID "Discord OAuth client ID (blank to set later)" ""
    ask DISCORD_CLIENT_SECRET "Discord OAuth client secret" "" secret
    info "${C_DIM}Your Discord user ID: Discord → Settings → Advanced → Developer Mode, then right-click your name → Copy User ID.${C_0}"
    ask ADMIN_DISCORD_IDS "Admin Discord user ID(s), comma separated" ""
fi

[[ "$WWW" =~ ^[Yy] ]] && SERVER_NAMES="$DOMAIN www.$DOMAIN" || SERVER_NAMES="$DOMAIN"
[[ "$DB_DRIVER" == "mysql" || "$DB_DRIVER" == "sqlite" ]] || die "DB_DRIVER must be mysql or sqlite."

ok "Domain: ${SERVER_NAMES}"
ok "App directory: ${APP_DIR}"

# ---------------------------------------------------------------------------
# System packages
# ---------------------------------------------------------------------------
export DEBIAN_FRONTEND=noninteractive

step "Installing system packages"
apt-get update -qq
apt-get install -y -qq ca-certificates curl gnupg git unzip lsb-release software-properties-common > /dev/null 2>&1 \
    || apt-get install -y -qq ca-certificates curl gnupg git unzip lsb-release > /dev/null
ok "Base tools"

if ! apt-cache policy "php${PHP_VERSION}-fpm" 2>/dev/null | grep -q 'Candidate: [0-9]'; then
    info "PHP ${PHP_VERSION} isn't in the default repositories — adding the Sury PHP repository"
    if [[ "$ID" == "ubuntu" ]]; then
        add-apt-repository -y ppa:ondrej/php > /dev/null
    else
        curl -fsSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
        echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ ${VERSION_CODENAME} main" > /etc/apt/sources.list.d/sury-php.list
    fi
    apt-get update -qq
fi

PHP_PACKAGES=(fpm cli common mbstring xml curl zip gd intl bcmath sqlite3 opcache)
[[ "$DB_DRIVER" == "mysql" ]] && PHP_PACKAGES+=(mysql)
apt-get install -y -qq nginx certbot python3-certbot-nginx "${PHP_PACKAGES[@]/#/php${PHP_VERSION}-}" > /dev/null
ok "Nginx, Certbot and PHP ${PHP_VERSION}"

if [[ "$DB_DRIVER" == "mysql" ]] && ! command -v mysql > /dev/null; then
    apt-get install -y -qq mariadb-server > /dev/null
    systemctl enable --now mariadb > /dev/null
    ok "MariaDB"
fi

if ! command -v node > /dev/null || [[ "$(node -p 'process.versions.node.split(".")[0]')" -lt 20 ]]; then
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash - > /dev/null
    apt-get install -y -qq nodejs > /dev/null
fi
ok "Node.js $(node -v)"

if ! command -v composer > /dev/null; then
    EXPECTED="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSLo /tmp/composer-setup.php https://getcomposer.org/installer
    ACTUAL="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
    [[ "$EXPECTED" == "$ACTUAL" ]] || die "Composer installer checksum mismatch."
    php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi
ok "Composer $(composer --version 2>/dev/null | awk '{print $3}')"

# ---------------------------------------------------------------------------
# PHP tuning — larger uploads for screenshots, sensible production limits
# ---------------------------------------------------------------------------
step "Configuring PHP-FPM"
PHP_INI="/etc/php/${PHP_VERSION}/fpm/conf.d/99-siedestudios.ini"
cat > "$PHP_INI" <<'INI'
; Managed by deploy/install.sh
upload_max_filesize = 16M
post_max_size = 64M
max_file_uploads = 25
memory_limit = 256M
expose_php = Off
opcache.enable = 1
opcache.validate_timestamps = 1
opcache.revalidate_freq = 2
INI
systemctl enable --now "php${PHP_VERSION}-fpm" > /dev/null
systemctl restart "php${PHP_VERSION}-fpm"
ok "Upload limit 16 MB per file, OPcache on"

# ---------------------------------------------------------------------------
# Code
# ---------------------------------------------------------------------------
step "Fetching the code"
mkdir -p "$(dirname "$APP_DIR")"
git config --global --add safe.directory "$APP_DIR" 2>/dev/null || true
if [[ -d "$APP_DIR/.git" ]]; then
    info "Repository already present — pulling latest ${BRANCH}"
    git -C "$APP_DIR" fetch --quiet origin "$BRANCH"
    git -C "$APP_DIR" checkout --quiet "$BRANCH"
    git -C "$APP_DIR" pull --quiet --ff-only origin "$BRANCH"
elif [[ -d "$APP_DIR" && -n "$(ls -A "$APP_DIR" 2>/dev/null)" ]]; then
    die "$APP_DIR exists and isn't a git checkout. Move its contents aside and re-run."
else
    git clone --quiet --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
ok "$(git -C "$APP_DIR" log -1 --format='%h %s')"

# ---------------------------------------------------------------------------
# Database
# ---------------------------------------------------------------------------
step "Database"
if [[ "$DB_DRIVER" == "mysql" ]]; then
    if [[ $EXISTING_ENV -eq 1 && -n "$(get_env DB_PASSWORD)" ]]; then
        DB_PASS="$(get_env DB_PASSWORD)"
        DB_NAME="$(get_env DB_DATABASE)"
        DB_USER="$(get_env DB_USERNAME)"
        ok "Using existing credentials from .env"
    else
        DB_PASS="${DB_PASS:-$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 32)}"
        mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
        ok "MariaDB database '${DB_NAME}' and user '${DB_USER}'"
    fi
else
    touch "$APP_DIR/database/database.sqlite"
    ok "SQLite at database/database.sqlite"
fi

# ---------------------------------------------------------------------------
# Environment file
# ---------------------------------------------------------------------------
step "Environment (.env)"
if [[ $EXISTING_ENV -eq 0 ]]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    set_env DISCORD_CLIENT_ID "${DISCORD_CLIENT_ID:-}"
    set_env DISCORD_CLIENT_SECRET "${DISCORD_CLIENT_SECRET:-}"
    set_env ADMIN_DISCORD_IDS "${ADMIN_DISCORD_IDS:-}"
    ok "Created .env"
else
    ok "Keeping existing .env"
fi

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://${DOMAIN}"
set_env LOG_STACK daily
set_env LOG_LEVEL warning
set_env SESSION_SECURE_COOKIE true
set_env INERTIA_SSR_ENABLED false
set_env DISCORD_REDIRECT_URI "https://${DOMAIN}/auth/discord/callback"
if [[ "$DB_DRIVER" == "mysql" ]]; then
    set_env DB_CONNECTION mysql
    set_env DB_HOST 127.0.0.1
    set_env DB_PORT 3306
    set_env DB_DATABASE "$DB_NAME"
    set_env DB_USERNAME "$DB_USER"
    set_env DB_PASSWORD "$DB_PASS"
else
    set_env DB_CONNECTION sqlite
fi
chmod 640 "$APP_DIR/.env"

# ---------------------------------------------------------------------------
# Build
# ---------------------------------------------------------------------------
step "Installing dependencies and building assets"
chown -R "$WEB_USER:$WEB_USER" "$APP_DIR"
export COMPOSER_ALLOW_SUPERUSER=1
sudo -u "$WEB_USER" HOME=/tmp composer install --working-dir="$APP_DIR" --no-dev --optimize-autoloader --no-interaction --no-progress --quiet
ok "Composer packages"
sudo -u "$WEB_USER" HOME=/tmp bash -c "cd '$APP_DIR' && npm ci --no-audit --no-fund --loglevel=error && npm run build --silent" > /dev/null
ok "Front-end built"

[[ -n "$(get_env APP_KEY)" ]] || artisan key:generate --force --no-interaction > /dev/null
ok "Application key"

FIRST_RUN=0
artisan migrate:status > /dev/null 2>&1 || FIRST_RUN=1
artisan migrate --force --no-interaction > /dev/null
ok "Migrations"
if [[ $FIRST_RUN -eq 1 ]]; then
    artisan db:seed --force --no-interaction > /dev/null
    ok "Seeded starter services and projects"
fi

artisan storage:link --force > /dev/null 2>&1 || true
artisan optimize > /dev/null
ok "Caches warmed"

chmod 640 "$APP_DIR/.env"
chmod +x "$APP_DIR/artisan" "$APP_DIR/deploy/"*.sh
chown -R "$WEB_USER:$WEB_USER" "$APP_DIR"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
[[ "$DB_DRIVER" == "sqlite" ]] && chmod 664 "$APP_DIR/database/database.sqlite"
ok "Permissions"

# ---------------------------------------------------------------------------
# Nginx
# ---------------------------------------------------------------------------
step "Configuring Nginx"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}.conf"
if [[ ! -f "$NGINX_CONF" ]] || ! grep -q 'managed by Certbot' "$NGINX_CONF"; then
    cat > "$NGINX_CONF" <<NGINX
# Managed by deploy/install.sh — Certbot adds the SSL server block below.
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_NAMES};
    root ${APP_DIR}/public;
    index index.php;
    charset utf-8;

    client_max_body_size 64M;
    server_tokens off;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Fingerprinted Vite assets can be cached forever.
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    location ~* \.(?:png|jpe?g|gif|webp|svg|ico|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public";
        access_log off;
        try_files \$uri /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 60;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
    ok "Wrote ${NGINX_CONF}"
else
    ok "Keeping existing SSL-enabled config"
fi
ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${DOMAIN}.conf"
rm -f /etc/nginx/sites-enabled/default
nginx -t > /dev/null 2>&1 || { nginx -t; die "Nginx configuration test failed."; }
systemctl enable --now nginx > /dev/null
systemctl reload nginx
ok "Nginx serving http://${DOMAIN}"

if command -v ufw > /dev/null && ufw status | grep -q 'Status: active'; then
    ufw allow 'Nginx Full' > /dev/null
    ok "Firewall: opened ports 80 and 443"
fi

# ---------------------------------------------------------------------------
# SSL (Let's Encrypt)
# ---------------------------------------------------------------------------
step "SSL certificate"
if [[ "${SKIP_SSL:-0}" == "1" ]]; then
    warn "Skipped (SKIP_SSL=1). Run: certbot --nginx -d ${SERVER_NAMES// / -d }"
elif [[ -z "${LE_EMAIL:-}" ]]; then
    warn "No email given — skipping. Re-run with LE_EMAIL=you@example.com to issue the certificate."
else
    SERVER_IP="$(curl -fsS4 --max-time 5 https://api.ipify.org 2>/dev/null || true)"
    DNS_OK=1
    for name in $SERVER_NAMES; do
        RESOLVED="$(getent ahostsv4 "$name" | awk 'NR==1{print $1}')"
        if [[ -z "$RESOLVED" ]]; then
            warn "${name} does not resolve yet"; DNS_OK=0
        elif [[ -n "$SERVER_IP" && "$RESOLVED" != "$SERVER_IP" ]]; then
            warn "${name} points to ${RESOLVED}, but this server is ${SERVER_IP}"; DNS_OK=0
        fi
    done

    if [[ $DNS_OK -eq 0 ]]; then
        warn "Point your DNS A records at this server, then re-run this script to issue the certificate."
    else
        CERT_ARGS=()
        for name in $SERVER_NAMES; do CERT_ARGS+=(-d "$name"); done
        certbot --nginx "${CERT_ARGS[@]}" \
            --non-interactive --agree-tos --email "$LE_EMAIL" \
            --redirect --keep-until-expiring --quiet
        ok "Certificate issued for ${SERVER_NAMES} — HTTP now redirects to HTTPS"

        # Renewal runs from certbot's systemd timer (or cron on older systems).
        systemctl enable --now certbot.timer > /dev/null 2>&1 || true
        mkdir -p /etc/letsencrypt/renewal-hooks/deploy
        printf '#!/bin/sh\nsystemctl reload nginx\n' > /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
        chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
        certbot renew --dry-run --quiet && ok "Automatic renewal verified (dry run)"
    fi
fi

# ---------------------------------------------------------------------------
# Scheduler
# ---------------------------------------------------------------------------
step "Scheduler"
echo "* * * * * ${WEB_USER} cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/siedestudios
chmod 644 /etc/cron.d/siedestudios
ok "Cron entry /etc/cron.d/siedestudios (prunes expired shares daily)"

# ---------------------------------------------------------------------------
# Done
# ---------------------------------------------------------------------------
PROTO="http"; [[ -d "/etc/letsencrypt/live/${DOMAIN}" ]] && PROTO="https"
cat <<DONE

${C_GRN}${C_B}  SiedeStudios is installed.${C_0}

  Site            ${PROTO}://${DOMAIN}
  Admin panel     ${PROTO}://${DOMAIN}/admin
  Code            ${APP_DIR}

  ${C_B}Next steps${C_0}
  1. Discord Developer Portal → your app → OAuth2 → Redirects, add:
       https://${DOMAIN}/auth/discord/callback
  2. Sign in at /admin, then open Settings → Payments and add your PayPal
     business email (IPN URL: https://${DOMAIN}/paypal/ipn).
  3. Optional: Settings → Notifications → Discord webhook for alerts.

  ${C_DIM}Deploy updates later with:  sudo bash ${APP_DIR}/deploy/update.sh${C_0}
  ${C_DIM}Change Discord keys or admin IDs in ${APP_DIR}/.env, then run update.sh.${C_0}

DONE
