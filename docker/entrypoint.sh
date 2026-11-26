#!/bin/sh
# Prepares the application each time the container starts, then hands over to Apache.
set -e

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set: generate one with 'php artisan key:generate --show'." >&2
    exit 1
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Discover packages, then cache config, routes and views for faster responses.
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Apply database migrations (the database container is already healthy, see docker-compose.yml).
php artisan migrate --force

# Optional demo data; the seeder does nothing if accounts already exist.
if [ "$SEED_DEMO_DATA" = "true" ]; then
    php artisan db:seed --force --class=DemoSeeder
fi

# Apache runs as www-data and must be able to write logs, sessions and caches.
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
