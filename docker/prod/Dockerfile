###############################################################################
# Stage 1 — Builder (PHP extensions and composer dependencies)
###############################################################################
FROM dunglas/frankenphp:1-builder-php8.4-bookworm AS php-builder

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        openssh-client \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pcntl gd pdo_mysql pdo_pgsql zip intl exif

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN --mount=type=secret,id=composer_auth \
    export COMPOSER_AUTH=$(cat /run/secrets/composer_auth) \
    && composer install --no-scripts --prefer-dist --optimize-autoloader --no-dev

COPY . .

###############################################################################
# Stage 2 — Node builder (for building frontend assets)
###############################################################################
FROM node:24-bookworm-slim AS node-builder

WORKDIR /var/www/html

COPY package.json package-lock.json ./

RUN npm install

COPY --from=php-builder /var/www/html .

RUN npm run build

###############################################################################
# Stage 3 — Runtime (final image with only runtime dependencies and the application)
###############################################################################
FROM dunglas/frankenphp:1-php8.4-bookworm AS runtime

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data || true

COPY --from=php-builder /usr/local/bin/install-php-extensions /usr/local/bin/install-php-extensions
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN install-php-extensions pcntl gd pdo_mysql pdo_pgsql zip intl exif \
    && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

COPY --from=php-builder --chown=www-data:www-data /var/www/html .
COPY --from=node-builder --chown=www-data:www-data /var/www/html/public/build ./public/build

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

USER www-data

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
