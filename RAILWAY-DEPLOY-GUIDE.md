# Guía de Deploy Multi-Servicio en Railway para Laravel + Inertia

## Arquitectura de Servicios

Necesitarás **4 servicios separados** en Railway:

1. **App Service** - Laravel + Inertia (nixpacks.toml)
2. **Reverb Service** - WebSocket server (nixpacks-reverb.toml)
3. **Horizon Service** - Queue workers (nixpacks-horizon.toml)
4. **Cron Service** - Scheduler (nixpacks-cron.toml)

## Configuración por Servicio

### 1. App Service (Principal)

**Archivo**: `nixpacks.toml`
**Script**: `start.sh`
**Puerto**: `$PORT` (Railway asigna automáticamente)

### 2. Reverb Service

**Archivo**: `nixpacks-reverb.toml`
**Script**: `reverb-start.sh`
**Puerto**: 8080

### 3. Horizon Service

**Archivo**: `nixpacks-horizon.toml`
**Script**: `horizon-start.sh`
**Propósito**: Queue workers y dashboard

### 4. Cron Service

**Archivo**: `nixpacks-cron.toml`
**Script**: `cron-start.sh`
**Propósito**: Ejecutar tareas programadas

## Variables de Entorno

Configura estas variables en **TODOS** los servicios:

```bash
APP_NAME=AquaShield
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app.railway.app
ASSET_URL=https://tu-app.railway.app
APP_KEY=base64:tu-clave-generada
DB_CONNECTION=pgsql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
FILESYSTEM_DISK=r2
```

### Variables Adicionales por Servicio

**App Service**:
```bash
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="reverb-service-url.railway.app"
VITE_REVERB_PORT="8080"
```

**Reverb Service**:
```bash
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_APP_ID=203142
REVERB_APP_KEY=k6hobagw3c87bamlpzhg
REVERB_APP_SECRET=552iyp9hghmu7zn5rm0q
```

## Servicios Adicionales Requeridos

### 1. PostgreSQL Database
- Añade servicio PostgreSQL
- Variables DB_* se configuran automáticamente

### 2. Redis Cache
- Añade servicio Redis 
- Conectar a: `redis:6379`

## Pasos de Deploy

### 1. Crear Servicios en Railway

1. **App Service**: Usa `nixpacks.toml`
2. **Reverb Service**: Usa `nixpacks-reverb.toml` 
3. **Horizon Service**: Usa `nixpacks-horizon.toml`
4. **Cron Service**: Usa `nixpacks-cron.toml`

### 2. Configurar Variables

Copia las variables de `railway.env` al dashboard de Railway.

### 3. Configurar Conexiones

**App Service → Reverb**:
```bash
VITE_REVERB_HOST=https://reverb-service.railway.app
```

**Todos los servicios → Database/Redis**:
Las variables Railway se configuran automáticamente

### 4. Deploy Order

1. **Database y Redis** primero
2. **Horizon y Cron** después  
3. **Reverb** luego
4. **App Service** al final

## Verificación

### Check List
- [ ] App Service carga frontend correctamente
- [ ] Reverb Service escucha en puerto 8080
- [ ] Horizon dashboard accesible en `/horizon`
- [ ] Logs muestran conexión a Redis/DB
- [ ] Assets cargan desde ASSET_URL

### Debug Commands
```bash
# Ver logs de cada servicio
railway logs service-name

# Acceder a Horizon
https://tu-app.railway.app/horizon

# Probar WebSocket
console.log(Echo.connector.pusher.connection)
```

## Solución de Problemas

### Pantalla Blanca
1. Verificar ASSET_URL
2. Revisar logs del App Service
3. Confirmar build exitoso

### WebSocket No Conecta
1. Verificar Reverb Service logs
2. Confirmar VITE_REVERB_* variables
3. Chequear firewall/ports

### Queue No Procesa
1. Verificar Horizon Service logs
2. Confirmar conexión Redis
3. Revisar configuración QUEUE_CONNECTION

### Cron No Ejecuta
1. Verificar Cron Service logs
2. Confirmar tareas en `app/Console/Kernel.php`
3. Revisar conexión a servicios

## Notas Importantes

- Cada servicio necesita su propio `nixpacks-*.toml`
- Comparte variables entre servicios
- Usa Railway environment variables para DB/Redis
- Monitoriza logs individuales por servicio
