#!/bin/bash

# Exit on error
set -e

# Install Composer dependencies
composer install --no-progress --no-interaction --ignore-platform-reqs

cp .env.dev.example .env
# Start PHP-FPM and Nginx in the background

#nginx -g "daemon off;"
# Set up Laravel application
echo "Setting up Laravel application..."


# Clear Laravel caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Run migrations
php artisan migrate
php artisan db:seed --class=PermissionSeeder
#php-fpm -D
#nginx -g "daemon off;"
nginx &
php-fpm &
supervisord -c /etc/supervisor/conf.d/supervisord.conf
#php artisan storage:link

# Run the queue listener indefinitely

# Uncomment if additional seeders or setup is required
# php artisan db:seed --class=PermissionSeeder


# Hand off control to the main PHP entrypoint script for any final actions
exec docker-php-entrypoint "$@"
