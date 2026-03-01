# Resumen de Correcciones de `readonly`

## Problema General
PHP 8.2+ introdujo clases `readonly`, pero tienen restricciones estrictas:
1. Una clase `readonly` solo puede extender otra clase `readonly`
2. Las propiedades `readonly` no pueden tener valores por defecto
3. Las clases `readonly` no pueden mutar su estado interno

## Correcciones Realizadas

### ✅ DTOs y ReadModels (extienden `Spatie\LaravelData\Data`)
**Archivos corregidos:**
- `src/Modules/CompanyData/Application/DTOs/CompanyDataFilterDTO.php`
- `src/Modules/CompanyData/Application/DTOs/CreateCompanyDataDTO.php`
- `src/Modules/CompanyData/Application/DTOs/UpdateCompanyDataDTO.php`
- `src/Modules/Users/Application/DTOs/UserFilterDTO.php`
- `src/Modules/Users/Application/DTOs/CreateUserDTO.php`
- `src/Modules/Users/Application/DTOs/UpdateUserDTO.php`
- `src/Modules/Users/Contracts/DTOs/UserSummaryDTO.php`
- `src/Modules/Users/Application/Queries/ReadModels/UserListReadModel.php`
- `src/Modules/Users/Application/Queries/ReadModels/UserReadModel.php`
- `src/Modules/Users/Application/Queries/ReadModels/UserProfileReadModel.php`
- `src/Modules/CompanyData/Application/Queries/ReadModels/CompanyDataReadModel.php`

**Cambio:** Removido `readonly` de la declaración de clase
**Razón:** `Spatie\LaravelData\Data` no es `readonly`

### ✅ AggregateRoot
**Archivo corregido:**
- `src/Shared/Domain/Entities/AggregateRoot.php`

**Cambios:**
1. Removido `readonly` de la clase
2. Removida la propiedad `public mixed $id`

**Razón:** 
- Necesita mantener un array mutable de eventos de dominio
- Permite que cada entidad defina su propio tipo de ID

### ✅ Entidades de Dominio (extienden `AggregateRoot`)
**Archivos corregidos:**
- `src/Modules/Users/Domain/Entities/User.php`
- `src/Modules/CompanyData/Domain/Entities/CompanyData.php`

**Cambios:**
1. Removido `readonly` de la declaración de clase
2. Removido `#[\Override]` de la propiedad `$id`

**Razón:** No pueden ser `readonly` porque extienden `AggregateRoot` (no-readonly)

## Clases que SÍ mantienen `readonly`

### ✅ Value Objects
- `src/Shared/Domain/ValueObjects/UuidValueObject.php` (readonly)
- `src/Shared/Domain/ValueObjects/IntValueObject.php` (readonly)
- `src/Shared/Domain/ValueObjects/StringValueObject.php` (readonly)
- Todos los Value Objects específicos que los extienden

**Razón:** Son inmutables por naturaleza y las clases base son `readonly`

### ✅ Domain Events
- `src/Shared/Domain/Events/DomainEvent.php` (readonly)
- Todos los eventos específicos que lo extienden

**Razón:** Son inmutables por naturaleza y la clase base es `readonly`

### ✅ Entidades Simples
- `src/Modules/Users/Domain/Entities/UserPreferences.php`
- `src/Modules/Users/Domain/Entities/UserProfile.php`
- `src/Modules/Users/Domain/Entities/UserActivity.php`

**Razón:** No extienden ninguna clase, son standalone

## Reglas para el Futuro

### ✅ USAR `readonly` cuando:
1. La clase es un Value Object
2. La clase es un Domain Event
3. La clase es una entidad simple que no extiende nada
4. La clase es verdaderamente inmutable

### ❌ NO USAR `readonly` cuando:
1. La clase extiende `Spatie\LaravelData\Data`
2. La clase extiende `AggregateRoot`
3. La clase necesita mutar su estado interno
4. La clase tiene propiedades con valores por defecto
5. La clase extiende cualquier clase no-readonly

## Verificación

Para verificar que no hay más problemas de `readonly`:

```bash
# Buscar clases readonly que extienden Data
grep -r "readonly class.*extends Data" src/

# Buscar clases readonly que extienden AggregateRoot
grep -r "readonly class.*extends AggregateRoot" src/

# Buscar propiedades readonly con valores por defecto
grep -r "readonly.*=.*;" src/
```

Todos estos comandos deben retornar 0 resultados.


---

## Correcciones Adicionales (Sesión 2)

### ✅ Corrección de Fechas en ListUsersHandler
**Archivo corregido:**
- `src/Modules/Users/Application/Queries/ListUsers/ListUsersHandler.php`

**Problema:** 
- El código intentaba llamar `->toISOString()` en strings (las fechas ya venían formateadas del mapper)
- Los nombres de propiedades no coincidían (snake_case vs camelCase)

**Cambios:**
```php
// Antes (incorrecto):
createdAt: $user->created_at?->toISOString() ?? '',
profilePhotoPath: $user->profile_photo_path,

// Después (correcto):
createdAt: $user->createdAt ?? '',
profilePhotoPath: $user->profilePhotoPath,
```

**Razón:** El `UserMapper::toDomain()` ya convierte las fechas Carbon a strings ISO8601, y la entidad de dominio usa camelCase.

### ✅ Mejora de Gestión de Caché
**Archivos corregidos:**
- `src/Modules/Users/Application/Commands/DeleteUser/DeleteUserHandler.php`
- `src/Modules/Users/Application/Commands/RestoreUser/RestoreUserHandler.php`
- `src/Modules/Users/Application/Queries/ListUsers/ListUsersHandler.php`

**Cambios:**
1. Agregado soporte para cache tags en `ListUsersHandler`
2. Agregado flush de cache tags en `DeleteUserHandler` y `RestoreUserHandler`
3. Fallback a cache regular si tags no están soportados

**Razón:** Asegurar que la UI se actualice inmediatamente después de mutaciones (delete/restore).

### ✅ Corrección de Iniciales en Frontend
**Archivo corregido:**
- `resources/js/pages/users/UsersIndexPage.tsx`

**Problema:** La función de iniciales mostraba "?" cuando name o lastName eran null/empty.

**Cambio:**
```typescript
// Antes:
const initials = React.useCallback((name: string, lastName: string): string => {
  const f = name?.trim().charAt(0).toUpperCase() ?? '';
  const l = lastName?.trim().charAt(0).toUpperCase() ?? '';
  const result = (f + l).trim();
  return result || (name?.trim().charAt(0).toUpperCase() ?? 'U');
}, []);

// Después:
const initials = React.useCallback((name: string, lastName: string): string => {
  if (!name && !lastName) return 'U';
  const f = (name || '').trim().charAt(0).toUpperCase();
  const l = (lastName || '').trim().charAt(0).toUpperCase();
  return f && l ? f + l : f || l || 'U';
}, []);
```

**Razón:** Manejar correctamente casos edge donde name o lastName son null/undefined/empty.

## Estado Final

✅ Todas las fechas se muestran correctamente en las tablas
✅ Las iniciales se muestran correctamente para todos los casos
✅ El modal de eliminación muestra nombre y email correctamente
✅ El caché se invalida correctamente después de mutaciones
✅ Las filas eliminadas muestran fondo rojo con opacidad
✅ Los botones de acción son condicionales (View/Edit/Delete para activos, View/Restore para eliminados)
