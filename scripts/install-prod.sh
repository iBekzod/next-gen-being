#!/bin/bash
set -e

echo "🚀 Installing NextGenBeing Production Environment"

if [[ $EUID -eq 0 ]]; then
   echo "❌ Do not run as root"
   exit 1
fi

echo "📦 Updating system..."
sudo apt update && sudo apt upgrade -y

echo "🐳 Installing Docker..."
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
sudo usermod -aG docker $USER

echo "🛠️ Installing tools..."
sudo apt install -y git curl wget unzip htop fail2ban ufw certbot awscli

echo "🔥 Configuring firewall..."
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw --force enable

echo "🛡️ Configuring fail2ban..."
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

echo "✅ Installation completed!"
echo "Next: Configure .env.production and run deployment"
