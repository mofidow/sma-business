#!/bin/bash
# SMA Deployment Script — Hostinger VPS
# Run from: /var/www/sma
# Usage:    bash deploy/deploy.sh

set -e

APP_DIR="/var/www/sma"
PHP="php8.4"

echo "==> Pulling latest code..."
git pull origin main

echo "==> Installing PHP dependencies (no dev)..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running database migrations..."
$PHP artisan migrate --force

echo "==> Caching configuration..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache

echo "==> Installing frontend dependencies..."
npm install --prefer-offline

echo "==> Building frontend assets..."
npm run build

echo "==> Linking storage..."
$PHP artisan storage:link --force 2>/dev/null || true

echo "==> Restarting queue workers..."
$PHP artisan queue:restart

echo "==> Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Reloading PHP-FPM..."
systemctl reload php8.4-fpm

echo ""
echo "✓ Deployment complete!"
