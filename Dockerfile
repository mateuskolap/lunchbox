# Stage 1: PHP dependencies and Wayfinder generation
FROM php:8.5-alpine AS php-builder

# Install composer and official extension installer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY -e-from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install PHP extensions required for Laravel, SQLite, and Wayfinder generator
RUN install-php-extensions zip pdo pdo_pgsql pdo_mysql pdo_sqlite git

WORKDIR /app

# Copy dependency configuration files first to utilize Docker layer caching
COPY composer.json composer.lock ./

# Install packages without dev dependencies and run-scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy the rest of the application files
COPY . .

# Clear host-polluted cache files (package manifest, route, config caches, etc.)
RUN rm -f bootstrap/cache/*.php

# Regenerate package discovery manifest for production
RUN php artisan package:discover --ansi

# Generate Wayfinder types (using sqlite in-memory database context)
RUN DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    CACHE_STORE=file \
    SESSION_DRIVER=file \
    QUEUE_CONNECTION=sync \
    php artisan wayfinder:generate --with-form --no-interaction

# Stage 2: Frontend assets build
FROM node:24-slim AS frontend-builder

WORKDIR /app

# Copy configuration files first to use Docker caching
COPY package.json package-lock.json .npmrc* ./

# Install npm dependencies
RUN npm ci --fetch-retries=5 --fetch-retry-factor=2 --fetch-retry-mintimeout=20000 --fetch-retry-maxtimeout=120000

# Copy the rest of standard files
COPY . .

# Copy the generated Wayfinder folders from the PHP builder stage
COPY --from=php-builder /app/resources/js/actions ./resources/js/actions
COPY --from=php-builder /app/resources/js/routes ./resources/js/routes
COPY --from=php-builder /app/resources/js/wayfinder ./resources/js/wayfinder

# Build the production frontend assets (compiles to public/build)
RUN VITE_WAYFINDER_COMMAND="true" npm run build

# Stage 3: Production Runtime
FROM php:8.5-fpm-alpine

WORKDIR /var/www/html

# Install runtime tools
RUN apk add --no-cache nginx supervisor bash curl

# Install PHP extensions via precompiled binary installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions zip pdo pdo_mysql pdo_pgsql

# Copy Nginx config
RUN rm -f /etc/nginx/http.d/default.conf
COPY ./.docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy production Supervisor config
COPY ./.docker/supervisord.prod.conf /etc/supervisor/conf.d/supervisord.conf

# Copy production entrypoint script
COPY ./.docker/entrypoint.prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure Nginx run permissions/directory
RUN mkdir -p /var/lib/nginx && chown -R www-data:www-data /var/lib/nginx

# Copy application files (inheriting .dockerignore exclusions)
COPY --chown=www-data:www-data . /var/www/html

# Copy vendor and Wayfinder artifacts from PHP builder
COPY --from=php-builder --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/actions /var/www/html/resources/js/actions
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/routes /var/www/html/resources/js/routes
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/wayfinder /var/www/html/resources/js/wayfinder

# Copy compiled frontend assets
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Setup PHP-FPM listening configuration
RUN echo "listen = 9000" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Ensure storage and bootstrap/cache permissions are correct
RUN mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENV RUN_MIGRATIONS=true

CMD ["/usr/local/bin/entrypoint.sh"]