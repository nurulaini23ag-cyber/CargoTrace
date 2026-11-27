#!/bin/sh
set -e

cd /var/www/html

# Ensure writable storage, cache, and database directories
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache database
chmod -R 775 storage bootstrap/cache database

# Create SQLite database if using sqlite and file does not exist
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Generate APP_KEY dynamically if not provided via Railway environment variables
if [ -z "$APP_KEY" ]; then
    export APP_KEY=$(php artisan key:generate --show --no-interaction)
fi

# Clear stale caches so environment variables take effect
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations safely
php artisan migrate --force

# Seed initial demo data only if users table is empty
USER_COUNT=$(php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try { echo \App\Models\User::count(); } catch (\Throwable \$e) { echo 0; }
" 2>/dev/null || echo "0")

if [ "$USER_COUNT" = "0" ]; then
    echo "Database is empty. Seeding CargoTrace demo data..."
    php artisan db:seed --force
fi

# Start Laravel HTTP server on Railway dynamic PORT
echo "Starting CargoTrace server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
