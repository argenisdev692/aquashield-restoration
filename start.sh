#!/bin/bash
# ──────────────────────────────────────────────
# Railway App Service start script
# Runs migrations, caches config, starts server
# ──────────────────────────────────────────────
set -e

echo "==> Waiting for database..."
RETRIES=30
until nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null || [ $RETRIES -eq 0 ]; do
    echo "    Waiting for DB at $DB_HOST:$DB_PORT... ($RETRIES left)"
    RETRIES=$((RETRIES - 1))
    sleep 2
done

if [ $RETRIES -eq 0 ]; then
    echo "WARNING: Could not connect to DB, continuing anyway..."
fi

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Removing hot file (dev server artifact)..."
rm -f public/hot

echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "==> Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Starting server on port ${PORT:-8080}..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
