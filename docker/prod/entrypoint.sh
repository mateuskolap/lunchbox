#!/bin/bash
set -e

until php artisan db:show > /dev/null 2>&1; do
    echo "Banco de dados indisponível - aguardando..."
    sleep 2
done

#echo "# Rodando migrações..."
php artisan migrate --seed --force

#echo "# Limpeza de caches..."
php artisan cache:clear
php artisan config:cache

#echo "# Finalizando e iniciando processo principal: $@"
exec "$@"
