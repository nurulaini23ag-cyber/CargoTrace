#!/usr/bin/env bash
set -e

echo "=== CargoTrace Deployment Script ==="
echo "1. Pulling latest release from Git..."
git pull origin main

echo "2. Installing optimized composer dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "3. Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "4. Running database migrations..."
php artisan migrate --force

echo "5. Restarting queue worker & application..."
php artisan queue:restart

echo "=== Deployment Completed Successfully! ==="
