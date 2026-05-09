#!/bin/bash
# =======================================================
#  SMA Business Manager — Full VPS Auto-Setup Script
#  Run this ONCE on a fresh Ubuntu 22.04 VPS as root.
#  Usage: bash vps-setup.sh
# =======================================================

set -e

# ── Colours ─────────────────────────────────────────────
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓ $1${NC}"; }
info() { echo -e "${YELLOW}→ $1${NC}"; }
err()  { echo -e "${RED}✗ $1${NC}"; exit 1; }

# ── Collect inputs ───────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║   SMA Business Manager — Auto Setup             ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

read -rp "Enter your domain name (e.g. sma.example.com): " DOMAIN
read -rp "Enter MySQL root password to set: " DB_ROOT_PASS
read -rp "Enter app database password to set: " DB_APP_PASS
read -rp "Enter your WAAFI Merchant UID: " WAAFI_UID
read -rp "Enter your WAAFI API User ID:  " WAAFI_USER
read -rp "Enter your WAAFI API Key:      " WAAFI_KEY

DB_NAME="sma_db"
DB_USER="sma_user"
APP_DIR="/var/www/sma"

# ── 1. System update ─────────────────────────────────────
info "Updating system packages..."
apt update -qq && apt upgrade -y -qq
ok "System updated"

# ── 2. PHP 8.4 ───────────────────────────────────────────
info "Installing PHP 8.4..."
apt install -y -qq software-properties-common
add-apt-repository ppa:ondrej/php -y -qq
apt update -qq
apt install -y -qq \
    php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
    php8.4-openssl php8.4-tokenizer php8.4-ctype php8.4-json \
    php8.4-bcmath php8.4-zip php8.4-gd php8.4-curl \
    php8.4-xml php8.4-fileinfo php8.4-redis php8.4-intl
ok "PHP 8.4 installed"

# ── 3. Nginx ─────────────────────────────────────────────
info "Installing Nginx..."
apt install -y -qq nginx
systemctl enable nginx
ok "Nginx installed"

# ── 4. MySQL ─────────────────────────────────────────────
info "Installing MySQL 8.0..."
apt install -y -qq mysql-server
systemctl enable mysql

# Secure MySQL and create app database
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}';"
mysql -u root -p"${DB_ROOT_PASS}" -e "
    CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_APP_PASS}';
    GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
    FLUSH PRIVILEGES;
"
ok "MySQL configured, database '${DB_NAME}' created"

# ── 5. Redis ─────────────────────────────────────────────
info "Installing Redis..."
apt install -y -qq redis-server
systemctl enable redis-server
ok "Redis installed"

# ── 6. Node.js 20 ────────────────────────────────────────
info "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - -qq
apt install -y -qq nodejs
ok "Node.js $(node -v) installed"

# ── 7. Composer ──────────────────────────────────────────
info "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer -q
ok "Composer installed"

# ── 8. Supervisor ────────────────────────────────────────
info "Installing Supervisor..."
apt install -y -qq supervisor
systemctl enable supervisor
ok "Supervisor installed"

# ── 9. Certbot (SSL) ─────────────────────────────────────
info "Installing Certbot..."
apt install -y -qq certbot python3-certbot-nginx
ok "Certbot installed"

# ── 10. App directory ────────────────────────────────────
info "Creating app directory ${APP_DIR}..."
mkdir -p "${APP_DIR}"
chown -R www-data:www-data "${APP_DIR}"
ok "App directory ready — upload your files to ${APP_DIR}"

# ── 11. Nginx site config ────────────────────────────────
info "Configuring Nginx for ${DOMAIN}..."
cat > /etc/nginx/sites-available/sma <<NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};

    root ${APP_DIR}/public;
    index index.php;

    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss text/javascript image/svg+xml;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|webmanifest)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 135;
        fastcgi_send_timeout 135;
    }

    location ~ /\.(?!well-known).* { deny all; }

    client_max_body_size 50M;
    error_log  /var/log/nginx/sma_error.log;
    access_log /var/log/nginx/sma_access.log;
}
NGINX

ln -sf /etc/nginx/sites-available/sma /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
ok "Nginx configured for ${DOMAIN}"

# ── 12. Supervisor workers ───────────────────────────────
info "Configuring queue workers..."
cat > /etc/supervisor/conf.d/sma.conf <<SUP
[program:sma-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/sma-worker.log
stopwaitsecs=3600

[program:sma-scheduler]
process_name=%(program_name)s
command=/bin/bash -c "while true; do php ${APP_DIR}/artisan schedule:run >> /var/log/sma-scheduler.log 2>&1; sleep 60; done"
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/sma-scheduler.log
SUP
supervisorctl reread
supervisorctl update
ok "Supervisor configured"

# ── 13. Save .env template ───────────────────────────────
info "Writing .env template to /root/sma.env.template ..."
APP_KEY=$(openssl rand -base64 32)
cat > /root/sma.env.template <<ENV
APP_NAME="SMA Business Manager"
APP_ENV=production
APP_KEY=base64:${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}
APP_INSTALLED=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_APP_PASS}

CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=log

FILESYSTEM_DISK=local

WAAFI_MERCHANT_UID=${WAAFI_UID}
WAAFI_API_USER_ID=${WAAFI_USER}
WAAFI_API_KEY=${WAAFI_KEY}
WAAFI_ENABLED=true
ENV
ok ".env template saved to /root/sma.env.template"

# ── 14. PHP-FPM tuning ───────────────────────────────────
info "Tuning PHP-FPM for production..."
sed -i 's/^pm.max_children.*/pm.max_children = 20/' /etc/php/8.4/fpm/pool.d/www.conf
sed -i 's/^pm.start_servers.*/pm.start_servers = 4/' /etc/php/8.4/fpm/pool.d/www.conf
sed -i 's/^pm.min_spare_servers.*/pm.min_spare_servers = 2/' /etc/php/8.4/fpm/pool.d/www.conf
sed -i 's/^pm.max_spare_servers.*/pm.max_spare_servers = 6/' /etc/php/8.4/fpm/pool.d/www.conf
systemctl restart php8.4-fpm
ok "PHP-FPM tuned"

# ── Done ─────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║  ✓ Server setup complete!                               ║"
echo "║                                                          ║"
echo "║  NEXT STEPS:                                             ║"
echo "║  1. Upload your app files to: ${APP_DIR}          ║"
echo "║     (Use WinSCP — see guide)                             ║"
echo "║  2. Then run: bash ${APP_DIR}/deploy/app-setup.sh ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "  Your .env file is ready at: /root/sma.env.template"
echo "  Domain:   ${DOMAIN}"
echo "  Database: ${DB_NAME} / ${DB_USER}"
echo ""
