---
name: architecture-simple-crud-php
description: Minimal directory tree and file placement rules for standard Laravel CRUD modules with low business complexity and without enterprise overengineering.
---

# ARCHITECTURE-SIMPLE-CRUD-PHP.md — Standard CRUD Baseline

Use this guide when the module is a normal CRUD:

- One aggregate / one main entity.
- Around 3 to 8 persisted fields.
- Standard flows: list, show, create, update, delete, restore.
- Standard flows may also include `bulk delete` when the table UI supports row selection.
- No files/media, exports, queues, WebSockets, external integrations, projections, or complex orchestration unless explicitly requested.
- Business rules are real but shallow: uniqueness, active/inactive, ordering, visibility, simple validation.

Do NOT use this guide when the module has any of these characteristics:

- More than one aggregate root or several coordinated subdomains.
- Heavy business workflows, approvals, multi-step state machines, or cross-module orchestration.
- Files, signatures, image processing, cloud storage, exports, notifications, or external APIs.
- Domain events, listeners, projections, read repositories, or async processing are clearly justified.
- Rich Value Objects and advanced invariants dominate the model.

In those cases, use `ARCHITECTURE-INTERMEDIATE-PHP.md`.

---

## Directory Tree

```text
src/
└── Modules/
    └── {YourModule}/
        ├── Providers/
        │   └── {YourModule}ServiceProvider.php
        ├── Tests/
        │   ├── Feature/
        │   └── Unit/
        ├── Domain/
        │   ├── Entities/
        │   │   └── {YourEntity}.php
        │   ├── ValueObjects/
        │   │   └── {YourEntity}Id.php
        │   └── Ports/
        │       └── {YourEntity}RepositoryPort.php
        ├── Application/
        │   ├── DTOs/
        │   │   ├── Store{YourEntity}Data.php
        │   │   ├── Update{YourEntity}Data.php
        │   │   ├── {YourEntity}FilterData.php
        │   │   └── BulkDelete{YourEntity}Data.php
        │   ├── Commands/
        │   │   ├── Create{YourEntity}Handler.php
        │   │   ├── Update{YourEntity}Handler.php
        │   │   ├── Delete{YourEntity}Handler.php
        │   │   ├── BulkDelete{YourEntity}Handler.php
        │   │   └── Restore{YourEntity}Handler.php
        │   └── Queries/
        │       ├── List{YourEntities}Handler.php
        │       └── Get{YourEntity}Handler.php
        └── Infrastructure/
            ├── Http/
            │   ├── Controllers/
            │   │   ├── Api/
            │   │   │   ├── {YourEntity}Controller.php        ← Swagger @OA\Tag + method annotations MANDATORY
            │   │   │   └── {YourEntity}ExportController.php  ← Swagger @OA\Get annotation MANDATORY if exports exist
            │   │   └── Web/
            │   │       └── {YourEntity}PageController.php
            │   ├── Export/                                  ← MANDATORY when exports are in scope
            │   │   ├── {YourEntity}ExcelExport.php
            │   │   ├── {YourEntity}PdfExport.php
            │   │   └── {YourEntity}ExportTransformer.php
            │   ├── Requests/
            │   │   ├── Store{YourEntity}Request.php
            │   │   ├── Update{YourEntity}Request.php
            │   │   ├── BulkDelete{YourEntity}Request.php
            │   │   └── Export{YourEntity}Request.php         ← MANDATORY when exports are in scope
            │   └── Resources/
            │       └── {YourEntity}Resource.php
            ├── Persistence/
            │   ├── Eloquent/
            │   │   └── Models/
            │   │       └── {YourEntity}EloquentModel.php
            │   ├── Mappers/
            │   │   └── {YourEntity}Mapper.php
            │   └── Repositories/
            │       └── Eloquent{YourEntity}Repository.php
            └── Routes/
                ├── web.php   ← Inertia pages + /data/admin JSON endpoints (session auth)
                └── api.php   ← Sanctum API endpoints (MANDATORY when module exposes API)

resources/
└── views/
    └── exports/
        └── pdf/
            └── {your_module_snake}.blade.php   ← MANDATORY when PDF export is in scope
```

---

## Mandatory Baseline

- Keep `Domain / Application / Infrastructure` separation.
- Keep `Application/Commands` and `Application/Queries` flat. Do not create one extra folder per handler unless the module grows.
- The domain stays free of Laravel, HTTP, Eloquent, queues, storage, and framework imports.
- The repository port lives in `Domain/Ports`; the concrete Eloquent repository lives in `Infrastructure/Persistence/Repositories`.
- The mapper is the only bridge between Domain entities and Eloquent models.
- Controllers stay thin: validate, authorize, map request to DTO, invoke handler, return response.
- DTOs extend `Spatie\LaravelData\Data` and are not `readonly`.
- Public routes use `uuid`, not numeric `id`.
- Web routes are primary. API routes are optional and secondary.
- The module `ServiceProvider` is responsible for binding `{YourEntity}RepositoryPort::class` to `Eloquent{YourEntity}Repository::class`, loading routes, and loading views when export templates exist.
- If `bulk delete` is part of the UI scope, implement it as one dedicated command handler plus one request/DTO carrying the selected UUIDs.

---

## What Stays Small on Purpose

- One repository port for the aggregate is enough.
- One Eloquent repository is enough.
- One resource class is enough unless list/detail representations truly diverge.
- One page controller plus one admin data controller is enough for most modules.
- One service provider that binds the repository and loads routes is enough.
- One export controller plus one Excel export and one PDF export are enough when the module explicitly requires exports.
- One dedicated `Export{YourEntity}Request` is enough to validate export query params when the module exposes export endpoints.
- One `bulk delete` handler is enough when mass selection exists in the UI.
- Keep the folder depth low so the create/list/update flow is traceable in under a minute.

---

## Value Objects Rule

- `Uuid` or `{YourEntity}Id` is mandatory.
- Add more Value Objects only when a field has a real invariant worth protecting for the whole lifetime of the object.
- Good candidates: email, slug, money, percentage, status, normalized phone, URL.
- Do not wrap every string or every primitive only to satisfy architecture aesthetics.

---

## CQRS Rule for Simple CRUD

- Keep write handlers in `Application/Commands/`.
- Keep read handlers in `Application/Queries/`.
- This is still CQRS, but basic CQRS.
- `bulk delete` belongs to Commands, never Queries.
- Do not introduce a bus, projections, read repositories, listeners, or denormalized views unless a real second use-case requires them.

---

## Optional Folders — Add Only If Needed

Add these only when the module genuinely requires them:

- `Infrastructure/Storage/`
- `Infrastructure/Queue/`
- `Infrastructure/WebSocket/`
- `Infrastructure/ExternalServices/`
- `Application/Policies/`
- `Application/Listeners/`
- `Domain/Events/`
- extra `Tests/Integration/`

If you add one of these folders, be able to explain the concrete use-case in one sentence.

> `Infrastructure/Http/Export/` and `resources/views/exports/pdf/` are NOT optional when exports are in scope — see **Export Rule** below.

---

## ServiceProvider Rule

- `{YourModule}ServiceProvider` is mandatory even in a simple CRUD.
- It must bind the repository interface/port to the concrete repository implementation.
- It must register module routes.
- If the module includes PDF export views, it should also register the module view namespace.
- Keep the provider small: bindings, route loading, and optional view loading only.

Canonical responsibility set:

- `bind({YourEntity}RepositoryPort::class, Eloquent{YourEntity}Repository::class)`
- load `Infrastructure/Routes/web.php`
- load export Blade views only if the module ships PDF exports

---

## Export Rule — Mandatory When Exports Are in Scope

Once exports are requested, ALL of the following become **mandatory with no exceptions**:

### Backend files

- `Infrastructure/Http/Export/{YourEntity}ExcelExport.php` — implements `FromQuery`, `WithHeadings`, `WithMapping`, `ShouldAutoSize`, `WithTitle`, `WithStyles`.
- `Infrastructure/Http/Export/{YourEntity}PdfExport.php` — uses DomPDF `Pdf::loadView('exports.pdf.{your_module_snake}', [...])`, `cursor()` for memory efficiency, `setPaper('a4', 'landscape')`.
- `Infrastructure/Http/Export/{YourEntity}ExportTransformer.php` — static methods `forExcel()` and `forPdf()` using `|>` pipe chains (`extract → formatDates → sanitize → toRow/object`). Both methods must carry `#[\NoDiscard]`.
- `Infrastructure/Http/Controllers/Api/{YourEntity}ExportController.php` — single `__invoke` method using `match($format)` to branch excel/pdf. Must carry `@OA\Get` Swagger annotation.
- `Infrastructure/Http/Requests/Export{YourEntity}Request.php` — validates `format`, `search`, `status`, `date_from`, `date_to`, and any entity-specific filter fields.

### View file

- `resources/views/exports/pdf/{your_module_snake}.blade.php` **in the global views directory** (NOT inside the module). Use the path `'exports.pdf.{your_module_snake}'` in `Pdf::loadView()`.
- The Blade template must include: AquaShield logo header, generated-at metadata, a `<table>` with `@forelse`, Active/Deleted badges, and an `AquaShield CRM — Confidential` footer.

### Route

- Declare the export route as `Route::get('/export', {YourEntity}ExportController::class)` **before** `Route::get('/{uuid}', ...)` inside the `data/admin` prefix group.
- Register it in both `web.php` (session auth, Inertia flow) and `api.php` (Sanctum).

### Reuse filter DTO

- `{YourEntity}ExcelExport` and `{YourEntity}PdfExport` must both accept `{YourEntity}FilterData` in their constructor and apply identical filter logic via `->when()` chains.
- `{YourEntity}ExportController::__invoke` must call `{YourEntity}FilterData::from($request->validated())` — never raw `$request->query()`.

### Testing

- Feature test for Excel export: assert response is 200 with `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` content type.
- Feature test for PDF export: assert response is 200 with `application/pdf` content type.

---

## Bulk Delete Rule for Simple CRUD

- `bulk delete` is optional in simple CRUD, but fully valid when the table supports row selection.
- Reuse the same aggregate repository; do not create a second repository just for mass deletion.
- Accept selected UUIDs through one dedicated request/DTO such as `BulkDelete{YourEntity}Data`.
- Handle the operation in one `BulkDelete{YourEntity}Handler`.
- Keep the operation as soft delete unless the module explicitly requires another behavior.
- Register the route as `POST /bulk-delete` under the same admin data group.
- Validate that the UUID array is non-empty and that every item is a valid UUID.
- Apply the same authorization rules as single delete.
- If the UI has no row selection or no batch action, skip `bulk delete` completely.

---

## Audit, Security, and Tests

- Use `LogsActivity` on the Eloquent model with explicit `logOnly([...])`, `logOnlyDirty()`, and `dontSubmitEmptyLogs()`.
- Use `AuditPort` only when there is a meaningful business action beyond passive model lifecycle logging.
- Define permissions with `VIEW_*`, `CREATE_*`, `UPDATE_*`, `DELETE_*`, `RESTORE_*`.
- Call `forgetCachedPermissions()` before seeding permissions.
- Mandatory tests for simple CRUD:
  - Feature tests for HTTP CRUD flow.
  - Add feature coverage for `bulk delete` when the module exposes that endpoint.
  - Unit tests only for custom Value Objects or domain invariants.
  - Integration tests only when mapper logic, casts, scopes, or persistence rules are non-trivial.
  - If exports exist, add feature coverage for Excel/PDF export flow, validate the `Export{YourEntity}Request` contract, and keep the same `FilterDTO` contract.

---

## KISS Guardrails

Avoid these by default in a 5-field CRUD:

- Generic `BaseRepository` inside the module.
- `Manager`, `Orchestrator`, `Coordinator`, or `Facade` classes with no second use-case.
- Domain events for direct one-step CRUD flows.
- Separate read/write repositories when the same aggregate query logic remains simple.
- WebSocket, queue, export, or storage abstractions without an actual requirement.
- Splitting Excel and PDF into multiple extra services when one export controller and one shared filter contract are enough.
- Creating a separate “mass operations” sub-architecture when a single `BulkDelete` command is enough.
- Deep folder nesting that makes maintenance slower than the business problem itself.

---

## Review Heuristic

If a reviewer cannot identify all of these quickly, the module is too complex for a normal CRUD:

- where the request enters,
- where validation happens,
- where the use-case handler lives,
- where the repository is bound,
- where the mapper converts domain ↔ persistence,
- where the `ServiceProvider` binds the port to the repository,
- and, if applicable, where export entry points, `Export{YourEntity}Request`, and Blade PDF views are registered,
- and where the routes are registered.

For language, PHP 8.5 syntax, route conventions, security rules, exports, and observability rules, always defer to `BACKEND-PHP.md`.

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
     *     @OA\Response(response=200, description="Paginated list", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(...): JsonResponse { ... }

    // @OA\Get    → show      path="/api/{module-slug}/{uuid}"
    // @OA\Post   → store     path="/api/{module-slug}"
    // @OA\Put    → update    path="/api/{module-slug}/{uuid}"
    // @OA\Delete → destroy   path="/api/{module-slug}/{uuid}"
    // @OA\Patch  → restore   path="/api/{module-slug}/{uuid}/restore"
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
- Export annotation path uses the web `/data/admin/export` path (session auth).
- All annotations include `security={{"sanctum": {}}}` even on read endpoints.
- Never annotate `Web/` page controllers — those are Inertia only.

---

## API Routes Rule — Mandatory When Module Exposes API Endpoints

### File location

`Infrastructure/Routes/api.php` — mirroring the `web.php` permission groups but without Web page routes.

### ServiceProvider registration

```php
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
