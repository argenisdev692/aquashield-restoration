#!/bin/bash

# Script de inicio para Railway - Optimizado para producción con Supabase

if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
  export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
  export ASSET_URL="${APP_URL}"
fi

# Forzar entorno de producción
export APP_ENV="production"
export APP_DEBUG="false"

# Con Supabase no necesitamos esperar a la base de datos (servicio externo)
echo "Usando Supabase como base de datos externa..."

# Limpiar cachés antes de optimizar
echo "Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Ejecutar migraciones (no fatal: el servidor debe arrancar aunque fallen)
echo "Ejecutando migraciones..."
php artisan migrate --force || echo "WARN: migrate falló, continuando..."

# Optimizar la aplicación para producción (no fatal)
echo "Optimizando aplicación..."
php artisan config:cache  || echo "WARN: config:cache falló"
php artisan route:cache   || echo "WARN: route:cache falló"
php artisan view:cache    || echo "WARN: view:cache falló"
php artisan event:cache   || echo "WARN: event:cache falló"

# Verificar que los assets existen
if [ ! -d "public/build" ]; then
  echo "ERROR: No se encontraron assets compilados en public/build"
  echo "Ejecutando build de emergencia..."
  npm run build || echo "ERROR: Falló el build de emergencia"
fi

# Iniciar el servidor usando php -S con document root en public/
PORT="${PORT:-8080}"
echo "Iniciando servidor en 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t public public/index.php
