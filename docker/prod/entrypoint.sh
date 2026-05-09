#!/bin/bash
set -e

#echo "# Rodando migrações..."
php artisan migrate --seed --force

#echo "# Limpeza de caches..."
php artisan cache:clear
php artisan config:cache

#echo "# Finalizando e iniciando processo principal: $@"
exec "$@"
