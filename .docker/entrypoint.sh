#!/bin/sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

echo "Running database migrations..."
php artisan migrate --seed --no-interaction

exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
