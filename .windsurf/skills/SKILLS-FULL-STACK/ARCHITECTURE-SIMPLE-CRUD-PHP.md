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
        │   │   ├── BulkDelete{YourEntity}Data.php
        │   │   └── {YourEntity}Data.php
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
            │   │   └── Web/
            │   │       ├── {YourEntity}PageController.php
            │   │       └── Admin{YourEntity}Controller.php
            │   ├── Requests/
            │   │   ├── Store{YourEntity}Request.php
            │   │   ├── Update{YourEntity}Request.php
            │   │   └── Export{YourEntity}Request.php
            │   └── Resources/
            │       └── {YourEntity}Resource.php
            ├── Export/
            │   ├── {YourEntity}ExcelExport.php
            │   ├── {YourEntity}PdfExport.php
            │   ├── {YourEntity}ExportController.php
            │   └── Views/
            │       └── pdf.blade.php
            ├── Persistence/
            │   ├── Models/
            │   │   └── {YourEntity}EloquentModel.php
            │   ├── Mappers/
            │   │   └── {YourEntity}Mapper.php
            │   └── Repositories/
            │       └── Eloquent{YourEntity}Repository.php
            └── Routes/
                └── web.php
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

- `Infrastructure/Api/`
- `Infrastructure/Storage/`
- `Infrastructure/Queue/`
- `Infrastructure/WebSocket/`
- `Infrastructure/Export/` with `ExcelExport`, `PdfExport`, `ExportController`, and Blade PDF view
- `Infrastructure/Http/Requests/Export{YourEntity}Request.php` when export query params need validation
- `Infrastructure/ExternalServices/`
- `Application/Policies/`
- `Application/Listeners/`
- `Domain/Events/`
- extra `Tests/Integration/`

If you add one of these folders, be able to explain the concrete use-case in one sentence.

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

## Export Rule for Simple CRUD

- Exports are optional in simple CRUD, not mandatory by default.
- If exports are requested, keep them minimal and reuse the same `{YourEntity}FilterData` / filter DTO used by the list flow.
- Add one dedicated `Export{YourEntity}Request` to validate export query params such as `format`, `date_from`, `date_to`, and any module-specific filters.
- The export controller/action should consume `validated()` from `Export{YourEntity}Request`, not raw query input.
- Support both Excel and PDF only when the request/module scope explicitly includes exports.
- The export route must be declared before `/{uuid}` in the routes file.
- Do not introduce extra adapters or abstractions for exports unless the module has a real second export use-case.
- Keep one `ExportController` entry point and branch by requested format when possible.
- PDF should use one dedicated Blade template.
- Excel and PDF should keep the same column semantics whenever possible.

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
