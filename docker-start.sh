#!/bin/bash
set -e

cd /var/www/html

# Generate app key if not set
php artisan key:generate --force

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link || true

# Start Apache
apache2-foreground
