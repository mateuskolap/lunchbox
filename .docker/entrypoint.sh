#!/bin/sh
set -e

# If vendor directory doesn't exist, install composer packages
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Vendor directory not found. Installing composer dependencies..."
    composer install --no-interaction
    chown -R www-data:www-data /var/www/html/vendor
fi

# If node_modules directory doesn't exist, install npm packages
if [ ! -d "/var/www/html/node_modules" ]; then
    echo "Node modules not found. Installing npm packages..."
    npm install
    chown -R www-data:www-data /var/www/html/node_modules
fi

# Generate wayfinder types
if [ -f "/var/www/html/artisan" ]; then
    echo "Generating wayfinder types..."
    php artisan wayfinder:generate --with-form --no-interaction || echo "Wayfinder generation skipped."
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

echo "Running database migrations..."
php artisan migrate --seed --no-interaction

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
