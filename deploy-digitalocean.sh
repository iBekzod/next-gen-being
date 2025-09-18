#!/bin/bash
# deploy-digitalocean.sh

set -e

echo "🚀 Deploying NextGenBeing to Digital Ocean Droplet"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="nextgenbeing"
APP_DIR="/opt/$APP_NAME"
DOMAIN="nextgenbeing.com"  # Your new domain
DROPLET_IP="161.35.73.129"  # Your new droplet IP
DB_NAME="${DB_DATABASE:-nextgenbeing}"
DB_USER="${DB_USERNAME:-nextgenbeing}"
DB_PASSWORD="${DB_PASSWORD:-$(openssl rand -base64 32)}"
REDIS_PASSWORD="${REDIS_PASSWORD:-$(openssl rand -base64 32)}"
MEILISEARCH_KEY="${MEILISEARCH_KEY:-$(openssl rand -base64 32)}"

# Update system
echo -e "${YELLOW}Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# Install Docker and Docker Compose
echo -e "${YELLOW}Installing Docker and Docker Compose...${NC}"
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker

# Add current user to docker group
sudo usermod -aG docker $USER

# Install additional tools
echo -e "${YELLOW}Installing additional tools...${NC}"
sudo apt install -y git nginx certbot python3-certbot-nginx fail2ban

# Configure firewall
echo -e "${YELLOW}Configuring firewall...${NC}"
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw --force enable

# Create project directory
echo -e "${YELLOW}Setting up project directory...${NC}"
sudo mkdir -p $APP_DIR
sudo chown $USER:$USER $APP_DIR
cd $APP_DIR

# Clone repository
echo -e "${YELLOW}Cloning repository...${NC}"
git clone https://github.com/iBekzod/next-gen-being.git .
git checkout main

# Create production environment file
echo -e "${YELLOW}Creating production environment file...${NC}"
cp .env.example .env.production

# Generate secure keys
APP_KEY="base64:$(openssl rand -base64 32)"
JWT_SECRET="$(openssl rand -base64 32)"

# Update environment variables
sed -i "s/APP_ENV=local/APP_ENV=production/g" .env.production
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/g" .env.production
sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|g" .env.production
sed -i "s|JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|g" .env.production
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|g" .env.production
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|g" .env.production
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|g" .env.production
sed -i "s|REDIS_PASSWORD=.*|REDIS_PASSWORD=$REDIS_PASSWORD|g" .env.production
sed -i "s|MEILISEARCH_KEY=.*|MEILISEARCH_KEY=$MEILISEARCH_KEY|g" .env.production
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" .env.production
sed -i "s|APP_PORT=.*|APP_PORT=80|g" .env.production

# Load production environment variables
export $(cat .env.production | grep -v '#' | awk '/=/ {print $1}')

# Build Docker images
echo -e "${YELLOW}Building Docker images...${NC}"
docker compose -f docker-compose.prod.yml build

# Start containers
echo -e "${YELLOW}Starting containers...${NC}"
docker compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
echo -e "${YELLOW}Waiting for services to be ready...${NC}"
sleep 30

# Run database migrations
echo -e "${YELLOW}Running database migrations...${NC}"
docker compose -f docker-compose.prod.yml exec ngb-app php artisan migrate --force

# Install npm dependencies and build assets
echo -e "${YELLOW}Building frontend assets...${NC}"
docker compose -f docker-compose.prod.yml exec ngb-app npm install --silent
docker compose -f docker-compose.prod.yml exec ngb-app npm run build

# Set up application cache
echo -e "${YELLOW}Optimizing application...${NC}"
docker compose -f docker-compose.prod.yml exec ngb-app php artisan config:cache
docker compose -f docker-compose.prod.yml exec ngb-app php artisan route:cache
docker compose -f docker-compose.prod.yml exec ngb-app php artisan view:cache

# Set up SSL certificate with Certbot
echo -e "${YELLOW}Setting up SSL certificate...${NC}"
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN --redirect

# Set up backup cron job
echo -e "${YELLOW}Setting up backup schedule...${NC}"
(crontab -l 2>/dev/null; echo "0 2 * * * docker compose -f $APP_DIR/docker-compose.prod.yml exec ngb-app php artisan backup:run --quiet") | crontab -

# Set up scheduler cron job
(crontab -l 2>/dev/null; echo "* * * * * docker compose -f $APP_DIR/docker-compose.prod.yml exec ngb-app php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Set up SSL renewal cron job
(crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet && systemctl reload nginx") | crontab -

# Health check
echo -e "${YELLOW}Performing health check...${NC}"
sleep 10

if curl -f https://$DOMAIN > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo -e "${GREEN}🌐 Your application is available at: https://$DOMAIN${NC}"
else
    echo -e "${YELLOW}⚠️  Checking container status...${NC}"
    docker compose -f docker-compose.prod.yml ps
    echo -e "${YELLOW}⚠️  Checking logs...${NC}"
    docker compose -f docker-compose.prod.yml logs --tail=50
    echo -e "${YELLOW}⚠️  Trying HTTP check as fallback...${NC}"
    if curl -f http://$DROPLET_IP > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠️  Application running on HTTP but SSL not configured yet${NC}"
        echo -e "${GREEN}🌐 Your application is available at: http://$DROPLET_IP${NC}"
    else
        echo -e "${RED}❌ Health check failed${NC}"
    fi
fi

echo -e "\n${YELLOW}Next steps:${NC}"
echo "1. Configure DNS in Cloudflare:"
echo "   - Point nextgenbeing.com to $DROPLET_IP"
echo "   - Point www.nextgenbeing.com to $DROPLET_IP"
echo "2. Your application will be available at: https://nextgenbeing.com"
echo "3. Set up monitoring and alerting"
echo "4. Configure regular backups"

echo -e "\n${YELLOW}Useful commands:${NC}"
echo "View logs: docker compose -f docker-compose.prod.yml logs"
echo "View specific service logs: docker compose -f docker-compose.prod.yml logs ngb-app"
echo "Restart services: docker compose -f docker-compose.prod.yml restart"
echo "Update application: git pull && docker compose -f docker-compose.prod.yml up -d --build"
