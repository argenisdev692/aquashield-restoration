#!/usr/bin/env bash
set -e

# Railway Deploy Script for AQUASHIELD-CRM
# Laravel 13 + Inertia + React + PHP 8.5

echo "=== AQUASHIELD-CRM Railway Deploy ==="

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "Railway CLI not found. Installing..."
    npm install -g @railway/cli
fi

# Check if logged in
if ! railway whoami &> /dev/null; then
    echo "Please login to Railway:"
    railway login
fi

# Link project if not already linked
if [ ! -f .railway/config.json ]; then
    echo "Linking to Railway project..."
    railway link
fi

# Set required environment variables
echo "Setting up environment variables..."
railway variables --set "APP_ENV=production"
railway variables --set "APP_DEBUG=false"
railway variables --set "APP_KEY=base64:YOUR_APP_KEY_HERE"
railway variables --set "ASSET_URL=\${RAILWAY_STATIC_URL}"

# Deploy
echo "Deploying to Railway..."
railway up

echo "=== Deploy completed ==="
echo "Run 'railway logs' to see the application logs"
