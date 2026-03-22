---
name: architecture-intermediate-php
description: Directory tree and file placement rules for the modular PHP and Laravel backend, including the project's Shared, Modules, Providers, and Middleware layers.
---

```
src/
│
├── Shared/
│   ├── Domain/
│   │   ├── Exceptions/
│   │   │   ├── DomainException.php
│   │   │   ├── EntityNotFoundException.php
│   │   │   ├── ValidationException.php
│   │   │   ├── UnauthorizedException.php
│   │   │   ├── BusinessRuleViolationException.php
│   │   │   ├── ConcurrencyException.php
│   │   │   ├── InvariantViolationException.php
│   │   │   └── IntegrationException.php
│   │   ├── ValueObjects/
│   │   │   ├── Uuid.php
│   │   │   ├── Email.php
│   │   │   ├── PhoneNumber.php
│   │   │   ├── Money.php
│   │   │   ├── DateRange.php
│   │   │   ├── Timestamp.php
│   │   │   └── Url.php
│   │   ├── Entities/
│   │   │   └── AggregateRoot.php
│   │   └── Ports/
│   │       ├── CachePort.php
│   │       ├── QueuePort.php
│   │       ├── LoggerPort.php
│   │       ├── StoragePort.php
│   │       ├── NotificationPort.php
│   │       └── ExportPort.php
│   │
│   ├── Application/
│   │   ├── DTOs/
│   │   │   ├── BaseDTO.php
│   │   │   ├── PaginationDTO.php
│   │   │   └── FilterDTO.php
│   │   └── Transactions/
│   │       ├── TransactionInterface.php
│   │       └── TransactionalHandler.php
│   │
│   └── Infrastructure/
│       ├── Cache/
│       │   ├── CacheInterface.php
│       │   ├── RedisAdapter.php
│       │   └── InMemoryCacheAdapter.php
│       ├── Queue/
│       │   ├── QueueInterface.php
│       │   ├── LaravelQueueAdapter.php
│       │   ├── RabbitMQAdapter.php
│       │   └── SqsAdapter.php
│       ├── Broadcasting/
│       │   ├── BroadcastingInterface.php
│       │   ├── ReverbAdapter.php
│       │   └── PusherAdapter.php
│       ├── Storage/
│       │   ├── StorageInterface.php
│       │   ├── S3StorageAdapter.php
│       │   ├── LocalStorageAdapter.php
│       │   └── SpatieMediaLibraryAdapter.php
│       ├── AI/
│       │   ├── AIClientInterface.php
│       │   ├── OpenAIAdapter.php
│       │   ├── AnthropicAdapter.php
│       │   └── PrismLLMAdapter.php
│       ├── Mail/
│       │   ├── MailInterface.php
│       │   ├── ResendAdapter.php
│       │   ├── MailgunAdapter.php
│       │   └── ReactEmailTemplateRenderer.php
│       ├── Export/
│       │   ├── ExportInterface.php
│       │   ├── ExcelAdapter.php
│       │   ├── PdfAdapter.php
│       │   └── PdfTemplateRenderer.php
│       ├── Logging/
│       │   ├── ApplicationLogger.php
│       │   ├── Handlers/
│       │   │   ├── OpenTelemetryMonologHandler.php
│       │   │   └── StructuredJsonHandler.php
│       │   └── Processors/
│       │       ├── TraceContextProcessor.php
│       │       ├── CorrelationIdProcessor.php
│       │       └── RequestContextProcessor.php
│       ├── Observability/
│       │   ├── Tracing/
│       │   │   ├── OpenTelemetryAdapter.php
│       │   │   ├── InstrumentationProvider.php
│       │   │   └── SpanEnricher.php
│       │   ├── Metrics/
│       │   │   ├── PrometheusAdapter.php
│       │   │   └── PrometheusController.php
│       │   └── HealthCheck/
│       │       ├── HealthCheckController.php
│       │       ├── HealthCheckAggregator.php
│       │       ├── DatabaseHealthCheck.php
│       │       ├── RedisHealthCheck.php
│       │       ├── QueueHealthCheck.php
│       │       ├── ReverbHealthCheck.php
│       │       ├── StorageHealthCheck.php
│       │       └── ExternalServiceHealthCheck.php
│       ├── Resilience/
│       │   ├── CircuitBreaker/
│       │   │   ├── CircuitBreaker.php
│       │   │   ├── CircuitBreakerInterface.php
│       │   │   ├── CircuitBreakerState.php
│       │   │   └── CircuitBreakerMetricsExporter.php
│       │   ├── RateLimiter/
│       │   │   ├── RateLimiter.php
│       │   │   └── RateLimiterInterface.php
│       │   └── Retry/
│       │       ├── RetryPolicy.php
│       │       └── ExponentialBackoff.php
│       ├── Persistence/
│       │   └── Transactions/
│       │       ├── DatabaseTransaction.php
│       │       └── UnitOfWork.php
│       ├── Audit/
│       │   ├── AuditInterface.php
│       │   ├── SpatieActivityLogAdapter.php
│       │   └── AuditableInterface.php
│       └── Utils/
│           ├── EmailHelper.php
│           └── ImageHelper.php
│
├── Middleware/
│   ├── AuthenticationMiddleware.php
│   ├── AuthorizationMiddleware.php
│   ├── CorrelationIdMiddleware.php
│   ├── TraceContextMiddleware.php
│   ├── RateLimitMiddleware.php
│   └── HandleInertiaRequests.php
│
├── Providers/
│   ├── SharedServiceProvider.php
│   ├── BusServiceProvider.php
│   └── EventServiceProvider.php
│
└── Modules/
    │
    ├── Auth/
    │   ├── Providers/
    │   │   └── AuthServiceProvider.php
    │   ├── Tests/
    │   ├── Domain/
    │   ├── Application/
    │   └── Infrastructure/
    │       ├── Http/
    │       │   ├── Controllers/
    │       │   │   ├── Api/
    │       │   │   └── Web/
    │       │   ├── Requests/
    │       │   └── Resources/
    │       ├── WebSocket/
    │       ├── Persistence/
    │       ├── Queue/
    │       ├── ExternalServices/
    │       └── Routes/
    │
    ├── Users/
    │   ├── Providers/
    │   │   └── UsersServiceProvider.php
    │   ├── Tests/
    │   ├── Domain/
    │   ├── Application/
    │   └── Infrastructure/
    │       ├── Http/
    │       ├── WebSocket/
    │       ├── Persistence/
    │       ├── Queue/
    │       ├── Storage/
    │       ├── Utils/
    │       └── Routes/
    │
    ├── Notifications/
    │   ├── Providers/
    │   ├── Tests/
    │   ├── Domain/
    │   ├── Application/
    │   └── Infrastructure/
    │       ├── Http/
    │       ├── WebSocket/
    │       ├── Persistence/
    │       ├── Queue/
    │       ├── ExternalServices/
    │       ├── Notifications/
    │       └── Routes/
    │
    ├── Blog/
    │   ├── Providers/
    │   │   └── BlogServiceProvider.php
    │   ├── Domain/
    │   ├── Application/
    │   └── Infrastructure/
    │       ├── Http/
    │       ├── Persistence/
    │       └── Routes/
    │
    └── {YourModule}/
        ├── Providers/
        │   └── {YourModule}ServiceProvider.php          ← registerWebRoutes() + registerApiRoutes() MANDATORY
        ├── Tests/
        │   ├── Feature/
        │   └── Unit/
        ├── Domain/
        │   ├── Entities/
        │   ├── ValueObjects/
        │   └── Ports/
        ├── Application/
        │   ├── DTOs/
        │   ├── Commands/
        │   └── Queries/
        └── Infrastructure/
            ├── Http/
            │   ├── Controllers/
            │   │   ├── Api/
            │   │   │   ├── {YourEntity}Controller.php        ← @OA\Tag + method annotations MANDATORY
            │   │   │   └── {YourEntity}ExportController.php  ← @OA\Get annotation MANDATORY if exports exist
            │   │   └── Web/
            │   │       └── {YourEntity}PageController.php
            │   ├── Export/                                  ← MANDATORY when exports are in scope
            │   │   ├── {YourEntity}ExcelExport.php
            │   │   ├── {YourEntity}PdfExport.php
            │   │   └── {YourEntity}ExportTransformer.php
            │   └── Requests/
            │       ├── Store{YourEntity}Request.php
            │       ├── Update{YourEntity}Request.php
            │       ├── BulkDelete{YourEntity}Request.php
            │       └── Export{YourEntity}Request.php         ← MANDATORY when exports are in scope
            ├── WebSocket/
            ├── Persistence/
            │   ├── Eloquent/
            │   │   └── Models/
            │   │       └── {YourEntity}EloquentModel.php
            │   ├── Mappers/
            │   │   └── {YourEntity}Mapper.php
            │   ├── Repositories/
            │   │   └── Eloquent{YourEntity}Repository.php
            │   └── ReadRepositories/
            │       └── Eloquent{YourEntity}ReadRepository.php
            ├── Queue/
            ├── Storage/
            └── Routes/
                ├── web.php   ← Inertia pages + /data/admin JSON endpoints (session auth)
                └── api.php   ← Sanctum API endpoints (MANDATORY when module exposes API)

resources/
└── views/
    └── exports/
        └── pdf/
            └── {your_module_snake}.blade.php   ← MANDATORY when PDF export is in scope
```

> **For architecture rules** (date handling, property naming, cache management, readonly classes) → see `BACKEND-PHP.md` §5.
> This file is the detailed directory tree ONLY.

---

## Export Rule — Mandatory When Exports Are in Scope

Once exports are requested, ALL of the following become **mandatory with no exceptions**:

### Backend files

- `Infrastructure/Http/Export/{YourEntity}ExcelExport.php` — implements `FromQuery`, `WithHeadings`, `WithMapping`, `ShouldAutoSize`, `WithTitle`, `WithStyles`.
- `Infrastructure/Http/Export/{YourEntity}PdfExport.php` — uses DomPDF `Pdf::loadView('exports.pdf.{your_module_snake}', [...])`, `cursor()` for memory efficiency, `setPaper('a4', 'landscape')`.
- `Infrastructure/Http/Export/{YourEntity}ExportTransformer.php` — static methods `forExcel()` and `forPdf()` using `|>` pipe chains. Both methods must carry `#[\NoDiscard]`.
- `Infrastructure/Http/Controllers/Api/{YourEntity}ExportController.php` — single `__invoke` using `match($format)`. Must carry `@OA\Get` Swagger annotation.
- `Infrastructure/Http/Requests/Export{YourEntity}Request.php` — validates `format`, `search`, `status`, `date_from`, `date_to`, and entity-specific filters.

### View file

- `resources/views/exports/pdf/{your_module_snake}.blade.php` **in the global views directory** (NOT inside the module).
- Use the path `'exports.pdf.{your_module_snake}'` in `Pdf::loadView()`.
- The Blade template must include: AquaShield logo header, generated-at metadata, `<table>` with `@forelse`, Active/Deleted badges, and `AquaShield CRM — Confidential` footer.

### Route

- `Route::get('/export', {YourEntity}ExportController::class)` declared **before** `Route::get('/{uuid}', ...)` inside both `web.php` and `api.php`.

### Reuse filter DTO

- Both `{YourEntity}ExcelExport` and `{YourEntity}PdfExport` accept `{YourEntity}FilterData` and apply identical `->when()` chains.
- `{YourEntity}ExportController` calls `{YourEntity}FilterData::from($request->validated())` — never raw `$request->query()`.

---

## Swagger / OpenAPI Rule — Mandatory on All API Controllers

Every controller under `Infrastructure/Http/Controllers/Api/` **must** carry OpenAPI annotations. No exceptions.

### CRUD controller

```php
/**
 * @OA\Tag(name="{Human Name}", description="{Human Name} CRUD operations")
 */
final class {YourEntity}Controller extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/{module-slug}",
     *     tags={"{Human Name}"},
     *     summary="List {entities}",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated list"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(...): JsonResponse { ... }

    // @OA\Get    → show        path="/api/{module-slug}/{uuid}"
    // @OA\Post   → store       path="/api/{module-slug}"
    // @OA\Put    → update      path="/api/{module-slug}/{uuid}"
    // @OA\Delete → destroy     path="/api/{module-slug}/{uuid}"
    // @OA\Patch  → restore     path="/api/{module-slug}/{uuid}/restore"
    // @OA\Post   → bulk-delete path="/api/{module-slug}/bulk-delete"
}
```

### Export controller

```php
/**
 * @OA\Get(
 *     path="/{module-slug}/data/admin/export",
 *     tags={"{Human Name}"},
 *     summary="Export {entities} to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class {YourEntity}ExportController { ... }
```

### Rules

- `@OA\Tag` on the class body (not inside a method doc-block).
- Every public HTTP method gets its own annotation block.
- Path prefix is always `/api/{module-slug}` for CRUD endpoints (Sanctum).
- Export annotation path uses the web `/data/admin/export` path.
- All annotations include `security={{"sanctum": {}}}` even on read endpoints.
- Never annotate `Web/` page controllers — those are Inertia only.

---

## API Routes Rule — Mandatory When Module Exposes API Endpoints

### File location

`Infrastructure/Routes/api.php` — mirroring the `web.php` permission groups but without Inertia/Web page routes.

### ServiceProvider registration

```php
private function registerWebRoutes(): void
{
    Route::middleware(['web', 'auth'])
        ->group(__DIR__ . '/../Infrastructure/Routes/web.php');
}

private function registerApiRoutes(): void
{
    Route::middleware(['api', 'auth:sanctum'])
        ->prefix('api/{module-slug}')
        ->group(__DIR__ . '/../Infrastructure/Routes/api.php');
}
```

### Route order inside `api.php`

1. Export route first (before `/{uuid}` to avoid route capture).
2. Static named paths second (`/service-categories`, `/project-types`, etc.).
3. `/{uuid}` last within each middleware group.

### Permission middleware

Mirror the exact permissions used in `web.php`. Use `VIEW_*` for read-only API and match the project's permission naming convention.
