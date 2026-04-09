#!/bin/bash
# Railway deployment script for Laravel 13 + Inertia + React 19 + PHP 8.5
# This script ensures assets are built correctly before deployment

set -e

echo "🚀 Preparing for Railway deployment..."

# Remove hot file to prevent Laravel from trying to use dev server in production
if [ -f "public/hot" ]; then
    echo "🗑️  Removing public/hot file..."
    rm -f public/hot
fi

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear

# Install dependencies
echo "📦 Installing Node dependencies..."
npm install

# Build assets for production
echo "🔨 Building assets with Vite..."
npm run build

# Verify build directory exists
if [ ! -d "public/build" ]; then
    echo "❌ Error: public/build directory not found after build"
    exit 1
fi

echo "✅ Build completed successfully!"
echo "📦 Assets are ready in public/build/"
echo "🚀 Ready to deploy to Railway"
