#!/bin/bash

# Script para iniciar Horizon y Queue Workers
set -e

echo "Iniciando Horizon y Queue Workers..."

# Esperar a que la base de datos esté disponible
echo "Esperando a la base de datos..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done
echo "Base de datos conectada"

# Esperar a que Redis esté disponible
echo "Esperando a Redis..."
while ! nc -z redis 6379; do
  sleep 1
done
echo "Redis conectado"

# Publicar assets de Horizon
php artisan vendor:publish --tag=horizon-assets --force

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar Horizon
php artisan horizon
