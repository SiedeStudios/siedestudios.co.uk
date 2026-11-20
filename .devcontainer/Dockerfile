FROM mcr.microsoft.com/devcontainers/base:ubuntu-24.04

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
        php8.3-gd php8.3-intl php8.3-bcmath php8.3-sqlite3 unzip mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && printf 'upload_max_filesize = 16M\npost_max_size = 64M\n' > /etc/php/8.3/cli/conf.d/99-dev.ini
