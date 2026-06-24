# Stage 1: PHP dependencies and Wayfinder generation
FROM php:8.4-alpine AS php-builder

# Install system dependencies needed for composer and PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    zip \
    libzip-dev \
    libpq-dev \
    sqlite-dev

# Install PHP extensions required for Laravel, SQLite, and the Wayfinder generator
RUN docker-php-ext-install zip pdo pdo_pgsql pdo_mysql pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy configuration files first to use Docker caching
COPY package.json package-lock.json ./

# Install npm dependencies (using npm ci for a clean, reproducible production build)
RUN npm ci

# Copy the rest of standard files
COPY . .

# Copy the generated Wayfinder folders from the PHP builder stage
COPY --from=php-builder /app/resources/js/actions ./resources/js/actions
COPY --from=php-builder /app/resources/js/routes ./resources/js/routes
COPY --from=php-builder /app/resources/js/wayfinder ./resources/js/wayfinder

# Build the production frontend assets (compiles to public/build)
RUN VITE_WAYFINDER_COMMAND="true" npm run build

# Stage 3: Production Runtime
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install runtime system dependencies (nginx, supervisor, and libraries for PHP extensions)
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq \
    libzip \
    bash \
    curl \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql pdo_pgsql \
    && apk del .build-deps

# Copy Nginx config
RUN rm -f /etc/nginx/conf.d/default.conf /etc/nginx/sites-enabled/default
COPY ./.docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy production Supervisor config
COPY ./.docker/supervisord.prod.conf /etc/supervisor/conf.d/supervisord.conf

# Copy production entrypoint script
COPY ./.docker/entrypoint.prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure Nginx run permissions/directory
RUN mkdir -p /var/lib/nginx && chown -R www-data:www-data /var/lib/nginx

# Copy application files (inheriting .dockerignore exclusions)
COPY --chown=www-data:www-data . /var/www/html

# Copy the composer packages from the builder stage
COPY --from=php-builder --chown=www-data:www-data /app/vendor /var/www/html/vendor

# Copy the generated Wayfinder routes from builder stage
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/actions /var/www/html/resources/js/actions
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/routes /var/www/html/resources/js/routes
COPY --from=php-builder --chown=www-data:www-data /app/resources/js/wayfinder /var/www/html/resources/js/wayfinder

# Copy the compiled assets from the frontend builder stage
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

# Expose HTTP port 80
EXPOSE 80

# Configure production environment variables defaults
ENV RUN_MIGRATIONS=true

CMD ["/usr/local/bin/entrypoint.sh"]
