#!/bin/bash
# install-lemp-fixed.sh

set -e

echo "🚀 Installing Complete LEMP Stack for NextGenBeing"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="nextgenbeing"
APP_DIR="/var/www/$APP_NAME"
DOMAIN="nextgenbeing.com"
DB_NAME="nextgenbeing"
DB_USER="nextgenbeing"
DB_PASSWORD="$(openssl rand -base64 32)"
REDIS_PASSWORD="$(openssl rand -base64 32)"
JWT_SECRET="$(openssl rand -base64 32)"
MEILISEARCH_KEY="$(openssl rand -base64 32)"

# Get server IP
SERVER_IP=$(curl -s http://checkip.amazonaws.com || hostname -I | awk '{print $1}')

# Update system
echo -e "${YELLOW}Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# Install LEMP stack and dependencies
echo -e "${YELLOW}Installing LEMP stack and dependencies...${NC}"
sudo apt install -y \
    nginx \
    postgresql postgresql-contrib \
    php8.4-fpm php8.4-cli php8.4-common \
    php8.4-pgsql php8.4-mysql php8.4-zip \
    php8.4-gd php8.4-mbstring php8.4-curl \
    php8.4-xml php8.4-bcmath php8.4-intl \
    php8.4-imagick php8.4-redis \
    redis-server \
    certbot python3-certbot-nginx \
    git unzip fail2ban supervisor \
    software-properties-common

# Check if Node.js is already installed
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}Installing Node.js...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
else
    echo -e "${GREEN}✅ Node.js is already installed: $(node --version)${NC}"
fi

# Install Composer (fixed location)
echo -e "${YELLOW}Installing Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
else
    echo -e "${GREEN}✅ Composer is already installed: $(composer --version)${NC}"
fi

# Configure PostgreSQL (with error handling)
echo -e "${YELLOW}Configuring PostgreSQL...${NC}"

# Check if user already exists
if sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    echo -e "${YELLOW}⚠️  User '$DB_USER' already exists. Updating password...${NC}"
    sudo -u postgres psql -c "ALTER USER $DB_USER WITH PASSWORD '$DB_PASSWORD';"
else
    sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';"
fi

# Check if database already exists
if sudo -u postgres psql -lqt | cut -d \| -f 1 | grep -qw "$DB_NAME"; then
    echo -e "${YELLOW}⚠️  Database '$DB_NAME' already exists. Ensuring ownership...${NC}"
    sudo -u postgres psql -c "ALTER DATABASE $DB_NAME OWNER TO $DB_USER;"
else
    sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;"
fi

sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"

# Configure Redis with password
echo -e "${YELLOW}Configuring Redis...${NC}"
sudo sed -i "s/# requirepass .*/requirepass $REDIS_PASSWORD/" /etc/redis/redis.conf
sudo sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
sudo systemctl restart redis

# Configure PHP-FPM with production settings
echo -e "${YELLOW}Configuring PHP-FPM...${NC}"
sudo cp /etc/php/8.4/fpm/php.ini /etc/php/8.4/fpm/php.ini.backup
sudo tee /etc/php/8.4/fpm/conf.d/custom.ini << EOF
[PHP]
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php8.4-fpm.log
ignore_repeated_errors = On

memory_limit = 256M
max_execution_time = 30
max_input_time = 60
max_input_vars = 1000

upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

session.cookie_secure = On
session.cookie_httponly = On
session.cookie_samesite = Strict
session.use_only_cookies = On

expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

opcache.enable = On
opcache.enable_cli = On
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = Off
opcache.save_comments = On

date.timezone = UTC
default_charset = UTF-8
EOF

sudo sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.4/fpm/php.ini
sudo systemctl restart php8.4-fpm

# Configure firewall
echo -e "${YELLOW}Configuring firewall...${NC}"
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
echo "y" | sudo ufw enable

# Create application directory
echo -e "${YELLOW}Setting up application directory...${NC}"
sudo mkdir -p $APP_DIR
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
cd $APP_DIR

# Clone repository (skip if already exists)
if [ ! -d ".git" ]; then
    echo -e "${YELLOW}Cloning repository...${NC}"
    sudo -u www-data git clone https://github.com/iBekzod/next-gen-being.git .
    sudo -u www-data git checkout main
else
    echo -e "${GREEN}✅ Repository already exists. Pulling latest changes...${NC}"
    sudo -u www-data git pull origin main
fi

# Install PHP dependencies
echo -e "${YELLOW}Installing PHP dependencies...${NC}"
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies and build assets
echo -e "${YELLOW}Building frontend assets...${NC}"
sudo -u www-data npm install --silent
sudo -u www-data npm run build

# Create environment file
echo -e "${YELLOW}Creating environment file...${NC}"
if [ -f ".env" ]; then
    echo -e "${YELLOW}⚠️  .env file already exists. Backing up...${NC}"
    sudo -u www-data cp .env .env.backup.$(date +%Y%m%d%H%M%S)
fi

# Generate secure keys
APP_KEY="base64:$(openssl rand -base64 32)"

# Update environment variables with all necessary configurations
sudo -u www-data tee .env << EOF
APP_NAME=NextGenBeing
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=https://$DOMAIN

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=$REDIS_PASSWORD
REDIS_PORT=6379

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@$DOMAIN"
MAIL_FROM_NAME="NextGenBeing"

JWT_SECRET=$JWT_SECRET
JWT_TTL=86400

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=$MEILISEARCH_KEY

# Optional services
GOOGLE_ANALYTICS_ID=
SENTRY_LARAVEL_DSN=

# Security
SESSION_SECURE_COOKIE=true
EOF

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}Generating application key...${NC}"
    sudo -u www-data php artisan key:generate
fi

# Set proper permissions
echo -e "${YELLOW}Setting permissions...${NC}"
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# Create production Nginx configuration
echo -e "${YELLOW}Configuring Nginx...${NC}"
sudo tee /etc/nginx/sites-available/$APP_NAME << EOF
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Set up Supervisor for queue workers
echo -e "${YELLOW}Configuring Supervisor...${NC}"
sudo tee /etc/supervisor/conf.d/laravel-worker.conf << EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nextgenbeing/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-worker.log
stopwaitsecs=3600

[program:laravel-scheduler]
process_name=%(program_name)s
command=/bin/bash -c "while [ true ]; do php /var/www/nextgenbeing/artisan schedule:run --verbose --no-interaction; sleep 60; done"
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-schedule.log
EOF

sudo mkdir -p /var/log/supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all

# Set up SSL certificate
echo -e "${YELLOW}Setting up SSL certificate...${NC}"
sudo mkdir -p /var/www/certbot
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN --redirect

# Run database migrations
echo -e "${YELLOW}Running database migrations...${NC}"
sudo -u www-data php artisan migrate --force

# Optimize application
echo -e "${YELLOW}Optimizing application...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan storage:link

# Set up cron jobs
echo -e "${YELLOW}Setting up cron jobs...${NC}"
# Remove existing cron jobs for this app
(crontab -l | grep -v "$APP_DIR" | crontab -) || true

# Laravel scheduler
(crontab -l 2>/dev/null; echo "* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Backups
(crontab -l 2>/dev/null; echo "0 2 * * * cd $APP_DIR && php artisan backup:run --quiet") | crontab -

# SSL renewal
(crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet && systemctl reload nginx") | crontab -

# Health check
echo -e "${YELLOW}Performing health check...${NC}"
sleep 10

if curl -f https://$DOMAIN > /dev/null 2>&1; then
    echo -e "${GREEN}✅ LEMP stack deployment completed successfully!${NC}"
    echo -e "${GREEN}🌐 Your application is available at: https://$DOMAIN${NC}"
elif curl -f http://$DOMAIN > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Application running on HTTP but SSL not configured yet${NC}"
    echo -e "${GREEN}🌐 Your application is available at: http://$DOMAIN${NC}"
else
    echo -e "${RED}❌ Health check failed${NC}"
    echo "Checking Nginx status..."
    sudo systemctl status nginx
    echo "Checking PHP-FPM status..."
    sudo systemctl status php8.4-fpm
    echo "Checking Nginx error logs..."
    sudo tail -20 /var/log/nginx/error.log
fi

# Create management script
echo -e "${YELLOW}Creating management script...${NC}"
sudo tee /usr/local/bin/ngb-manage << EOF
#!/bin/bash
# NextGenBeing Management Script

case "\$1" in
    start)
        sudo systemctl start nginx php8.4-fpm postgresql redis supervisor
        ;;
    stop)
        sudo systemctl stop nginx php8.4-fpm supervisor
        ;;
    restart)
        sudo systemctl restart nginx php8.4-fpm postgresql redis supervisor
        ;;
    status)
        sudo systemctl status nginx php8.4-fpm postgresql redis supervisor
        ;;
    logs)
        sudo tail -f /var/log/nginx/error.log /var/log/php8.4-fpm.log /var/log/supervisor/laravel-worker.log
        ;;
    update)
        cd $APP_DIR
        sudo -u www-data git pull
        sudo -u www-data composer install --no-dev --optimize-autoloader
        sudo -u www-data npm install --silent
        sudo -u www-data npm run build
        sudo -u www-data php artisan migrate --force
        sudo -u www-data php artisan optimize
        sudo systemctl restart nginx php8.4-fpm
        ;;
    artisan)
        cd $APP_DIR
        sudo -u www-data php artisan "\${@:2}"
        ;;
    composer)
        cd $APP_DIR
        sudo -u www-data composer "\${@:2}"
        ;;
    *)
        echo "Usage: \$0 {start|stop|restart|status|logs|update|artisan|composer}"
        exit 1
        ;;
esac
EOF

sudo chmod +x /usr/local/bin/ngb-manage

echo -e "\n${GREEN}✅ Installation complete!${NC}"
echo -e "${BLUE}📋 Next steps:${NC}"
echo "1. Configure DNS in Cloudflare:"
echo "   - Point nextgenbeing.com to $SERVER_IP"
echo "   - Point www.nextgenbeing.com to $SERVER_IP"
echo "2. Set up your admin user:"
echo "   cd $APP_DIR && sudo -u www-data php artisan make:filament-user"
echo "3. Test your application:"
echo "   https://$DOMAIN"
echo "4. Set up monitoring and alerting"
echo "5. Configure regular backups"

echo -e "\n${BLUE}🛠️  Useful commands:${NC}"
echo "ngb-manage start      - Start all services"
echo "ngb-manage stop       - Stop all services"
echo "ngb-manage restart    - Restart all services"
echo "ngb-manage status     - Check service status"
echo "ngb-manage logs       - View application logs"
echo "ngb-manage update     - Update application"
echo "ngb-manage artisan    - Run artisan commands"
echo "ngb-manage composer   - Run composer commands"

echo -e "\n${GREEN}🌐 Your application should be available at: https://$DOMAIN${NC}"
