# Guía de Deploy en Railway para Laravel + Inertia

## Archivos Creados

1. **nixpacks.toml** - Configuración de build para Railway
2. **start.sh** - Script de inicio con migraciones y optimización
3. **railway.env** - Variables de entorno de ejemplo

## Pasos para Deploy

### 1. Configurar Variables de Entorno en Railway

En el dashboard de Railway, configura estas variables:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app.railway.app
ASSET_URL=https://tu-app.railway.app
APP_KEY=base64:tu-clave-generada
DB_CONNECTION=pgsql
```

Las variables de base de datos se configuran automáticamente al añadir un servicio PostgreSQL.

### 2. Generar APP_KEY

Ejecuta localmente:
```bash
php artisan key:generate --show
```

Copia el resultado y pégalo en la variable APP_KEY en Railway.

### 3. Configurar Base de Datos

Añade un servicio PostgreSQL en Railway y las variables DB_* se configurarán automáticamente.

### 4. Deploy

Sube tu código a GitHub y conecta el repositorio a Railway, o sube directamente los archivos.

## Solución de Problemas Comunes

### Pantalla Blanca

1. **Verificar logs**: Revisa los logs en Railway
2. **Assets**: Asegúrate que `ASSET_URL` esté configurada correctamente
3. **Build**: Confirma que `npm run build` se ejecutó sin errores
4. **Permisos**: El script start.sh debe ser ejecutable

### Errores Comunes

- **500 Internal Server Error**: Revisa logs de migración
- **404 Not Found**: Verifica configuración de rutas
- **Assets no cargan**: Confirma ASSET_URL y build de Vite

## Comandos Útiles

### Debug Local
```bash
# Probar build localmente
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

### Verificar Configuración
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Notas Importantes

- Railway usa PHP 8.5 según tu composer.json
- Los assets se construyen en `public/build`
- El script espera una base de datos PostgreSQL
- Se optimiza automáticamente para producción
