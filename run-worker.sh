#!/bin/bash
# ──────────────────────────────────────────────
# Railway Worker Service (Horizon)
# ──────────────────────────────────────────────
set -e

echo "==> Starting Horizon queue worker..."
php artisan horizon
