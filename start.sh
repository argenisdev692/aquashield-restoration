#!/bin/bash

# Script de inicio para Railway
set -e

if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
  export ASSET_URL="${APP_URL}"
fi

# Esperar a que la base de datos esté disponible (solo si DB_HOST está configurado)
if [ -n "$DB_HOST" ] && [ -n "$DB_PORT" ]; then
  echo "Esperando a la base de datos..."
  timeout=30
  while ! nc -z $DB_HOST $DB_PORT; do
    sleep 1
    timeout=$((timeout - 1))
    if [ $timeout -le 0 ]; then
      echo "Timeout esperando base de datos, continuando..."
      break
    fi
  done
  echo "Base de datos conectada"
fi

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Optimizar la aplicación para producción
echo "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Iniciar el servidor usando php -S con document root en public/
# php -S sirve correctamente archivos estáticos (assets de Vite en public/build/)
echo "Iniciando servidor en 0.0.0.0:${PORT}..."
php -S 0.0.0.0:${PORT} -t public public/index.php
