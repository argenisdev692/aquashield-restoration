---
name: backend-php
description: Primary guide for backend tasks with PHP 8.5 and Laravel 12, including Hexagonal Architecture, SOLID, DDD, Value Objects, Repository Pattern, CQRS, Event Driven Architecture, routes, security, DTOs, mappers, storage, and the project's enterprise conventions.
---

# BACKEND-PHP.md — PHP 8.5 + Laravel 12 · Enterprise Backend Bible (2026)

> **Authority**: This file is the SINGLE SOURCE OF TRUTH for all PHP backend rules.
> **Binary**: `/usr/bin/php8.5` (Sail container). Validate ALL code against this runtime.
> **Stack**: PHP 8.5 · Laravel 12 · Spatie Permission 6.x · Spatie Laravel Data 4.x · Pest 3.x

---

## §0 — PHP 8.5 Strict Protocol

- **Target**: PHP 8.5.0+ exclusively (released Nov 20, 2025). NEVER propose syntax compatible with 8.4 or lower when a native 8.5 improvement exists.
- **Validation gate**: Before writing ANY PHP block, verify: _"Does this syntax exist in 8.5? Am I using the most modern form?"_
- **Legacy code**: If existing project code uses pre-8.5 idioms, do NOT imitate it. Refactor to 8.5 standard immediately.
- **Strict types**: `declare(strict_types=1);` in EVERY `.php` file — no exceptions.
- **PSR-12**: Strict compliance. Every method MUST have an explicit return type.

---

## §1 — PHP 8.5 Features (Genuine)

### Adoption Map — Where to use each feature

> Sources verified: [php.net/migration85](https://www.php.net/manual/en/migration85.new-features.php) · [php.net/releases/8.5](https://www.php.net/releases/8.5/en.php) · laravel-news · stitcher.io

| Feature | Version | Where to apply in this project | Mandatory |
| --- | --- | --- | --- |
| **Pipe operator `\|>`** | PHP 8.5 | `Application/Commands/` handlers, `Infrastructure/Http/Export/*Transformer` (`forExcel`/`forPdf`), `Infrastructure/Http/Resources/` mappers, entity normalization methods | ✅ Yes |
| **Property hooks (`get`/`set`)** | PHP 8.4 ¹ | `Domain/ValueObjects/` — `*Id`, `Email`, `Url`, `Money`, `PhoneNumber`; any VO where the setter must validate an invariant | ✅ Yes |
| **`clone($obj, [...])`** | PHP 8.5 | `Domain/ValueObjects/` wither methods on `readonly` VOs, `Domain/Entities/` immutable update patterns | ✅ Yes |
| **`#[\NoDiscard]`** | PHP 8.5 | `Application/Commands/Create*Handler::handle()` returning UUID, static sanitization methods, `Domain/Services/` methods with meaningful return values | ✅ Yes |
| **`array_first()` / `array_last()`** | PHP 8.5 | `Infrastructure/Persistence/Repositories/` — replaces `reset()`/`end()` on Eloquent collections, `Application/Queries/` handlers inspecting result sets | ✅ Yes |
| **`FILTER_THROW_ON_FAILURE`** | PHP 8.5 | `Domain/ValueObjects/` — `Email`, `Url`, phone VOs using `filter_var()` for validation; replaces manual `=== false` checks | ✅ Yes |
| **FCCs in constant expressions** | PHP 8.5 | `Infrastructure/Http/Export/*Transformer` — declare transformation pipelines as `const PIPELINE = [trim(...), strtolower(...)]` | When needed |
| **`Closure::getCurrent()`** | PHP 8.5 | Anonymous recursive closures in `Application/Commands/` or `Infrastructure/Http/Export/` transformers | When needed |
| **Asymmetric visibility (static)** | PHP 8.5 ² | `Shared/Infrastructure/` singleton adapters, module config classes where the value is set once internally | When needed |
| **Final constructor promotion** | PHP 8.5 | `Domain/Entities/AggregateRoot` — mark `$id` as `final` to prevent child override | When needed |

> **¹** Property hooks were introduced in PHP 8.4. They are available and enforced in this PHP 8.5 project — do not confuse origin with availability.  
> **²** Instance-level asymmetric visibility (`public private(set)`) was PHP 8.4. PHP 8.5 extends this to **static** properties.

**Rule**: Before writing any PHP block, scan this table. Any layer listed as "✅ Yes" must use the corresponding feature — no exceptions, no legacy workarounds.

---

### Pipe Operator (`|>`)

Passes the left expression as the **sole argument** to the right callable. Compiled away — zero runtime overhead.

```php
// ✅ CORRECT — first-class callable syntax
$slug = $title
    |> trim(...)
    |> strtolower(...);

// ✅ CORRECT — multi-argument functions wrapped in arrow function WITH parentheses
$slug = $title
    |> trim(...)
    |> (fn(string $s): string => str_replace(' ', '-', $s))
    |> strtolower(...);

// ✅ All callable types supported
$result = $value
    |> 'strtoupper'                              // string callable
    |> str_shuffle(...)                          // first-class callable
    |> fn($x) => trim($x)                       // arrow function (parens optional if standalone)
    |> (fn($x) => strtolower($x))               // arrow function (parens required in chain)
    |> new MyTransformer()                       // invokable object
    |> [MyClass::class, 'staticMethod']          // static method
    |> my_named_function(...);                   // named function

// ❌ FORBIDDEN — nested function calls when pipe is cleaner
$slug = strtolower(str_replace(' ', '-', trim($title)));
```

**MUST use for**: `Application/Commands/`, `Infrastructure/Http/Resources/`, `Infrastructure/Persistence/Export/`, sanitization pipelines, any sequential transformation.

### Clone With

```php
// ✅ CORRECT — function-style clone (ONLY valid syntax)
$updated = clone($entity, ['status' => 'active', 'updatedAt' => now()->toIso8601String()]);

// ✅ Wither pattern for readonly classes
readonly class Money
{
    public function __construct(public int $amount, public string $currency) {}

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
        return clone($this, ['amount' => $this->amount + $other->amount]);
    }
}

// ❌ WRONG — "clone $obj with [...]" was NOT implemented
$updated = clone $this with ['amount' => 100]; // SYNTAX ERROR

// ❌ WRONG — old boilerplate wither
public function withEmail(string $email): self
{
    $values = get_object_vars($this);
    $values['email'] = $email;
    return new self(...$values);
}
```

**Behavior**: Respects `__clone()`, fires property hooks, honors type/visibility, lifts `readonly` write-once during clone, usable as callable `clone(...)`.

### `#[\NoDiscard]` + `(void)` Cast

```php
#[\NoDiscard("Sanitized data must be captured")]
public static function sanitize(array $input): array
{
    return $input |> self::trimStrings(...) |> self::normalizeUrls(...);
}

(void) calculateTotal($items); // Explicitly discarding — no warning
sanitize($data);               // ⚠️ Warning: return value not consumed
```

**MUST apply to**: Domain Services, sanitization methods, Command Handlers returning IDs.

### `array_first()` / `array_last()`

```php
$first = array_first($collection);  // null if empty
$last  = array_last($collection);   // null if empty

// ❌ FORBIDDEN
$first = reset($arr);  $last = end($arr);
```

### URI Extension

```php
use Uri\Rfc3986\Uri;
use Uri\WhatWg\Url;

$uri = Uri::fromString('https://example.com:443/path?q=1#frag');
$url = Url::fromString('https://example.com/api/../v2/users');
$url->getPathname();  // "/v2/users" (normalized)

// ❌ FORBIDDEN
$parts = parse_url($url); // NEVER
```

### Closures & FCCs in Constant Expressions

```php
class Transformers
{
    const TRIM = trim(...);
    const UPPER = strtoupper(...);
    const PIPELINE = [trim(...), strtoupper(...)];
}

#[SkipDiscovery(static function (Container $c): bool {
    return !$c->get(Application::class) instanceof ConsoleApplication;
})]
final class BlogPostEventHandlers { /* ... */ }
```

### Final Constructor Property Promotion

```php
class AggregateRoot
{
    public function __construct(
        public final string $id,   // Cannot be overridden in child classes
        public string $name,
    ) {}
}
```

### Asymmetric Visibility for Static Properties

> Instance-level asymmetric visibility was PHP 8.4. PHP 8.5 extends to **static** properties.

```php
class Config
{
    public private(set) static string $apiKey;

    public static function initialize(string $key): void
    {
        self::$apiKey = $key;
    }
}
```

### `Closure::getCurrent()`

```php
$factorial = function(int $n): int {
    return $n <= 1 ? 1 : $n * Closure::getCurrent()($n - 1);
};
```

### `FILTER_THROW_ON_FAILURE`

```php
try {
    $email = filter_var($input, FILTER_VALIDATE_EMAIL, FILTER_THROW_ON_FAILURE);
} catch (\ValueError $e) {
    throw new \InvalidArgumentException('Invalid email', previous: $e);
}
```

### Attribute Enhancements

- `#[\Deprecated]` → usable on **constants** and **traits** (was functions/classes in 8.4)
- `#[\Override]` → usable on **properties** (was methods in 8.3)
- `#[\DelayedTargetValidation]` → suppresses attribute compile-time errors (new)

### Other 8.5 Features

| Feature                       | Usage                                     |
| ----------------------------- | ----------------------------------------- |
| Partitioned cookies (CHIPS)   | `setcookie(..., ['partitioned' => true])` |
| `locale_is_right_to_left()`   | RTL detection                             |
| `IntlListFormatter`           | Locale-aware list formatting              |
| `grapheme_levenshtein()`      | Unicode-safe Levenshtein                  |
| Persistent cURL share handles | `curl_share_init_persistent()`            |
| Error stack traces for fatals | Automatic                                 |

---

## §2 — Features NOT PHP 8.5 (Use Them, Don't Mislabel)

| Feature                          | Introduced In | Status in 8.5          |
| -------------------------------- | ------------- | ---------------------- |
| Property hooks (`get`/`set`)     | **PHP 8.4**   | ✅ Available, USE THEM |
| Asymmetric visibility (instance) | **PHP 8.4**   | ✅ Available, USE THEM |
| `#[\Deprecated]` on functions    | **PHP 8.4**   | ✅ Available           |
| `#[\Override]` on methods        | **PHP 8.3**   | ✅ Available           |
| `readonly` classes               | **PHP 8.2**   | ✅ Available           |
| Enums                            | **PHP 8.1**   | ✅ Available           |
| Constructor property promotion   | **PHP 8.0**   | ✅ Available           |

### Property Hooks (PHP 8.4, enforced)

```php
final readonly class Email
{
    public function __construct(
        public string $value {
            get => strtolower($this->value);
            set {
                $normalized = strtolower(trim($value));
                if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException("Invalid email: {$value}");
                }
                $this->value = $normalized;
            }
        }
    ) {}
}
```

---

## §3 — PHP 8.5 Deprecations — NEVER Emit

| Deprecated                     | ✅ Use Instead                      |
| ------------------------------ | ----------------------------------- |
| `` `ls -la` `` (backtick)      | `shell_exec('ls -la')`              |
| `(boolean)$x`                  | `(bool)$x`                          |
| `(integer)$x`                  | `(int)$x`                           |
| `(double)$x`                   | `(float)$x`                         |
| `(binary)$x`                   | `(string)$x`                        |
| `array_key_exists(null, $arr)` | Use `''` or explicit key check      |
| `curl_close($ch)`              | Remove — no-op since 8.0            |
| `socket_set_timeout()`         | `stream_set_timeout()`              |
| `__sleep()` / `__wakeup()`     | `__serialize()` / `__unserialize()` |
| `parse_url()`                  | URI extension (§1)                  |
| PDO base-class constants       | Driver subclass constants           |

---

## §4 — Laravel 12 Rules

### Service Provider Registration

```php
// bootstrap/providers.php (NOT config/app.php in Laravel 12)
return [
    App\Providers\AppServiceProvider::class,
    Src\Providers\SharedServiceProvider::class,
    Src\Modules\Users\Providers\UsersServiceProvider::class,
    // ... all module providers
];
```

### Port Bindings

```php
public function register(): void
{
    $this->app->bind(
        UserRepositoryPort::class,
        EloquentUserRepository::class
    );
    // Bind ALL ports — never leave an unbound port
}
```

### Spatie Permission Middleware

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

### Database Rules

- Universal timestamps: `created_at`, `updated_at`, `deleted_at` on every table.
- No hard deletes — `SoftDeletes` trait on every EloquentModel.
- Always `select()` — never `SELECT *`. Always `paginate()` — never unbounded `get()`.
- Query by `uuid`, never by `id`, in public-facing operations.
- Use `when()` for conditional filters.

### EloquentModel Template

```php
declare(strict_types=1);

/** @internal */
final class {Module}EloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = '{table_name}';
    protected $fillable = [/* fields */];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field_1', 'field_2', 'status'])  // Never passwords/tokens
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('{context}.{module}');
    }
}
```

### Eloquent Relationship Rules

- Every FK column (`user_id`, `*_id`) in a migration **must** have a typed `BelongsTo` on the child model and a typed `HasMany`/`HasOne` on the parent — both sides, always, no orphan FKs.
- When adding or reviewing any `EloquentModel`, check that all FK columns in `$fillable` have their relationship method declared; if `user_id` is present, add `createdBy(): BelongsTo` on the model and the inverse `hasMany` on `UserEloquentModel`.
- All relationship methods must carry a typed `@return` generic: `@return BelongsTo<ParentModel, $this>` / `@return HasMany<ChildModel, $this>`.

---

### §4.1 — Eloquent Performance (Senior-Level Mandatory)

#### 1. `Model::shouldBeStrict()` — MANDATORY in `AppServiceProvider`

```php
public function boot(): void
{
    // Combines: preventLazyLoading + preventSilentlyDiscardingAttributes + preventAccessingMissingAttributes
    Model::shouldBeStrict(! $this->app->isProduction());

    // Production: log violations instead of crashing
    if ($this->app->isProduction()) {
        Model::handleLazyLoadingViolationUsing(function ($model, $relation): void {
            logger()->warning("Lazy loading [{$relation}] on [" . get_class($model) . "]");
        });
    }
}
```

#### 2. Eager Loading with Column Selection — ALWAYS specify columns

```php
// ❌ Loads ALL columns from relationships
$students = StudentEloquentModel::with('courses')->get();

// ✅ Only needed columns (MUST include id + foreign key)
$students = StudentEloquentModel::with('courses:id,student_id,name,status')->get();
```

#### 3. Large Dataset Processing — `chunk()` / `chunkById()` / `cursor()`

```php
// chunk() — process in batches (bulk operations)
StudentEloquentModel::where('active', true)->chunk(200, fn(Collection $batch) => /* ... */);

// chunkById() — safer when modifying rows during iteration
StudentEloquentModel::where('graduated', true)->chunkById(200, fn(Collection $batch) => /* ... */);

// cursor() — one model at a time, lowest memory (ideal for exports)
foreach (StudentEloquentModel::where('active', true)->cursor() as $student) { /* ... */ }
```

> **Rule**: `chunk()` for batch operations, `chunkById()` for mutations, `cursor()` for streaming exports. NEVER unbounded `get()` on tables with >1000 rows.

#### 4. `withWhereHas()` — replaces `whereHas` + `with` combo

```php
// ❌ Duplicated constraint
$q = Model::whereHas('relation', fn($q) => $q->where('active', true))
    ->with(['relation' => fn($q) => $q->where('active', true)]);

// ✅ Single method (Laravel 10+)
$q = Model::withWhereHas('relation', fn($q) => $q->where('active', true));
```

#### 5. Aggregate Subqueries — `withCount()` / `withAvg()` / `withSum()`

```php
// ❌ N+1 to count/sum relations
foreach ($students as $s) { echo $s->courses->count(); }

// ✅ Use subquery aggregates
$students = StudentEloquentModel::withCount('courses')
    ->withAvg('grades as average_grade', 'score')
    ->paginate(15);
// Access: $student->courses_count, $student->average_grade
```

#### 6. Database Indexes — Composite for query patterns

```php
// Migration: index columns used together in WHERE + ORDER BY
Schema::table('students', function (Blueprint $table): void {
    $table->index(['status', 'created_at']);  // Status filter + date sort
    $table->index(['name', 'email']);          // Search queries
    $table->index(['deleted_at']);             // SoftDeletes filter
});
```

> Rule: Every `when()` filter column + `orderBy` column MUST have an index.

#### 7. Query Scopes — Reusable Filters

```php
// In EloquentModel
public function scopeActive(Builder $query): Builder
{
    return $query->where('status', 'ACTIVE')->whereNull('deleted_at');
}

public function scopeSearch(Builder $query, ?string $term): Builder
{
    return $query->when($term, fn($q) => $q->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
          ->orWhere('email', 'like', "%{$term}%");
    }));
}

// Usage in Repository
StudentEloquentModel::active()->search($filters->search)->paginate($perPage);
```

---

## §5 — Architecture Rules

### Layer Imports (enforced)

| Layer          | Can import                            | Cannot import                   |
| -------------- | ------------------------------------- | ------------------------------- |
| Domain         | Shared/Domain, own VOs, Enums         | Eloquent, Laravel, HTTP, Queues |
| Application    | Domain, Shared/Application, DTOs      | Eloquent (`@internal` enforced) |
| Infrastructure | Domain (Ports), Application, Eloquent | No restrictions — adapter layer |

> The **Mapper** is the ONLY class importing both Domain Entity and EloquentModel.

### Readonly Classes

| Class type                                | Use `readonly`? | Reason                    |
| ----------------------------------------- | --------------- | ------------------------- |
| Value Objects                             | ✅ Yes          | Immutable by nature       |
| Domain Events                             | ✅ Yes          | Immutable by nature       |
| DTOs extending `Spatie\LaravelData\Data`  | ❌ No           | Parent is not readonly    |
| Domain Entities extending `AggregateRoot` | ❌ No           | Needs mutable event array |
| Classes with default property values      | ❌ No           | PHP restriction           |

### Date Handling (critical)

```php
// Mapper (Carbon → string)
createdAt: $model->created_at?->toIso8601String(),

// Query Handler (string → ReadModel — no conversion)
createdAt: $user->createdAt ?? '',   // ✅ Already a string

// ❌ WRONG
createdAt: $user->created_at?->toISOString() ?? '',
```

### Property Naming

- **Eloquent Model**: `snake_case` (`created_at`)
- **Domain Entity**: `camelCase` (`createdAt`)
- **ReadModel/DTO**: `camelCase` (`createdAt`)
- **Frontend**: receives camelCase from JSON

### Hexagonal Architecture

- **Domain** sits at the center and owns invariants, entities, value objects, domain services, and domain exceptions.
- **Application** orchestrates use-cases through handlers, DTOs, ports, policies, transactions, and cache boundaries.
- **Infrastructure** implements adapters for persistence, storage, queues, mail, exports, observability, and framework concerns.
- Dependencies point inward: Domain knows nothing about Laravel, Eloquent, HTTP, queues, storage drivers, or third-party SDKs.
- Controllers MUST stay thin: validate, authorize, map request → DTO/command, invoke handler, return response.
- Business rules NEVER live in controllers, FormRequests, Blade/Inertia middleware, or Eloquent models when they belong to the domain.

### SOLID + SRP

- **SOLID** is mandatory in every module, with special attention to **SRP** and dependency inversion through ports.
- **SRP**: each Handler, Service, Mapper, Adapter, Exporter, Listener, and Policy should have one primary reason to change.
- **Open/Closed**: prefer extending behavior through handlers, policies, events, and adapters instead of editing unrelated classes.
- **Liskov / Interface Segregation**: ports should stay small, cohesive, and specific to the use-case.
- **Dependency Inversion**: Application depends on abstractions (`RepositoryPort`, `StoragePort`, `AuditPort`), never on concrete adapters.

### Domain-Driven Design (DDD)

- Use ubiquitous language consistently in class names, methods, exceptions, DTOs, events, routes, and permission names.
- Domain owns business language, invariants, aggregate boundaries, and state transitions.
- Favor explicit domain methods such as `enrollStudent()`, `publishCampaign()`, `restoreAttendance()` over generic setters.
- Domain exceptions must express business intent, not framework internals.
- If a rule matters to the business, it belongs in Domain or in an Application policy that protects the use-case boundary.

### Value Objects

- Use Value Objects to avoid primitive obsession for identifiers, emails, money, percentages, status flags, paths, slugs, and similar concepts.
- Value Objects MUST be `readonly`, validate themselves on construction, and preserve invariants for their entire lifetime.
- Prefer property hooks and explicit named constructors when they improve invariants and readability.
- Equality should be value-based, not identity-based.
- Never leak unvalidated primitives deep into the domain when a Value Object exists.

### CQRS (básico o avanzado)

- **Commands** mutate state, enforce business rules, may dispatch domain events, and return `void`, IDs, or explicit result objects.
- **Queries** are side-effect free, return `ReadModel`s or paginated collections, and may use `Cache::remember`.
- Basic CQRS is enough for most modules: separate handlers and DTOs for writes vs reads.
- Advanced CQRS is allowed when justified: dedicated read repositories, projections, denormalized read models, async listeners, or specialized caching.
- Queries MUST NOT mutate state. Commands MUST NOT act as generic read services.
- Inject handlers directly — no Bus unless the module clearly benefits from it.

### Repository Pattern

- Every aggregate root with persistence must expose a `RepositoryPort` in `Domain/Ports/`.
- Infrastructure repositories implement the port and hide Eloquent specifics, query builders, eager loading, and transaction details.
- Application handlers depend on repository ports, never concrete Eloquent repositories.
- The Mapper remains the only bridge between domain entities and Eloquent models.
- Repositories should model aggregate persistence and retrieval, not become dumping grounds for unrelated helpers.

### Event-Driven Architecture

- Use domain/application events when a business action must trigger decoupled side effects.
- Domain events should be immutable (`readonly`) and named in business language.
- `AggregateRoot` records domain events; listeners/subscribers live in Infrastructure or Providers.
- Use events to decouple reactions such as audit, notifications, cache invalidation, media processing, exports, or activity tracking.
- Prefer synchronous listeners for simple local reactions and queued listeners when the workload is expensive or non-critical to the request.
- Do NOT introduce events for trivial direct flows where a simple method call keeps the design clearer.

### KISS / DRY / Clean Code / DX / Code Review

- **KISS**: prefer the smallest architecture that preserves module boundaries. No speculative abstraction, no generic framework inside the app without a real second use-case.
- **DRY**: extract duplicated mapping, validation, formatting, authorization glue, and cross-cutting logic to the correct layer.
- **Clean Code**: use descriptive names, small methods, typed exceptions, explicit return types, and low branching complexity.
- **DX**: modules should be predictable to navigate, with clear directories, naming, contracts, test placement, and actionable error messages.
- **Code Review readiness**: no dead code, commented-out blocks, debug leftovers, hidden side effects, or inconsistent conventions.
- If a reviewer cannot identify the use-case entry point, domain invariants, and adapter boundaries in under a minute, the module needs simplification.

### Storage Strategy (R2 / S3-Compatible)

- File storage concerns belong to Infrastructure adapters or dedicated storage services, never to Domain entities.
- If the project standard is Cloudflare R2, review `config/filesystems.php` and the corresponding env defaults so the intended default disk is explicit.
- If a module stores cloud artifacts, prefer an explicit configured disk (`r2` or config-driven alias) instead of relying on ambiguous implicit defaults.
- In this project, **ALL uploads** of PDFs, DOCX, XLS/XLSX/CSV, images, and any other user/business files MUST use **Cloudflare R2** through a dedicated adapter/service/port. Do NOT store uploads on `local`, `public`, `storage/app/public`, or any filesystem path inside the server as the final persistence target.
- `local` disk is allowed only for framework internals, ephemeral processing, or tests explicitly scoped for that purpose. It is **forbidden** as the final storage destination for module uploads.
- Never implement upload flows with `store(..., 'public')`, `Storage::disk('local')` as final destination, `public_path()`, or manual writes to server folders when the file must persist for users or business operations.
- Public or temporary URLs must be generated by adapters, not by manual string concatenation.
- Storage policies must remain reviewable: `config/filesystems.php`, `.env.example`, and module-level storage config should not drift silently.

### Cache Management

```php
// List query — tags with fallback
try {
    return Cache::tags(['{module}_list'])->remember($cacheKey, $ttl, fn() => $this->fetchData());
} catch (\Exception $e) {
    return Cache::remember($cacheKey, $ttl, fn() => $this->fetchData());
}

// Mutation — clear cache
Cache::forget("{module}_{$uuid}");
try { Cache::tags(['{module}_list'])->flush(); } catch (\Exception $e) { /* expires naturally */ }
```

---

## §6 — Routes Convention

### Web Routes (Inertia + session)

```php
// Inertia pages
Route::get('/', [{Module}PageController::class, 'index'])->name('{module}.index');
Route::get('/create', [{Module}PageController::class, 'create'])->name('{module}.create');
Route::get('/{uuid}', [{Module}PageController::class, 'show'])->name('{module}.show')->whereUuid('uuid');
Route::get('/{uuid}/edit', [{Module}PageController::class, 'edit'])->name('{module}.edit')->whereUuid('uuid');

// JSON data endpoints (TanStack Query — web session auth)
Route::prefix('data')->group(function () {
    Route::middleware(['role:SUPER_ADMIN'])->prefix('admin')->group(function () {
        Route::get('/', [Admin{Module}Controller::class, 'index']);
        Route::post('/', [Admin{Module}Controller::class, 'store']);
        Route::get('/export', [{Module}ExportController::class, '__invoke']); // BEFORE /{uuid}
        Route::get('/{uuid}', [Admin{Module}Controller::class, 'show'])->whereUuid('uuid');
        Route::put('/{uuid}', [Admin{Module}Controller::class, 'update'])->whereUuid('uuid');
        Route::delete('/{uuid}', [Admin{Module}Controller::class, 'destroy'])->whereUuid('uuid');
        Route::patch('/{uuid}/restore', [Admin{Module}Controller::class, 'restore'])->whereUuid('uuid');
        Route::post('/bulk-delete', [Admin{Module}Controller::class, 'bulkDelete']);
    });
});
```

> ⚠️ `/export` route MUST be registered BEFORE `/{uuid}` — otherwise Laravel matches "export" as a UUID.

### API Routes (Sanctum — mobile/external)

```php
Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('/api/{module}/admin')->group(function () {
    Route::get('/', [{Module}Controller::class, 'index']);
    Route::post('/', [{Module}Controller::class, 'store']);
    Route::get('/{uuid}', [{Module}Controller::class, 'show'])->whereUuid('uuid');
    Route::put('/{uuid}', [{Module}Controller::class, 'update'])->whereUuid('uuid');
    Route::delete('/{uuid}', [{Module}Controller::class, 'destroy'])->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [{Module}Controller::class, 'restore'])->whereUuid('uuid');
});
```

**Never call `/api/*` from Inertia pages. Never use session auth on API routes.**

---

## §7 — CRUD Backend Checklist

### Domain Layer

- [ ] `Domain/Entities/{YourEntity}.php` — extends `AggregateRoot`, no Eloquent
- [ ] `Domain/ValueObjects/{YourId}.php` — `readonly` + uuid
- [ ] `Domain/Ports/{YourEntity}RepositoryPort.php`
- [ ] Domain invariants and business rules live in Domain, not controllers or Eloquent
- [ ] Domain language is explicit in exceptions, events, and method names

### Application Layer

- [ ] `Application/Commands/Create{YourEntity}/` handler + command
- [ ] `Application/Commands/Update{YourEntity}/` handler + command
- [ ] `Application/Commands/Delete{YourEntity}/` handler (soft delete only)
- [ ] `Application/Commands/Restore{YourEntity}/` handler
- [ ] `Application/Queries/List{YourEntities}/` (paginated, cached)
- [ ] `Application/Queries/Get{YourEntity}/` (single, cached)
- [ ] DTOs: Create, Update, Filter (extend `Spatie\LaravelData\Data`, no `readonly`)
- [ ] ReadModels: List + Detail (no `readonly`)
- [ ] Domain events + cache invalidation listeners
- [ ] Handlers depend on ports/interfaces, not concrete infrastructure classes
- [ ] Handlers keep SRP and contain orchestration only

### Infrastructure Layer

- [ ] `{YourEntity}EloquentModel` — `@internal`, `SoftDeletes`, `LogsActivity`
- [ ] `{YourEntity}Mapper` — only class importing domain + Eloquent
- [ ] `Eloquent{YourEntity}Repository` — implements port
- [ ] If files/media exist: `Infrastructure/ExternalServices/Storage/` or equivalent adapter layer
- [ ] If the module uploads PDFs, DOCX, Excel, images, or any persisted file: final storage MUST be `r2` via dedicated disk / adapter / port
- [ ] `public` and `local` disks are forbidden as final persistence targets for module uploads
- [ ] Review `config/filesystems.php` default disk and env alignment when the module depends on storage conventions
- [ ] Web Controller (Inertia) + API Controller (JSON)
- [ ] Requests + Resources
- [ ] Routes: Inertia pages + `/data/admin/*` endpoints (export BEFORE `/{uuid}`)
- [ ] ServiceProvider registered in `bootstrap/providers.php`

### Permissions

- [ ] `VIEW_{MODULE}`, `CREATE_{MODULE}`, `UPDATE_{MODULE}`, `DELETE_{MODULE}`, `RESTORE_{MODULE}`
- [ ] `forgetCachedPermissions()` BEFORE creating permissions
- [ ] Super Admin gets all

### Export

- [ ] `{YourEntity}ExcelExport.php` — `FromQuery`, `WithHeadings`, `WithMapping`, `ShouldAutoSize`
- [ ] `{YourEntity}PdfExport.php` — DomPDF + Blade template
- [ ] Every CRUD that includes PDF export MUST have its own dedicated Blade view under `resources/views/exports/pdf/`
- [ ] PDF export Blade files MUST expose a `Status` column when the aggregate uses `SoftDeletes`
- [ ] In PDF exports for soft-deletable CRUDs, status MUST be derived from `deleted_at` only: `Active` when `deleted_at === null`, `Suspended` when `deleted_at !== null`
- [ ] Do NOT label soft-deleted rows as `Inactive` in CRUD exports
- [ ] Both reuse same `FilterDTO`
- [ ] `ExportController` + Blade view namespace registered

### Tests

- [ ] `Tests/Unit/Domain/` — domain invariants
- [ ] `Tests/Unit/Application/` — handlers with mocked repository
- [ ] `Tests/Integration/` — DB round-trip via Mapper
- [ ] `Tests/Feature/` — full HTTP CRUD + export
- [ ] Storage flows and file validation covered when the module manages files
- [ ] Critical Value Objects and business rules covered by tests

---

## §8 — Exports (Excel + PDF)

### Export Date Format Rule

**CRITICAL**: All dates in exports (Excel and PDF) MUST use human-readable format `F j, Y` (e.g., "March 3, 2026"), NOT ISO8601.

### Export Status + Phone Rule

**MANDATORY**: Every CRUD export (Excel and PDF) MUST include a human-readable `Status` column/cell for soft-delete state.

- If `deleted_at` is `null`, export status MUST be `Active`.
- If `deleted_at` is not `null`, export status MUST be `Suspended`.
- If the module also has a business/editorial lifecycle state, it MUST remain in a separate column such as `Publication Status`.

**MANDATORY**: Every phone value shown in exports (Excel and PDF) MUST be formatted as `(XXX) XXX-XXXX`.

- Use the shared backend phone formatter/helper.
- Never expose raw `+1XXXXXXXXXX`, raw digits, or inconsistent phone formats in reports.

### ExportController

```php
final class {Module}ExportController
{
    public function __invoke(Request $request): mixed
    {
        $filters = {Entity}FilterDTO::from($request->all());
        $format  = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return $this->exportPdf($filters);
        }
        return Excel::download(new {Entity}ExcelExport($filters), '{entities}.xlsx');
    }

    private function exportPdf({Entity}FilterDTO $filters): Response
    {
        $items = app(List{Entities}Handler::class)->handle($filters, perPage: 9999);
        $pdf = Pdf::loadView('{module}::exports.pdf', [
            'items' => $items->data,
            'generatedAt' => now()->format('F j, Y H:i'),
        ])->setPaper('a4', 'landscape');
        return $pdf->download('{entities}-export.pdf');
    }
}
```

### ExcelExport with Date Formatting

```php
final class {Entity}ExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly {Entity}FilterDTO $filters) {}

    public function query(): Builder
    {
        return {Entity}EloquentModel::query()
            ->when($this->filters->search, fn($q) => $q->where('name', 'like', "%{$this->filters->search}%"))
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array { return ['Name', 'Email', 'Status', 'Created At']; }

    public function map($row): array
    {
        return [
            $row->name,
            $row->email ?? '—',
            $row->status,
            $row->created_at?->format('F j, Y') ?? '—'  // ✅ Human-readable format
        ];
    }
}
```

### Export Transformer Pattern (MANDATORY)

Every module with export functionality MUST use a dedicated transformer with the pipe operator (`|>`) to ensure consistent data formatting (especially dates and null values).

```php
// Canonical Example: Modules\Clients\Infrastructure\Http\Export\ClientExportTransformer
final class {Entity}ExportTransformer
{
    /**
     * Transform entity to export array for Excel
     */
    #[\NoDiscard]
    public static function transformForExcel({Entity}ReadModel $entity): array
    {
        return $entity
            |> self::extractBaseData(...)
            |> self::formatDates(...)
            |> self::sanitizeOutput(...);
    }

    /**
     * Transform entity to export array for PDF
     */
    #[\NoDiscard]
    public static function transformForPdf({Entity}ReadModel $entity): array
    {
        return $entity
            |> self::extractPdfData(...)
            |> self::formatDates(...)
            |> self::sanitizeOutput(...);
    }

    private static function extractBaseData({Entity}ReadModel $entity): array
    {
        return [
            'uuid' => $entity->uuid,
            'name' => $entity->name,
            'email' => $entity->email,
            'created_at' => is_string($entity->createdAt) ? $entity->createdAt : null,
            'updated_at' => is_string($entity->updatedAt) ? $entity->updatedAt : null,
        ];
    }

    /**
     * Format date fields to human-readable format "March 3, 2026"
     */
    private static function formatDates(array $data): array
    {
        $dateFields = ['created_at', 'updated_at'];

        foreach ($dateFields as $field) {
            if (isset($data[$field]) && is_string($data[$field]) && $data[$field] !== '') {
                try {
                    $date = new \DateTimeImmutable($data[$field]);
                    $data[$field] = $date->format('F j, Y');  // ✅ "March 3, 2026"
                } catch (\Exception) {
                    // Keep original value if parsing fails
                }
            }
        }

        return $data;
    }

    /**
     * Sanitize output values (convert null to empty string)
     */
    private static function sanitizeOutput(array $data): array
    {
        return array_map(fn($value) => $value ?? '', $data);
    }
}
```

### PDF Blade Template Rules

**MANDATORY styling for all PDF exports:**

```blade
<style>
    body {
        font-family: sans-serif;
        font-size: 11px;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th {
        background-color: #f3f4f6;
        color: #333;
        text-align: center;        /* ✅ MANDATORY: centered headers */
        padding: 8px;
        border: 1px solid #e5e7eb;
        font-weight: bold;         /* ✅ MANDATORY: bold headers */
    }

    td {
        padding: 8px;
        border: 1px solid #e5e7eb;
        text-align: center;        /* ✅ MANDATORY: centered content */
        vertical-align: middle;    /* ✅ MANDATORY: vertical centering */
    }

    tr:nth-child(even) {
        background-color: #fafafa;
    }
</style>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td>{{ $row['created_at'] }}</td>  {{-- Already formatted by transformer --}}
            </tr>
        @endforeach
    </tbody>
</table>
```

### Export Checklist

- [ ] Dates formatted as `F j, Y` (e.g., "March 3, 2026") in both Excel and PDF
- [ ] PDF table headers: `text-align: center` + `font-weight: bold`
- [ ] PDF table cells: `text-align: center` + `vertical-align: middle`
- [ ] Transformer uses pipe operator for data transformation
- [ ] `formatDates()` method handles both Carbon instances and strings
- [ ] Null values sanitized to empty strings or '—'
- [ ] Export route registered BEFORE `/{uuid}` route
- [ ] Both Excel and PDF reuse same `FilterDTO`
- [ ] Generated timestamp uses `now()->format('F j, Y H:i')`

---

## §9 — Swagger / OpenAPI

```php
/** @OA\Info(version="1.0.0", title="VIDULA API", description="REST API — Sanctum token auth") */
/** @OA\Server(url=L5_SWAGGER_CONST_HOST, description="API Server") */
/** @OA\SecurityScheme(securityScheme="sanctum", type="http", scheme="bearer", bearerFormat="JWT") */
```

Every API method: `@OA\Get`/`@OA\Post`/`@OA\Put`/`@OA\Delete`/`@OA\Patch`. Every DTO/Resource: `@OA\Schema`. Run `l5-swagger:generate` after changes.

---

## §10 — Security (OWASP Top 10:2025)

| Category                       | Key Mitigation                                                                                              |
| ------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| **A01 Broken Access Control**  | Enforce in `AuthorizationService.php` via Policies/Gates. Deny by default. `->whereUuid('uuid')` on routes. |
| **A02 Security Misconfig**     | HSTS, strict CSP, `SecurityHeadersMiddleware`. `APP_DEBUG=false` in prod.                                   |
| **A03 Supply Chain**           | Pin deps. `composer audit` + `npm audit` in CI.                                                             |
| **A04 Crypto Failures**        | `Hash::make()` only. HTTPS/TLS 1.3+. Never log passwords/tokens/PII.                                        |
| **A05 Injection**              | Eloquent PDO binding only. No raw SQL with user input. No `exec()`/`shell_exec()` with user input.          |
| **A06 Insecure Design**        | Domain layer = framework-agnostic. Rate limiting on auth. `spatie/laravel-honeypot` on public forms.        |
| **A07 Auth Failures**          | Fortify + Sanctum. MFA via `spatie/laravel-one-time-passwords`. Token rotation.                             |
| **A08 Integrity Failures**     | Verify uploaded file mime+content. Signed URLs. No `unserialize()` on user input.                           |
| **A09 Logging Failures**       | Structured OTEL logs. Never log raw sensitive data. Audit trail via `AuditPort`.                            |
| **A10 Exceptional Conditions** | Typed exceptions. Global handler maps to HTTP codes. Queue jobs implement `failed()`.                       |

---

## §11 — Observability & Audit

### Observability

- **OpenTelemetry**: primary. Instrument all crucial flows.
- **Structured logging**: never bare `Log::error('string')`. Use OTEL with `trace_id`.
- **Health checks**: `HealthCheckController` monitors DB, queue, cache, Reverb.

### Audit — Two-Level Strategy

| Event type       | Mechanism                     | Activation       |
| ---------------- | ----------------------------- | ---------------- |
| Model lifecycle  | `LogsActivity` trait          | Automatic        |
| Business actions | `AuditPort` in CommandHandler | Manual, explicit |

**`getActivitylogOptions()` rules**: `logOnly([...])` explicit, never `logAll()`. Never log passwords/tokens. `logOnlyDirty()` + `dontSubmitEmptyLogs()` mandatory.

---

## §12 — Composer Packages Mapping

| Package                      | Maps to                                         |
| ---------------------------- | ----------------------------------------------- |
| `laravel/framework`          | All modules                                     |
| `laravel/sanctum`            | `Auth/Infrastructure`                           |
| `laravel/fortify`            | `Auth/Application/Services/`                    |
| `spatie/laravel-data`        | `Shared/Application/DTOs/`                      |
| `spatie/laravel-permission`  | `Auth/Application/Permissions/`                 |
| `spatie/laravel-activitylog` | `Shared/Infrastructure/Audit/`                  |
| `maatwebsite/excel`          | `Shared/Infrastructure/Export/ExcelAdapter.php` |
| `barryvdh/laravel-dompdf`    | `Shared/Infrastructure/Export/PdfAdapter.php`   |
| `league/flysystem-aws-s3-v3` | S3-compatible disks such as AWS S3 / Cloudflare R2 |
| `darkaonline/l5-swagger`     | API documentation                               |
| `ramsey/uuid`                | `Shared/Domain/ValueObjects/Uuid.php`           |
| `pestphp/pest`               | All modules `Tests/`                            |

---

## §13 — Quick Decision Table

| Situation                        | Feature                                   |
| -------------------------------- | ----------------------------------------- |
| Sequential data transformation   | Pipe `\|>` (§1)                           |
| Immutable "wither" method        | `clone($obj, [...])` (§1)                 |
| Result must not be ignored       | `#[\NoDiscard]` + `(void)` (§1)           |
| First/last array element         | `array_first()` / `array_last()` (§1)     |
| Parse/validate URL               | `Uri\Rfc3986\Uri` / `Uri\WhatWg\Url` (§1) |
| Property validation in VOs       | Property hooks `set { }` (§2, PHP 8.4)    |
| Computed derived property        | Property hook `get =>` (§2, PHP 8.4)      |
| Prevent child override           | `final` in promotion (§1)                 |
| Shared file storage in backend   | Storage Port + Infrastructure adapter + configured disk |
| Cross-cutting side effects       | Domain/App event + listener/subscriber    |
| Static public-read private-write | `public private(set) static` (§1)         |
| Anonymous recursion              | `Closure::getCurrent()` (§1)              |
| Strict filter validation         | `FILTER_THROW_ON_FAILURE` (§1)            |

---

## §14 — Common Errors

| Error                                       | Fix                                                                                                                                                |
| ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Target class [role] does not exist`        | Add Spatie middleware aliases in `bootstrap/app.php` (§4)                                                                                          |
| `Readonly class cannot extend non-readonly` | Remove `readonly` from classes extending `Data`/`AggregateRoot` (§5)                                                                               |
| `->toISOString()` on string                 | Mapper already converts Carbon → string. Pass `$entity->createdAt ?? ''` (§5)                                                                      |
| 401 on `/api/*` from browser                | Use `/data` web JSON endpoints, not API routes (§6)                                                                                                |
| 404 after route changes                     | `php artisan config:clear && cache:clear && route:clear && view:clear`                                                                             |
| Frontend receives camelCase keys            | Add `#[MapOutputName(SnakeCaseMapper::class)]` on every `Data` ReadModel/DTO that serializes to JSON. Frontend always expects `snake_case`.        |
| DTO receives snake_case from request        | Add `#[MapInputName(SnakeCaseMapper::class)]` on every `Data` DTO that receives `snake_case` request data (e.g. `last_name` → `lastName`).         |
| Admin-created user needs password           | Auto-generate in Handler: `'password' => Hash::make(Str::password(8))`. Never require password from admin form.                                    |
| Role stored as column but doesn't exist     | Roles use Spatie Permission pivot table. Assign via `$model->assignRole('ROLE_NAME')` after `create()`, never pass `role` to the `create()` array. |
