#!/usr/bin/env bash
set -e

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_MIGRATIONS_ON_START:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEED_ON_START:-false}" = "true" ]; then
    php artisan app:seed-if-empty
    php artisan app:sync-starter-credentials
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

apache2-foreground
