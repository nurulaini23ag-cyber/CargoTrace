#!/bin/sh
set -e

# Wait for database if needed
echo "Running CargoTrace startup checks..."

# Ensure storage directories exist with correct permissions
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage bootstrap/cache

# Execute migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

exec "$@"
