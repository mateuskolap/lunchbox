#!/bin/bash
set -e

if [ ! -f "vendor/autoload.php" ]; then
  echo "# Vendor não encontrada. Rodando composer install..."
  composer install --no-interaction --prefer-dist --no-cache
fi

if [ ! -d "node_modules" ]; then
  echo "# node_modules não encontrado. Rodando npm install..."
  npm install
fi

echo "# Rodando migrações..."
php artisan migrate --seed --force

echo "# Limpando caches..."
php artisan cache:clear

echo "# Iniciando Vite dev server em background..."
mkdir -p node_modules/.vite-temp
npm run dev -- --host 0.0.0.0 &

echo "# Iniciando processo principal..."
exec "$@"
