#!/usr/bin/env bash
#
# SiedeStudios — server installer
#
# Installs everything the site needs on a fresh Ubuntu (22.04/24.04) or
# Debian (12/13) server: points the domain at this server through the
# Cloudflare API, clones the repository into /var/www/siedestudios, creates
# its own MariaDB database, configures Nginx + PHP-FPM and the Laravel
# scheduler, and issues a Let's Encrypt SSL certificate with auto-renewal.
#
# Safe to re-run: existing .env values, database and certificates are kept.
#
# Usage (as root):
#   git clone https://github.com/SiedeStudios/siedestudios.co.uk.git /var/www/siedestudios
#   bash /var/www/siedestudios/deploy/install.sh
#
# Every prompt can be answered up-front with environment variables, e.g.
#   DOMAIN=siedestudios.co.uk LE_EMAIL=you@example.com bash deploy/install.sh
#
# Variables: DOMAIN, WWW (yes|no), LE_EMAIL, APP_DIR, REPO_URL, BRANCH,
#            SERVER_IP (default 138.201.130.254), CF_API_TOKEN (Cloudflare
#            token with Zone → DNS → Edit), CF_PROXIED (yes|no, default no),
#            DB_NAME, DB_USER, DB_PASS, DB_ROOT_PASSWORD (only if root
#            can't log in via the local socket),
#            DISCORD_CLIENT_ID, DISCORD_CLIENT_SECRET, ADMIN_DISCORD_IDS,
#            PHP_VERSION (default 8.3), SKIP_SSL=1, NONINTERACTIVE=1

set -Eeuo pipefail

# ---------------------------------------------------------------------------
# Defaults
# ---------------------------------------------------------------------------
DOMAIN="${DOMAIN:-siedestudios.co.uk}"
APP_DIR="${APP_DIR:-/var/www/siedestudios}"
SERVER_IP="${SERVER_IP:-138.201.130.254}"
CF_API_BASE="${CF_API_BASE:-https://api.cloudflare.com/client/v4}"
CF_CREDENTIALS="/root/.secrets/cloudflare.ini"
REPO_URL="${REPO_URL:-https://github.com/SiedeStudios/siedestudios.co.uk.git}"
BRANCH="${BRANCH:-main}"
PHP_VERSION="${PHP_VERSION:-8.3}"
NODE_MAJOR="${NODE_MAJOR:-22}"
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

cf() {
    # cf METHOD /path [json-body] — Cloudflare API call; prints the JSON result,
    # returns non-zero (with Cloudflare's error message on stderr) on failure.
    local method="$1" path="$2" body="${3:-}" out
    local args=(-sS --max-time 30 -X "$method" "${CF_API_BASE}${path}"
        -H "Authorization: Bearer ${CF_API_TOKEN}" -H "Content-Type: application/json")
    [[ -n "$body" ]] && args+=(--data "$body")
    out="$(curl "${args[@]}")" || return 1
    if [[ "$(jq -r '.success' <<< "$out" 2> /dev/null)" != "true" ]]; then
        printf '    Cloudflare: %s\n' "$(jq -r '[.errors[]?.message] | join("; ")' <<< "$out" 2> /dev/null || echo "$out")" >&2
        return 1
    fi
    printf '%s' "$out"
}

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
ask SERVER_IP "Server IP the domain should point to" "$SERVER_IP"
[[ "$SERVER_IP" =~ ^([0-9]{1,3}\.){3}[0-9]{1,3}$ ]] || die "SERVER_IP must be an IPv4 address."

# Reuse the token saved for certificate renewals on re-runs.
if [[ -z "${CF_API_TOKEN:-}" && -f "$CF_CREDENTIALS" ]]; then
    CF_API_TOKEN="$(awk -F' *= *' '/dns_cloudflare_api_token/ {print $2}' "$CF_CREDENTIALS")"
fi
if [[ -z "${CF_API_TOKEN:-}" ]]; then
    info "${C_DIM}Cloudflare: My Profile → API Tokens → Create Token → \"Edit zone DNS\" template, limited to ${DOMAIN}.${C_0}"
    ask CF_API_TOKEN "Cloudflare API token (blank to manage DNS yourself)" "" secret
fi
if [[ -n "${CF_API_TOKEN:-}" ]]; then
    ask CF_PROXIED "Proxy through Cloudflare (orange cloud)? (yes/no)" "no"
fi

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

ok "Domain: ${SERVER_NAMES}"
ok "Points to: ${SERVER_IP}"
ok "App directory: ${APP_DIR}"

# ---------------------------------------------------------------------------
# System packages
# ---------------------------------------------------------------------------
export DEBIAN_FRONTEND=noninteractive

step "Installing system packages"
apt-get update -qq
apt-get install -y -qq ca-certificates curl gnupg git unzip jq lsb-release software-properties-common > /dev/null 2>&1 \
    || apt-get install -y -qq ca-certificates curl gnupg git unzip jq lsb-release > /dev/null
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

PHP_PACKAGES=(fpm cli common mysql mbstring xml curl zip gd intl bcmath opcache)
apt-get install -y -qq nginx certbot python3-certbot-nginx python3-certbot-dns-cloudflare "${PHP_PACKAGES[@]/#/php${PHP_VERSION}-}" > /dev/null
ok "Nginx, Certbot and PHP ${PHP_VERSION}"

# MariaDB server (reuses a MariaDB/MySQL server that's already installed).
if command -v mariadbd > /dev/null || command -v mysqld > /dev/null; then
    ok "Using the database server already installed"
else
    apt-get install -y -qq mariadb-server mariadb-client > /dev/null
    ok "Installed MariaDB"
fi
systemctl enable --now mariadb > /dev/null 2>&1 || systemctl enable --now mysql > /dev/null 2>&1 || true
DB_CLI="$(command -v mariadb || command -v mysql)" || die "MariaDB client not found."
DB_ADMIN="$(command -v mariadb-admin || command -v mysqladmin)" || die "mariadb-admin not found."
for _ in $(seq 1 30); do
    "$DB_ADMIN" ping --silent > /dev/null 2>&1 && break
    sleep 1
done
"$DB_ADMIN" ping --silent > /dev/null 2>&1 || die "MariaDB isn't running. Check: systemctl status mariadb"
ok "MariaDB is running ($("$DB_CLI" --version | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | tail -n1))"

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
# Cloudflare DNS — point the domain (and www) at this server
# ---------------------------------------------------------------------------
step "Cloudflare DNS"
THIS_IP="$(curl -fsS4 --max-time 5 https://api.ipify.org 2> /dev/null || true)"
if [[ -n "$THIS_IP" && "$THIS_IP" != "$SERVER_IP" ]]; then
    warn "This machine's public IP is ${THIS_IP}, but DNS will point to ${SERVER_IP}."
fi

ZONE_ID=""
PROXIED=false
if [[ -z "${CF_API_TOKEN:-}" ]]; then
    warn "No Cloudflare token — add A records for ${SERVER_NAMES} → ${SERVER_IP} yourself."
else
    [[ "${CF_PROXIED:-no}" =~ ^[Yy] ]] && PROXIED=true
    cf GET /user/tokens/verify > /dev/null || die "Cloudflare rejected the API token."

    # Find the zone: try the domain itself, then each parent domain.
    candidate="$DOMAIN"
    while [[ -z "$ZONE_ID" && "$candidate" == *.* ]]; do
        ZONE_ID="$(cf GET "/zones?name=${candidate}" | jq -r '.result[0].id // empty')" || die "Couldn't list Cloudflare zones."
        [[ -n "$ZONE_ID" ]] && ZONE_NAME="$candidate"
        candidate="${candidate#*.}"
    done
    [[ -n "$ZONE_ID" ]] || die "No Cloudflare zone found for ${DOMAIN} — check the token has access to it."
    ok "Zone ${ZONE_NAME}"

    for name in $SERVER_NAMES; do
        records="$(cf GET "/zones/${ZONE_ID}/dns_records?name=${name}&per_page=100")" || die "Couldn't read DNS records for ${name}."

        # Records that would send traffic elsewhere: CNAMEs (can't coexist with
        # an A record) and AAAA records pointing at the old server.
        while IFS=$'\t' read -r id type content; do
            [[ -z "$id" ]] && continue
            cf DELETE "/zones/${ZONE_ID}/dns_records/${id}" > /dev/null || die "Couldn't remove ${type} ${name}."
            info "Removed ${type} ${name} → ${content}"
        done < <(jq -r '.result[] | select(.type == "CNAME" or .type == "AAAA") | [.id, .type, .content] | @tsv' <<< "$records")

        payload="$(jq -nc --arg name "$name" --arg ip "$SERVER_IP" --argjson proxied "$PROXIED" \
            '{type: "A", name: $name, content: $ip, ttl: 1, proxied: $proxied}')"
        mapfile -t a_ids < <(jq -r '.result[] | select(.type == "A") | .id' <<< "$records")
        if (( ${#a_ids[@]} > 0 )); then
            cf PUT "/zones/${ZONE_ID}/dns_records/${a_ids[0]}" "$payload" > /dev/null || die "Couldn't update A ${name}."
            for id in "${a_ids[@]:1}"; do
                cf DELETE "/zones/${ZONE_ID}/dns_records/${id}" > /dev/null || die "Couldn't remove extra A ${name}."
            done
            ok "Updated A ${name} → ${SERVER_IP}$([[ $PROXIED == true ]] && echo ' (proxied)')"
        else
            cf POST "/zones/${ZONE_ID}/dns_records" "$payload" > /dev/null || die "Couldn't create A ${name}."
            ok "Created A ${name} → ${SERVER_IP}$([[ $PROXIED == true ]] && echo ' (proxied)')"
        fi
    done
fi

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
# MariaDB database + user
# ---------------------------------------------------------------------------
step "MariaDB database"
MYSQL_ROOT_CNF="$(mktemp)"
APP_DB_CNF="$(mktemp)"
chmod 600 "$MYSQL_ROOT_CNF" "$APP_DB_CNF"
trap 'rm -f "$MYSQL_ROOT_CNF" "$APP_DB_CNF"' EXIT

if [[ $EXISTING_ENV -eq 1 && "$(get_env DB_CONNECTION)" == "mysql" && -n "$(get_env DB_PASSWORD)" ]]; then
    DB_NAME="$(get_env DB_DATABASE)"
    DB_USER="$(get_env DB_USERNAME)"
    DB_PASS="$(get_env DB_PASSWORD)"
    ok "Reusing the database credentials in .env"
else
    DB_PASS="${DB_PASS:-$(openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 32)}"
fi
[[ "$DB_NAME" =~ ^[A-Za-z0-9_]+$ && "$DB_USER" =~ ^[A-Za-z0-9_]+$ ]] || die "DB_NAME and DB_USER may only contain letters, numbers and underscores."
[[ "$DB_PASS" != *\'* && "$DB_PASS" != *\\* && "$DB_PASS" != *\"* ]] || die "DB_PASS must not contain quotes or backslashes."

# Root access: socket auth (the Ubuntu/Debian default) or a root password.
if "$DB_CLI" --protocol=socket -uroot -e 'SELECT 1' > /dev/null 2>&1; then
    printf '[client]\nuser=root\nprotocol=socket\n' > "$MYSQL_ROOT_CNF"
else
    ask DB_ROOT_PASSWORD "MariaDB root password" "" secret
    printf '[client]\nuser=root\npassword="%s"\n' "${DB_ROOT_PASSWORD:-}" > "$MYSQL_ROOT_CNF"
    "$DB_CLI" --defaults-extra-file="$MYSQL_ROOT_CNF" -e 'SELECT 1' > /dev/null 2>&1 \
        || die "Couldn't log in to MariaDB as root. Re-run with DB_ROOT_PASSWORD=yourpassword."
fi

# Created for both localhost (socket) and 127.0.0.1 (TCP, which Laravel uses).
"$DB_CLI" --defaults-extra-file="$MYSQL_ROOT_CNF" <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

printf '[client]\nuser=%s\npassword="%s"\nhost=127.0.0.1\nport=3306\n' "$DB_USER" "$DB_PASS" > "$APP_DB_CNF"
"$DB_CLI" --defaults-extra-file="$APP_DB_CNF" -e 'SELECT 1' "$DB_NAME" > /dev/null 2>&1 \
    || die "The app user couldn't connect to MariaDB on 127.0.0.1:3306."
ok "Database '${DB_NAME}' and user '${DB_USER}' ready (connection tested)"

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
set_env DB_CONNECTION mysql
set_env DB_HOST 127.0.0.1
set_env DB_PORT 3306
set_env DB_DATABASE "$DB_NAME"
set_env DB_USERNAME "$DB_USER"
set_env DB_PASSWORD "$DB_PASS"
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
CERT_ARGS=()
for name in $SERVER_NAMES; do CERT_ARGS+=(-d "$name"); done
if [[ -n "${LE_EMAIL:-}" ]]; then EMAIL_ARGS=(--email "$LE_EMAIL"); else EMAIL_ARGS=(--register-unsafely-without-email); fi
CERT_ISSUED=0

if [[ "${SKIP_SSL:-0}" == "1" ]]; then
    warn "Skipped (SKIP_SSL=1)."
elif [[ -n "${CF_API_TOKEN:-}" ]]; then
    # DNS-01 challenge through Cloudflare: works before DNS has propagated and
    # with the Cloudflare proxy on. The token is kept for automatic renewals.
    install -d -m 700 "$(dirname "$CF_CREDENTIALS")"
    printf 'dns_cloudflare_api_token = %s\n' "$CF_API_TOKEN" > "$CF_CREDENTIALS"
    chmod 600 "$CF_CREDENTIALS"
    certbot run -a dns-cloudflare -i nginx \
        --dns-cloudflare-credentials "$CF_CREDENTIALS" --dns-cloudflare-propagation-seconds 30 \
        "${CERT_ARGS[@]}" "${EMAIL_ARGS[@]}" \
        --non-interactive --agree-tos --redirect --keep-until-expiring --quiet
    CERT_ISSUED=1
    ok "Certificate issued for ${SERVER_NAMES} (Cloudflare DNS challenge) — HTTP now redirects to HTTPS"

    if [[ "$PROXIED" == "true" ]]; then
        if cf PATCH "/zones/${ZONE_ID}/settings/ssl" '{"value":"strict"}' > /dev/null 2>&1; then
            ok "Cloudflare SSL/TLS mode set to Full (strict)"
        else
            warn "Set Cloudflare → SSL/TLS → Full (strict) yourself (the token needs Zone Settings: Edit to do it)."
        fi
    fi
else
    # HTTP-01 challenge: the domain must already resolve to this server.
    DNS_OK=1
    for name in $SERVER_NAMES; do
        RESOLVED="$(getent ahostsv4 "$name" | awk 'NR==1{print $1}')"
        if [[ "$RESOLVED" != "$SERVER_IP" ]]; then
            warn "${name} resolves to ${RESOLVED:-nothing}, not ${SERVER_IP}"; DNS_OK=0
        fi
    done
    if [[ $DNS_OK -eq 0 ]]; then
        warn "Point your DNS at ${SERVER_IP}, then re-run this script to issue the certificate."
    else
        certbot --nginx "${CERT_ARGS[@]}" "${EMAIL_ARGS[@]}" \
            --non-interactive --agree-tos --redirect --keep-until-expiring --quiet
        CERT_ISSUED=1
        ok "Certificate issued for ${SERVER_NAMES} — HTTP now redirects to HTTPS"
    fi
fi

if [[ $CERT_ISSUED -eq 1 ]]; then
    # Renewal runs from certbot's systemd timer (or cron on older systems).
    systemctl enable --now certbot.timer > /dev/null 2>&1 || true
    mkdir -p /etc/letsencrypt/renewal-hooks/deploy
    printf '#!/bin/sh\nsystemctl reload nginx\n' > /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
    chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
    certbot renew --dry-run --quiet && ok "Automatic renewal verified (dry run)"
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
  Server IP       ${SERVER_IP}

  ${C_B}Next steps${C_0}
  1. Discord Developer Portal → your app → OAuth2 → Redirects, add:
       https://${DOMAIN}/auth/discord/callback
  2. Sign in at /admin, then open Settings → Payments and add your PayPal
     business email (IPN URL: https://${DOMAIN}/paypal/ipn).
  3. Optional: Settings → Notifications → Discord webhook for alerts.

  ${C_DIM}Deploy updates later with:  sudo bash ${APP_DIR}/deploy/update.sh${C_0}
  ${C_DIM}Change Discord keys or admin IDs in ${APP_DIR}/.env, then run update.sh.${C_0}

DONE
