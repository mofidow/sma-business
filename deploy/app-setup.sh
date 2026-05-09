#!/bin/bash
# =======================================================
#  SMA — App Setup Script (run AFTER uploading files)
#  Run from inside the app directory:
#  cd /var/www/sma && bash deploy/app-setup.sh
# =======================================================

set -e
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓ $1${NC}"; }
info() { echo -e "${YELLOW}→ $1${NC}"; }

APP_DIR="/var/www/sma"
cd "${APP_DIR}"

info "Copying .env from template..."
cp /root/sma.env.template "${APP_DIR}/.env"
ok ".env configured"

info "Setting file permissions..."
chown -R www-data:www-data "${APP_DIR}"
chmod -R 755 "${APP_DIR}"
chmod -R 775 storage bootstrap/cache
ok "Permissions set"

info "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction -q
ok "PHP dependencies installed"

info "Installing & building frontend..."
npm ci --prefer-offline
npm run build
ok "Frontend built"

info "Running database migrations..."
php8.4 artisan migrate --force
ok "Database migrated"

info "Creating storage symlink..."
php8.4 artisan storage:link --force 2>/dev/null || true
ok "Storage linked"

info "Caching configuration..."
php8.4 artisan config:cache
php8.4 artisan route:cache
php8.4 artisan view:cache
php8.4 artisan event:cache
ok "Caches warmed"

info "Starting queue workers..."
supervisorctl reread
supervisorctl update
supervisorctl start sma-worker:* 2>/dev/null || supervisorctl restart sma-worker:*
supervisorctl start sma-scheduler 2>/dev/null || supervisorctl restart sma-scheduler
ok "Queue workers started"

info "Issuing SSL certificate..."
DOMAIN=$(grep APP_URL .env | cut -d'/' -f3)
certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "admin@${DOMAIN}" || \
    echo "⚠ SSL: add an email or run manually: certbot --nginx -d ${DOMAIN}"

systemctl reload nginx

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║  ✓ SMA is LIVE!                                         ║"
echo "║                                                          ║"
echo "║  Open in browser: https://${DOMAIN}          ║"
echo "║  Admin login:     /admin                                 ║"
echo "║                                                          ║"
echo "║  To update later: bash deploy/deploy.sh                  ║"
echo "╚══════════════════════════════════════════════════════════╝"
