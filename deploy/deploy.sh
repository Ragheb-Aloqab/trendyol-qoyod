#!/bin/bash
set -e

echo "🚀 Starting Trendyol-Qoyod Deployment..."

# 1. Enter Maintenance Mode
php artisan down || true

# 2. Pull the latest code from GitHub
git pull origin main

# 3. Install/Update Composer dependencies (no-dev for production)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 4. Install/Update NPM packages and build assets
if [ -f "package-lock.json" ]; then
    npm ci --prefer-offline --no-audit
else
    npm install
fi
npm run build

# 5. Run Database Migrations
php artisan migrate --force

# 6. Publish / Upgrade Filament assets
php artisan filament:upgrade

# 7. Ensure Storage Link exists
php artisan storage:link || true

# 8. Optimize and Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 9. Set correct permissions for storage and cache
chmod -R 775 storage bootstrap/cache

# 10. Restart queue workers (if supervisor is running)
php artisan queue:restart || true

# 11. Exit Maintenance Mode
php artisan up

echo "✅ Deployment completed successfully!"
