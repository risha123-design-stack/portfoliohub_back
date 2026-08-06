#!/bin/sh

set -e

echo "Starting PortfolioHub backend..."

cd /var/www/html

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data \
    storage \
    bootstrap/cache

php artisan config:clear
php artisan view:clear

echo "Running database migrations..."
php artisan migrate --force

echo "Caching Laravel configuration..."
php artisan config:cache
php artisan view:cache

if [ ! -L public/storage ]; then
    php artisan storage:link
fi

echo "Checking Apache configuration..."
apache2ctl configtest

echo "Starting Apache on port 8080..."
exec apache2-foreground