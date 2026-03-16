#!/bin/bash

# Script para iniciar Reverb WebSocket server
set -e

echo "Iniciando Reverb WebSocket server..."

# Configurar variables de entorno para Reverb
export REVERB_HOST=${REVERB_HOST:-"0.0.0.0"}
export REVERB_PORT=${REVERB_PORT:-"8080"}
export REVERB_SCHEME=${REVERB_SCHEME:-"http"}

# Iniciar Reverb
php artisan reverb:start --host=$REVERB_HOST --port=$REVERB_PORT
