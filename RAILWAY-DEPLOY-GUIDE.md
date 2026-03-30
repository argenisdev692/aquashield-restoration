# Guía de Deploy en Railway para Laravel + Inertia

## Archivos de Configuración

1. **nixpacks.toml** - Build + variables de entorno de Railway
2. **start.sh** - Startup: migraciones, cache, servidor
3. **railway.toml** - Configuración del servicio Railway

## Pasos para Deploy

### 1. Variables de Entorno en Railway Dashboard

Estas variables DEBEN configurarse manualmente en el dashboard (no las cubre nixpacks.toml):

```bash
APP_KEY=base64:tu-clave-generada   # php artisan key:generate --show

# Base de datos (PostgreSQL recomendado)
DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

# Redis (para cache y queues)
REDIS_HOST=...
REDIS_PORT=6379
REDIS_PASSWORD=...

# Cloudflare R2 (almacenamiento de archivos)
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=...
AWS_ENDPOINT=https://....r2.cloudflarestorage.com
```

Las variables `APP_URL`, `ASSET_URL`, `APP_ENV`, `APP_DEBUG` ya están en `nixpacks.toml`.

### 2. Deploy

Conecta el repo de GitHub en Railway y el deploy es automático.
Nixpacks ejecuta `composer install && npm install && npm run build` en el build phase.

---

## Bugs Conocidos y Sus Fixes (IMPORTANTE)

### Bug 1 — `buildDirectory: 'public/build'` en vite.config.ts ✅ FIXED

**Síntoma:** Pantalla azul / JS no carga. CSS puede cargar pero React no monta.

**Causa raíz:** `buildDirectory` en `laravel-vite-plugin` es relativo a `public/`.
Ponerlo como `'public/build'` causaba que el build saliera a `public/public/build/`
y las URLs de assets fueran `/public/build/assets/...` en lugar de `/build/assets/...`.

**Fix aplicado:** Eliminado `buildDirectory: 'public/build'` de `vite.config.ts`.
Ahora usa el default `'build'` → build correcto en `public/build/`.

### Bug 2 — `ASSET_URL` sin `https://` en nixpacks.toml ✅ FIXED

**Síntoma:** Assets con URLs inválidas como `myapp.railway.app/build/assets/app.js`
(el browser lo trata como ruta relativa → 404).

**Causa raíz:** `RAILWAY_PUBLIC_DOMAIN` retorna solo el dominio sin protocolo.

**Fix aplicado:** `ASSET_URL = "https://${RAILWAY_PUBLIC_DOMAIN}"` en nixpacks.toml.

### Bug 3 — `public/hot` puede existir en producción

**Síntoma:** Laravel apunta al dev server `localhost:5173` en producción.

**Fix aplicado:** `start.sh` ejecuta `rm -f public/hot` antes de cachear config.

---

## Solución de Problemas

### Pantalla azul (CSS carga pero JS no)
1. Verificar en browser DevTools → Network que los assets `/build/assets/*.js` retornen 200
2. Si retornan 404: revisar que `npm run build` corrió en los logs de Railway
3. Si la URL del asset incluye `/public/build/...`: el bug 1 no está aplicado
4. Si la URL empieza con `myapp.railway.app/...` sin https: el bug 2 no está aplicado

### 500 en Railway
```bash
# Ver logs en Railway CLI
railway logs
```

### Regenerar APP_KEY localmente
```bash
./vendor/bin/sail artisan key:generate --show
```

## Flujo de Build en Nixpacks

```
1. nixpacks build phase:
   - composer install --no-dev --optimize-autoloader
   - npm install
   - npm run build  → genera public/build/ con assets + manifest.json

2. start.sh (runtime):
   - Espera DB disponible
   - php artisan migrate --force
   - rm -f public/hot
   - php artisan config:cache / route:cache / view:cache / event:cache
   - php artisan serve --host=0.0.0.0 --port=$PORT
```
