# AquaShield CRM — Railway Deployment Guide

> **Stack:** Laravel 13 + Inertia 2.0 + React 19 + Tailwind CSS 4 + PHP 8.5

---

## Architecture (4 Services)

| Service | Config File | Start Script | Port |
|---------|------------|-------------|------|
| **App** (web) | `nixpacks.toml` | `start.sh` | `$PORT` (auto) |
| **Worker** (Horizon) | `nixpacks-worker.toml` | `run-worker.sh` | N/A |
| **Cron** (Scheduler) | `nixpacks-cron.toml` | `run-cron.sh` | N/A |
| **Reverb** (WebSocket) | `nixpacks-reverb.toml` | `run-reverb.sh` | 8080 |

---

## Step 1: Create Railway Project

1. Click **New** → **Empty Project**
2. Add **PostgreSQL** database service
3. Add **Redis** service

---

## Step 2: Create App Service

1. Click **Create** → **Empty Service**
2. Go to **Settings → Source** → Connect your GitHub repo
3. In **Settings → Build**:
   - **Builder:** Nixpacks (uses `nixpacks.toml` automatically)
   - OR switch to **Railpack (beta)** for FrankenPHP auto-config
4. In **Settings → Networking**:
   - Click **Generate Domain** to get a public URL

---

## Step 3: Environment Variables (App Service)

Go to **Variables → Raw Editor** and paste:

```env
APP_NAME=AquaShield
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://YOUR-DOMAIN.railway.app
ASSET_URL=https://YOUR-DOMAIN.railway.app

# Database (use Railway reference variables)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Cache & Queue
CACHE_STORE=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis
REDIS_URL=${{Redis.REDIS_URL}}

# Logs
LOG_CHANNEL=errorlog
LOG_LEVEL=error

# Storage
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=your-r2-key
R2_SECRET_ACCESS_KEY=your-r2-secret
R2_DEFAULT_REGION=auto
R2_BUCKET=aquashield-bucket
R2_ENDPOINT=https://your-account.r2.cloudflarestorage.com
R2_URL=https://your-account.r2.cloudflarestorage.com
R2_USE_PATH_STYLE_ENDPOINT=false

# Vite build-time vars (MUST exist during build)
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY=your-reverb-key
VITE_REVERB_HOST=reverb-service.railway.app
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

# Google Maps
PUBLIC_GOOGLE_MAPS_API_KEY=your-google-maps-key
VITE_TURNSTILE_SITE_KEY=your-turnstile-key
```

> **CRITICAL:** Replace `YOUR-DOMAIN.railway.app` with your actual Railway public domain.
> Generate APP_KEY locally: `./vendor/bin/sail artisan key:generate --show`

---

## Step 4: Create Secondary Services

For each additional service (Worker, Cron, Reverb):

1. In Railway, right-click the App Service → **Duplicate**
2. Rename appropriately
3. In **Settings → Build → Config Path**, set the correct toml file:
   - Worker: `nixpacks-worker.toml`
   - Cron: `nixpacks-cron.toml`
   - Reverb: `nixpacks-reverb.toml`
4. In **Settings → Deploy → Custom Start Command**, set:
   - Worker: `chmod +x ./run-worker.sh && ./run-worker.sh`
   - Cron: `chmod +x ./run-cron.sh && ./run-cron.sh`
   - Reverb: `chmod +x ./run-reverb.sh && ./run-reverb.sh`

### Reverb Service extra config:
- **Settings → Networking** → Generate Domain
- Set env var: `REVERB_HOST=0.0.0.0` and `REVERB_PORT=8080`
- Use the Reverb domain in the App Service's `VITE_REVERB_HOST`

---

## Step 5: Deploy Order

1. **PostgreSQL + Redis** → auto-start
2. **Worker + Cron + Reverb** → deploy
3. **App Service** → deploy last

---

## Troubleshooting

### Styles/CSS not loading
1. Check Railway build logs for `npm run build` output — look for `vite build` success
2. Verify `ASSET_URL` matches your actual public domain
3. View page source → check `<link>` and `<script>` URLs
4. Browser DevTools → Network tab → look for 404s on `.css`/`.js`
5. Ensure `public/build/manifest.json` was created (check build logs)

### Forms not working
1. Usually means **JS didn't load** → Inertia can't render React components
2. Fix asset loading first (see above)
3. Verify `APP_URL` matches public domain (CSRF relies on this)
4. Check browser console for errors

### Blank white page
1. Set `APP_DEBUG=true` temporarily to see error messages
2. Check if `ASSET_URL` resolves correctly
3. Verify `APP_KEY` is set

### Database connection error during build
`SQLSTATE: could not translate host name "postgres.railway.internal"`
- This is normal — Railway internal DNS isn't available at build time
- Don't run `php artisan migrate` during build — it runs at start time in `start.sh`
- Don't run `php artisan config:cache` during build if it requires DB

### Mixed content errors
- `URL::forceScheme('https')` is in `AppServiceProvider.php`
- Ensure `APP_URL` starts with `https://`
- Ensure `ASSET_URL` starts with `https://`

---

## Alternative: Railpack Builder

If Nixpacks gives issues, switch to **Railpack (beta)** in Settings → Build:
- Uses **FrankenPHP** instead of nginx + php-fpm
- Auto-detects Laravel, runs `npm run build`, `php artisan optimize`, `php artisan migrate --force`
- No `nixpacks.toml` or `start.sh` needed
- Just set environment variables correctly

---

## Verify Deployment

- [ ] App loads with styles (Tailwind CSS renders)
- [ ] Login form works (Inertia + React rendering)
- [ ] Reverb WebSocket connects (check browser console)
- [ ] Horizon dashboard at `/horizon`
- [ ] Cron logs show scheduler running
- [ ] R2 file uploads work
