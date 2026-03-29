#!/bin/bash
set -e

echo "=== Running database migrations ==="
php artisan migrate --force

echo "=== Creating storage link ==="
php artisan storage:link || true

echo "=== Starting Laravel server ==="
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
