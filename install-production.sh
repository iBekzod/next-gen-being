#!/bin/bash

echo "🚀 Installing NextGenBeing on Production Server"

# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
    nginx \
    postgresql-15 \
    postgresql-contrib \
    redis-server \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-redis \
    php8.3-zip \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-imagick \
    supervisor \
    certbot \
    python3-certbot-nginx \
    unzip \
    git \
    curl \
    htop \
    fail2ban

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Create database user and database
sudo -u postgres createuser --interactive nextgen_user
sudo -u postgres createdb nextgenbeing -O nextgen_user

# Create deploy user
sudo adduser deploy
sudo usermod -aG www-data deploy

# Create project directory
sudo mkdir -p /home/deploy/projects
sudo chown deploy:deploy /home/deploy/projects

echo "✅ Production server setup completed!"
echo "Next steps:"
echo "1. Clone your repository to /home/deploy/projects/nextgenbeing"
echo "2. Configure environment variables"
echo "3. Set up SSL certificates"
echo "4. Configure backup scripts"
