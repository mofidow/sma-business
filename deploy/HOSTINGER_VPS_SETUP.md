# Hostinger VPS Setup Guide — SMA Business Manager

## 1. Server Requirements
- Ubuntu 22.04 LTS
- PHP 8.4 (with extensions: pdo_mysql, mbstring, openssl, tokenizer, ctype, json, bcmath, zip, gd, curl, xml, fileinfo)
- MySQL 8.0 or MariaDB 10.11
- Nginx
- Node.js 20+ and npm
- Supervisor
- Redis (optional, recommended)
- Certbot (Let's Encrypt SSL)

## 2. Install Dependencies

```bash
# Update system
apt update && apt upgrade -y

# PHP 8.4
add-apt-repository ppa:ondrej/php -y
apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-openssl \
    php8.4-tokenizer php8.4-ctype php8.4-json php8.4-bcmath php8.4-zip \
    php8.4-gd php8.4-curl php8.4-xml php8.4-fileinfo php8.4-redis

# Nginx
apt install -y nginx

# MySQL
apt install -y mysql-server

# Redis (for caching & sessions)
apt install -y redis-server
systemctl enable redis-server

# Supervisor (queue workers)
apt install -y supervisor

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

## 3. Database Setup

```bash
mysql -u root -p
CREATE DATABASE sma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sma_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON sma.* TO 'sma_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Application Setup

```bash
# Clone / upload your app
mkdir -p /var/www/sma
cd /var/www/sma
git clone https://github.com/YOUR_REPO . 
# OR: upload the files via SFTP to /var/www/sma

# Set permissions
chown -R www-data:www-data /var/www/sma
chmod -R 755 /var/www/sma
chmod -R 775 storage bootstrap/cache

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Configure environment
cp .env.example .env
nano .env
```

## 5. Environment File (.env) — Key Settings

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_INSTALLED=true
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sma
DB_USERNAME=sma_user
DB_PASSWORD=STRONG_PASSWORD_HERE

CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# WAAFI Pay credentials (enter in Admin → Settings → Payments too)
WAAFI_MERCHANT_UID=your_merchant_uid
WAAFI_API_USER_ID=your_api_user_id
WAAFI_API_KEY=your_api_key
WAAFI_ENABLED=true
```

## 6. Laravel Setup

```bash
php8.4 artisan key:generate
php8.4 artisan migrate --force
php8.4 artisan storage:link
php8.4 artisan config:cache
php8.4 artisan route:cache
php8.4 artisan view:cache
```

## 7. Nginx Configuration

```bash
cp /var/www/sma/deploy/nginx.conf /etc/nginx/sites-available/sma
# Edit: replace yourdomain.com with your actual domain
nano /etc/nginx/sites-available/sma

ln -s /etc/nginx/sites-available/sma /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

## 8. SSL Certificate

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
# Auto-renewal is set up automatically by Certbot
```

## 9. Supervisor (Queue Workers)

```bash
cp /var/www/sma/deploy/supervisor.conf /etc/supervisor/conf.d/sma.conf
supervisorctl reread
supervisorctl update
supervisorctl start sma-worker:*
supervisorctl start sma-scheduler
```

## 10. Deploy Updates

```bash
cd /var/www/sma
bash deploy/deploy.sh
```

## 11. WAAFI Pay Configuration

1. Log in to Admin Panel → Settings → Payments
2. Find "WAAFI Pay" section
3. Enter your Merchant UID, API User ID, API Key
4. Set to "Live (Production)"
5. Save — credentials are encrypted in the database

## 12. Useful Commands

```bash
# View logs
tail -f /var/log/nginx/sma_error.log
tail -f /var/www/sma/storage/logs/laravel.log

# Queue status
supervisorctl status

# Clear all caches
php8.4 artisan optimize:clear

# Run migrations
php8.4 artisan migrate

# Check routes
php8.4 artisan route:list
```
