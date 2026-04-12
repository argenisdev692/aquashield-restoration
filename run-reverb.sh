#!/bin/bash
# ──────────────────────────────────────────────
# Railway Reverb Service (WebSocket)
# ──────────────────────────────────────────────
set -e

echo "==> Starting Reverb WebSocket server..."
php artisan reverb:start --host=0.0.0.0 --port="${REVERB_PORT:-8080}"
