#!/bin/bash

# Script para ejecutar tareas cron
set -e

echo "Iniciando scheduler cron..."

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

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar scheduler cada minuto
while true; do
  php artisan schedule:run
  sleep 60
done
