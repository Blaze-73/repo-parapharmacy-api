#!/bin/bash
set -e

echo "=== Installing PHP dependencies ==="
composer install --no-dev --optimize-autoloader --no-interaction

echo "=== Generating app key ==="
php artisan key:generate --force

echo "=== Caching config ==="
php artisan config:cache
php artisan route:cache

echo "=== Build complete ==="
