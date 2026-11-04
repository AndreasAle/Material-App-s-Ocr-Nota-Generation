#!/usr/bin/env bash
# Update aplikasi di server: jalankan dari folder aplikasi (mis. /var/www/notago) sebagai user www-data / pemilik folder.
set -euo pipefail

php artisan down --retry=30 || true

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

php artisan migrate --force
php artisan storage:link || true
php artisan scans:secure-files
php artisan notago:sync-purchase-payment-status

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

php artisan up
echo "Deploy selesai."
