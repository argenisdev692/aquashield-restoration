#!/bin/bash

# Script de inicio para Railway
set -e

# Esperar a que la base de datos esté disponible
echo "Esperando a la base de datos..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "Base de datos conectada"

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Eliminar hot file para que Laravel no apunte al dev server
rm -f public/hot

# Descubrir paquetes (no se ejecutó en build por --no-scripts)
echo "Descubriendo paquetes..."
php artisan package:discover --ansi

# Optimizar la aplicación para producción
echo "Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Iniciar el servidor
echo "Iniciando servidor..."
php artisan serve --host=0.0.0.0 --port=$PORT
