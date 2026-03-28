#!/bin/bash

# Script de inicio para Railway

if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
  export ASSET_URL="${APP_URL}"
fi

# Esperar a que la base de datos esté disponible (solo si DB_HOST está configurado)
if [ -n "$DB_HOST" ] && [ -n "$DB_PORT" ]; then
  echo "Esperando a la base de datos..."
  timeout=30
  while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
    sleep 1
    timeout=$((timeout - 1))
    if [ $timeout -le 0 ]; then
      echo "Timeout esperando base de datos, continuando..."
      break
    fi
  done
  echo "Base de datos conectada"
fi

# Ejecutar migraciones (no fatal: el servidor debe arrancar aunque fallen)
echo "Ejecutando migraciones..."
php artisan migrate --force || echo "WARN: migrate falló, continuando..."

# Optimizar la aplicación para producción (no fatal)
echo "Optimizando aplicación..."
php artisan config:cache  || echo "WARN: config:cache falló"
php artisan route:cache   || echo "WARN: route:cache falló"
php artisan view:cache    || echo "WARN: view:cache falló"
php artisan event:cache   || echo "WARN: event:cache falló"

# Iniciar el servidor usando php -S con document root en public/
PORT="${PORT:-8080}"
echo "Iniciando servidor en 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t public public/index.php
