#!/bin/bash

CF_API_TOKEN="${CLOUDFLARE_API_TOKEN}"
CF_ZONE_ID="${CLOUDFLARE_ZONE_ID}"
DROPLET_IP="${DROPLET_IP}"

if [ -z "$CF_API_TOKEN" ] || [ -z "$CF_ZONE_ID" ] || [ -z "$DROPLET_IP" ]; then
    echo "❌ Please set CLOUDFLARE_API_TOKEN, CLOUDFLARE_ZONE_ID, and DROPLET_IP environment variables"
    exit 1
fi

echo "🌐 Setting up Cloudflare DNS..."

# Create A record for main domain
curl -X POST "https://api.cloudflare.com/client/v4/zones/$CF_ZONE_ID/dns_records" \
     -H "Authorization: Bearer $CF_API_TOKEN" \
     -H "Content-Type: application/json" \
     --data '{
       "type": "A",
       "name": "nextgenbeing.com",
       "content": "'$DROPLET_IP'",
       "ttl": 1,
       "proxied": true
     }'

# Create A record for www
curl -X POST "https://api.cloudflare.com/client/v4/zones/$CF_ZONE_ID/dns_records" \
     -H "Authorization: Bearer $CF_API_TOKEN" \
     -H "Content-Type: application/json" \
     --data '{
       "type": "A",
       "name": "www",
       "content": "'$DROPLET_IP'",
       "ttl": 1,
       "proxied": true
     }'

echo "✅ Cloudflare DNS setup completed!"
