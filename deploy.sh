#!/bin/bash

# ============================================
# CalaiaPH Deployment Script
# Run this script on your server via PuTTY
# Usage: bash deploy.sh
# ============================================

set -e

echo "🚀 Starting deployment..."

# Go to project directory (UPDATE THIS PATH TO YOUR ACTUAL PROJECT PATH)
cd /var/www/calaiaph  # Change this to your actual path

# Put application in maintenance mode
echo "🔧 Enabling maintenance mode..."
php artisan down --render="errors::503" --retry=60

# Pull latest changes from git
echo "📥 Pulling latest changes from git..."
git pull origin main
# Or use: git pull origin master (depending on your branch)

# Install/update composer dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install/update npm dependencies
echo "📦 Installing npm dependencies..."
npm install

# Build frontend assets (Vite)
echo "🏗️ Building frontend assets..."
npm run build

# Clear all Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan event:clear

# Cache configuration for production
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Link storage if needed
php artisan storage:link 2>/dev/null || true

# Restart queue workers if you have any
# php artisan queue:restart

# Clear OPcache (if you have opcache installed)
echo "🔄 Clearing OPcache..."
php artisan opcache:clear 2>/dev/null || true

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# Bring application back online
echo "✅ Bringing application back online..."
php artisan up

echo "🎉 Deployment completed successfully!"
echo "📅 $(date)"
