# NextGenBeing Deployment Guide

This guide explains how to deploy your NextGenBeing application using the existing GitHub Actions workflow.

## 📋 Prerequisites

Before you can deploy, you need to set up the following:

### 1. Server Requirements
- Ubuntu/Debian Linux server
- PHP 8.4 with extensions: pgsql, mbstring, zip, gd, curl, xml, bcmath, intl, imagick, redis
- PostgreSQL 15
- Nginx
- Composer
- Node.js 20+
- Supervisor (for queue workers)

### 2. GitHub Secrets

You need to add the following secrets to your GitHub repository:

**Steps to add secrets:**
1. Go to GitHub repository → Settings → Secrets and variables → Actions
2. Click "New repository secret"
3. Add each secret below:

#### Required Secrets:

```
SERVER_IP          : Your server's IP address (e.g., 192.168.1.100 or 123.45.67.89)
SSH_USER          : SSH user for deployment (e.g., deploy, www-data)
SSH_PRIVATE_KEY   : Your SSH private key (complete key including -----BEGIN PRIVATE KEY-----)
```

**How to generate SSH keys (if you don't have them):**

```bash
# On your local machine
ssh-keygen -t rsa -b 4096 -f ~/.ssh/deploy_key -N ""

# Copy public key to server
ssh-copy-id -i ~/.ssh/deploy_key.pub deploy@your-server-ip

# Get the private key content
cat ~/.ssh/deploy_key
```

## 🚀 How Deployment Works

### Automatic Deployment

The deployment workflow automatically runs when you push to the `main` branch:

```
git push origin main  →  GitHub Actions runs deploy.yml  →  Updates your server
```

### Workflow Steps

The `deploy.yml` workflow performs these steps:

1. **Testing Phase** (runs on Ubuntu):
   - Runs PHP tests with PostgreSQL
   - Ensures code quality before deployment
   - Only proceeds to deployment if tests pass

2. **Deployment Phase** (if tests pass):
   - Puts application in maintenance mode
   - Pulls latest code from GitHub
   - Installs PHP and Node.js dependencies
   - Runs database migrations
   - Clears and rebuilds all caches
   - Sets proper file permissions
   - Restarts services (PHP-FPM, Nginx, Supervisor)
   - Brings application back online

## 🔧 Server Setup

### 1. Create Deploy User

```bash
# On your server as root
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG sudo deploy
```

### 2. Setup Application Directory

```bash
# As deploy user or root
sudo mkdir -p /var/www/nextgenbeing
sudo chown deploy:deploy /var/www/nextgenbeing
cd /var/www/nextgenbeing
```

### 3. Clone Repository

```bash
# As deploy user
git clone https://github.com/yourusername/next-gen-being.git .
```

### 4. Configure Environment

```bash
# Create .env file on server
cp .env.example .env

# Edit and set your production values
nano .env
```

**Important .env variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # Generate with: php artisan key:generate

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=nextgenbeing
DB_USERNAME=nextgen_user
DB_PASSWORD=strong_password_here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Add your API keys, Socialite configs, etc.
```

### 5. Install Dependencies First Time

```bash
cd /var/www/nextgenbeing

# PHP dependencies
composer install --optimize-autoloader

# Node.js dependencies
npm install
npm run build

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data /var/www/nextgenbeing
sudo chmod -R 755 /var/www/nextgenbeing
sudo chmod -R 775 /var/www/nextgenbeing/storage
sudo chmod -R 775 /var/www/nextgenbeing/bootstrap/cache
```

### 6. Configure Nginx

```bash
# Create Nginx config
sudo nano /etc/nginx/sites-available/nextgenbeing
```

```nginx
server {
    listen 80;
    server_name nextgenbeing.com www.nextgenbeing.com;
    root /var/www/nextgenbeing/public;

    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/nextgenbeing /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Configure Supervisor (for Queue Workers)

```bash
sudo nano /etc/supervisor/conf.d/nextgenbeing.conf
```

```ini
[program:nextgenbeing-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nextgenbeing/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/nextgenbeing-worker.log
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

## 📝 Making a Deployment

### Step 1: Make your changes locally

```bash
git add .
git commit -m "Add new feature or fix"
```

### Step 2: Push to main branch

```bash
git push origin main
```

### Step 3: GitHub Actions runs automatically

- Check the Actions tab in GitHub
- Watch the deployment progress
- Deployment takes ~2-5 minutes

### Step 4: Verify deployment

Check your application:
```bash
curl https://nextgenbeing.com/health
```

Or visit: `https://nextgenbeing.com`

## 🐛 Troubleshooting

### Deployment Failed - Check Logs

**View GitHub Actions logs:**
1. Go to GitHub → Actions tab
2. Click on the failed workflow
3. Scroll down to see error details

**Check server logs:**
```bash
# PHP errors
tail -f /var/log/php8.4-fpm.log

# Nginx errors
tail -f /var/log/nginx/error.log

# Application logs
tail -f /var/www/nextgenbeing/storage/logs/laravel.log
```

### Common Issues

#### 1. "SSH key permission denied"
```bash
# Ensure SSH key has correct permissions (600)
chmod 600 ~/.ssh/deploy_key
```

#### 2. "Composer install fails"
```bash
# Check if enough disk space
df -h

# Check PHP version on server
php -v

# Reinstall composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

#### 3. "Database migration fails"
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check if user has migrate permission
# Run migrations manually first time
```

#### 4. "Assets not building"
```bash
# Check Node.js version
node -v

# Clear npm cache
npm cache clean --force

# Rebuild assets
npm install
npm run build
```

### Application in Maintenance Mode

If deployment fails and app is stuck in maintenance mode:

```bash
ssh deploy@your-server-ip
cd /var/www/nextgenbeing
php artisan up
```

## 🔒 Security Checklist

- [ ] SSH keys are secure (600 permissions)
- [ ] .env file is never committed to Git
- [ ] GitHub secrets are configured
- [ ] Database user has minimal required permissions
- [ ] Nginx is configured to hide PHP files
- [ ] SSL certificate is installed (Let's Encrypt)
- [ ] Firewall is configured (only open 80, 443, 22)
- [ ] Regular backups are being taken

## 🔐 Enable HTTPS with Let's Encrypt

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d nextgenbeing.com -d www.nextgenbeing.com
```

Update Nginx config to redirect HTTP to HTTPS:

```nginx
server {
    listen 80;
    server_name nextgenbeing.com www.nextgenbeing.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name nextgenbeing.com www.nextgenbeing.com;

    ssl_certificate /etc/letsencrypt/live/nextgenbeing.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/nextgenbeing.com/privkey.pem;

    # ... rest of config
}
```

## 📊 Monitoring

### Check Application Status

```bash
# Health check endpoint
curl https://nextgenbeing.com/health

# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# Check Nginx status
sudo systemctl status nginx

# Check supervisor workers
sudo supervisorctl status
```

### View Logs

```bash
# Application logs
cd /var/www/nextgenbeing
tail -f storage/logs/laravel.log

# Watch multiple logs
tail -f /var/log/nginx/access.log /var/log/nginx/error.log
```

## 🔄 Rollback Procedure

If something goes wrong after deployment:

```bash
ssh deploy@your-server-ip
cd /var/www/nextgenbeing

# Revert to previous commit
git reset --hard HEAD~1

# Composer install
composer install --optimize-autoloader

# Rebuild assets
npm install && npm run build

# Run pending migrations (if any)
php artisan migrate --force

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

## 📞 Support

For deployment issues:

1. Check GitHub Actions logs for error messages
2. SSH into server and check application logs
3. Verify all prerequisites are installed
4. Ensure GitHub secrets are correct
5. Check server disk space and memory

## 📚 Additional Resources

- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Nginx Configuration](https://nginx.org/en/docs/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
