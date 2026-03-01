# Guía de Rutas - Referencia Rápida

## 🌐 Rutas Web (Navegador - Inertia + React Query)

### Company Data
**Páginas Inertia:**
- `GET /company-data` → Lista de empresas
- `GET /company-data/create` → Formulario crear
- `GET /company-data/{uuid}` → Ver detalle
- `GET /company-data/{uuid}/edit` → Formulario editar

**Endpoints JSON (para React Query):**
- `GET /company-data/data/admin` → Listar (con filtros)
- `POST /company-data/data/admin` → Crear
- `GET /company-data/data/admin/{uuid}` → Ver uno
- `PUT /company-data/data/admin/{uuid}` → Actualizar
- `DELETE /company-data/data/admin/{uuid}` → Eliminar
- `PATCH /company-data/data/admin/{uuid}/restore` → Restaurar
- `GET /company-data/data/admin/export` → Exportar
- `GET /company-data/data/me` → Perfil actual
- `PUT /company-data/data/me` → Actualizar perfil

**Middleware:** `web`, `auth` (sesión web)

---

### Users
**Páginas Inertia:**
- `GET /users` → Lista de usuarios
- `GET /users/create` → Formulario crear
- `GET /users/{uuid}` → Ver detalle
- `GET /users/{uuid}/edit` → Formulario editar

**Endpoints JSON (para React Query):**
- `GET /users/data/admin` → Listar (con filtros)
- `POST /users/data/admin` → Crear
- `GET /users/data/admin/{uuid}` → Ver uno
- `PUT /users/data/admin/{uuid}` → Actualizar
- `DELETE /users/data/admin/{uuid}` → Eliminar
- `PATCH /users/data/admin/{uuid}/restore` → Restaurar
- `POST /users/data/admin/{uuid}/suspend` → Suspender
- `POST /users/data/admin/{uuid}/activate` → Activar
- `GET /users/data/admin/export` → Exportar
- `GET /users/data/profile` → Perfil actual
- `PUT /users/data/profile` → Actualizar perfil

**Middleware:** `web`, `auth`, `role:SUPER_ADMIN` (para admin)

---

## 📱 API REST (Mobile - Sanctum)

### Company Data
- `GET /api/company-data/admin` → Listar
- `POST /api/company-data/admin` → Crear
- `GET /api/company-data/admin/{uuid}` → Ver uno
- `PUT /api/company-data/admin/{uuid}` → Actualizar
- `DELETE /api/company-data/admin/{uuid}` → Eliminar
- `PATCH /api/company-data/admin/{uuid}/restore` → Restaurar
- `GET /api/company-data/me` → Perfil actual
- `PUT /api/company-data/me` → Actualizar perfil

**Middleware:** `api`, `auth:sanctum`

---

### Users
- `GET /api/users/admin` → Listar
- `POST /api/users/admin` → Crear
- `GET /api/users/admin/{uuid}` → Ver uno
- `PUT /api/users/admin/{uuid}` → Actualizar
- `DELETE /api/users/admin/{uuid}` → Eliminar
- `PATCH /api/users/admin/{uuid}/restore` → Restaurar
- `POST /api/users/admin/{uuid}/suspend` → Suspender
- `POST /api/users/admin/{uuid}/activate` → Activar
- `GET /api/users/profile` → Perfil actual
- `PUT /api/users/profile` → Actualizar perfil

**Middleware:** `api`, `auth:sanctum`, `role:super-admin` (para admin)

---

## ⚠️ Errores Comunes

### 401 Unauthorized en `/api/*`
**Causa:** Estás intentando acceder a rutas API desde el navegador sin token Sanctum.
**Solución:** Usa las rutas web `/company-data/data/*` o `/users/data/*` en su lugar.

### 404 Not Found
**Causa:** Cache de rutas desactualizado.
**Solución:** Ejecuta `clear-cache.bat` o manualmente:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔑 Diferencias Clave

| Aspecto | Rutas Web | Rutas API |
|---------|-----------|-----------|
| **Prefijo** | `/company-data/data/*` | `/api/company-data/*` |
| **Autenticación** | Sesión web (cookies) | Token Sanctum (Bearer) |
| **Middleware** | `web`, `auth` | `api`, `auth:sanctum` |
| **Uso** | Frontend React (navegador) | Apps mobile, APIs externas |
| **CSRF** | Requerido | No requerido |

---

## 📝 Notas

- Las rutas web con prefijo `/data` son endpoints JSON internos usados por React Query
- Las páginas Inertia (sin `/data`) renderizan componentes React
- Las rutas API (`/api/*`) son para consumo externo (mobile, terceros)
- Todos los hooks de React Query ya están configurados para usar `/data`


---

## 🔧 Problemas Corregidos

### 1. Middleware `role` no registrado
**Error:** `Target class [role] does not exist`
**Solución:** Agregados los middleware aliases de Spatie Permission en `bootstrap/app.php`:
- `role` → `RoleMiddleware`
- `permission` → `PermissionMiddleware`
- `role_or_permission` → `RoleOrPermissionMiddleware`

### 2. DTOs y ReadModels con `readonly` incompatible
**Error:** `Readonly class cannot extend non-readonly class`
**Solución:** Removido `readonly` de todos los DTOs y ReadModels que extienden `Spatie\LaravelData\Data`:

**DTOs:**
- `CompanyDataFilterDTO`
- `CreateCompanyDataDTO`
- `UpdateCompanyDataDTO`
- `UserFilterDTO`
- `CreateUserDTO`
- `UpdateUserDTO`
- `UserSummaryDTO`

**ReadModels:**
- `UserListReadModel`
- `UserReadModel`
- `UserProfileReadModel`
- `CompanyDataReadModel`

**Lección:** Una clase `readonly` solo puede extender otra clase `readonly`. Como `Spatie\LaravelData\Data` no es readonly, los DTOs y ReadModels que la extienden tampoco pueden serlo.

### 3. AggregateRoot y Entidades con `readonly`
**Error:** `Readonly property cannot have default value`, `Readonly class cannot extend non-readonly class`, y `Type must be mixed`
**Solución:** 
- Removido `readonly` de `AggregateRoot` porque necesita mantener un array mutable de eventos de dominio
- Removido la propiedad `$id` de `AggregateRoot` para permitir que cada entidad defina su propio tipo de ID
- Removido `readonly` de todas las entidades que extienden `AggregateRoot`:
  - `User` (con `UserId $id`)
  - `CompanyData` (con `CompanyDataId $id`)

**Lección:** 
- Las clases `readonly` son para objetos inmutables
- `AggregateRoot` necesita mutar su estado interno (array de eventos), por lo tanto no puede ser `readonly`
- Las entidades que extienden `AggregateRoot` tampoco pueden ser `readonly` (restricción de herencia de PHP)
- No se puede redefinir una propiedad con un tipo diferente al de la clase padre
- Los Value Objects, Events, y entidades simples que no extienden nada SÍ pueden ser `readonly`

### 3. Rutas reorganizadas
**Cambio:** Separación clara entre rutas web y API:
- Rutas web: `/company-data/data/*` y `/users/data/*` (sesión web)
- Rutas API: `/api/company-data/*` y `/api/users/*` (token Sanctum)

### 4. Frontend actualizado
**Cambio:** Todos los hooks de React Query actualizados para usar el prefijo `/data`:
- `useCompanyData.ts`
- `useCompanies.ts`
- `useCompanyDataMutations.ts`
- `useUser.ts`
- `useUsers.ts`
- `useUserMutations.ts`

---

## 🚀 Próximos Pasos

1. **Limpiar cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

2. **Verificar rutas:**
   ```bash
   php artisan route:list --path=company-data
   php artisan route:list --path=users
   ```

3. **Compilar frontend:**
   ```bash
   npm run build
   ```
   O para desarrollo:
   ```bash
   npm run dev
   ```

4. **Verificar permisos:**
   Asegúrate de que tu usuario tenga el rol `SUPER_ADMIN` para acceder a las rutas admin.
