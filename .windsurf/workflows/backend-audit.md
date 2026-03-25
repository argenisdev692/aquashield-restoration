---
description: Audits a Laravel 13 / PHP 8.5 module against architecture, security, audit & test rules. Generates a FAIL/PASS checklist, auto-fixes violations, then re-verifies until 100% score.
---

# BACKEND AUDIT AGENT — PHP 8.5 + Laravel 13

## PHASE 0 — CLASSIFY THE MODULE

Before starting the audit, you MUST classify the module complexity:

- **Simple CRUD baseline**: one aggregate, around 3 to 8 fields, standard CRUD + restore, low business complexity, no files/exports/queues/WebSockets/external integrations unless explicitly requested
- **Intermediate baseline**: multi-step business flow, richer invariants, integrations, files, exports, events, or cross-module orchestration

Then read the correct architecture file:

- Simple CRUD → `.windsurf/skills/SKILLS-FULL-STACK/ARCHITECTURE-SIMPLE-CRUD-PHP.md`
- Intermediate / complex → `.windsurf/skills/SKILLS-FULL-STACK/ARCHITECTURE-INTERMEDIATE-PHP.md`

### Classification Rules

Classify as **Intermediate** immediately if any of these are true:

- More than one aggregate root or multiple coordinated entities inside the same use-case flow
- Files, signatures, media processing, cloud storage, or storage adapters are required
- Excel/PDF exports are part of the declared module scope
- Queue workers, listeners, domain events, async processing, notifications, or scheduled side-effects are required
- Reverb/WebSockets or real-time broadcasting are part of the module scope
- External APIs, third-party SDKs, or cross-module orchestration are required
- Multi-step workflow, approval flow, state machine, or non-trivial lifecycle transitions exist
- Dedicated read models, projections, read repositories, or denormalized query paths are justified

Classify as **Simple CRUD** only when all of these are true:

- One main entity / one aggregate root
- Around 3 to 8 persisted fields
- Standard list, show, create, update, delete, restore flow
- Low business complexity with shallow invariants
- No requirement for files, exports, queues, events, WebSockets, integrations, or orchestration

Tie-breaker rule:

- If there is doubt, prefer **Intermediate** only when there is a concrete requirement already present in the module or explicitly requested by the user
- If the extra layers exist only because of template inertia, classify as **Simple CRUD** and flag the extra layers as overengineering

The audit output MUST start by stating:

- Selected baseline: `Simple CRUD` or `Intermediate`
- 2 to 5 concrete reasons taken from the actual module files or request scope
- Which architecture skill was used as the audit baseline

## PHASE 1 — AUDIT (produce checklist)

Before starting the audit, you MUST:

1. Call context7 to resolve current docs for: Laravel 13, filesystem / storage disks, events & listeners, Spatie Laravel Data 4.x, Spatie Permission 7.x, Spatie Activitylog, Pest 3
2. Call tavily to verify the latest stable versions of all packages in §12, prioritizing recent/current sources (`time_range: day`, `week`, or `month`) and official docs; avoid historical years unless the task explicitly asks for them

Then analyze the indicated module against these rules.
For each item mark ✅ PASS, ❌ FAIL (with file:line and brief description), ⚠️ WARN, or ➖ N/A (with reason when the item is outside the module scope).

### Required Checklist

**PHP 8.5**

- [ ] `declare(strict_types=1)` in EVERY .php file
- [ ] Pipe `|>` used in sequential transformations (no nested calls)
- [ ] `clone($obj, [...])` in wither methods (no manual boilerplate)
- [ ] `array_first()` / `array_last()` (never `reset()`/`end()`)
- [ ] `#[\NoDiscard]` on methods whose return value must not be ignored
- [ ] `Uri\Rfc3986\Uri` or `Uri\WhatWg\Url` (never `parse_url()`)
- [ ] `FILTER_THROW_ON_FAILURE` in `filter_var` validations
- [ ] PSR-12: explicit return type on EVERY method

**Architecture (selected baseline)**

- [ ] Audit baseline was classified correctly before judging complexity-sensitive items
- [ ] Audit report states the selected baseline, concrete evidence, and the skill file used for the baseline
- [ ] Module lives in `src/Modules/{Name}/` with Domain / Application / Infrastructure
- [ ] Hexagonal Architecture respected: Domain/Application depend on ports, Infrastructure implements adapters
- [ ] SOLID respected overall, especially dependency inversion through ports and cohesive interfaces
- [ ] SRP respected: handlers, services, adapters, listeners, policies, and mappers each have one clear reason to change
- [ ] Domain imports nothing from Infrastructure or Laravel
- [ ] Mapper is the ONLY contact point between Domain and Eloquent
- [ ] Controllers stay thin: no business rules, orchestration delegated to handlers/services
- [ ] DDD present: ubiquitous language, domain invariants and business rules live in Domain layer
- [ ] Value Objects are used where business concepts deserve invariants, are `readonly`, and validate themselves
- [ ] For simple CRUD modules, primitive fields are not wrapped in speculative Value Objects without a real invariant
- [ ] For simple CRUD modules, application structure stays flat enough to trace list/create/update flows quickly
- [ ] For simple CRUD modules, folders like `Storage`, `Queue`, `WebSocket`, `Export`, `ExternalServices`, listeners, or projections do not exist unless the scope truly requires them
- [ ] DTOs extend `Spatie\LaravelData\Data` and are NOT `readonly`
- [ ] Commands/Queries follow strict CQRS separation (basic or advanced), with side-effect free queries
- [ ] Repository: port defined in Domain, implementation in Infrastructure
- [ ] Event Driven Architecture is present when business flow requires it; otherwise marked `N/A` instead of forced
- [ ] For intermediate modules, extra adapters, listeners, policies, or subfolders are justified by concrete use-cases and not by template copy-paste
- [ ] KISS preserved: no speculative abstraction or generic layers without a real second use-case
- [ ] DRY preserved: duplicated business or mapping logic is centralized in the correct layer
- [ ] Clean Code preserved: naming, method size, branching, and exception semantics remain readable
- [ ] DX preserved: directory layout, naming, contracts, and errors are predictable for maintainers
- [ ] Code Review readiness: no dead code, hidden side effects, commented-out blocks, or convention drift
- [ ] ServiceProvider registered in `bootstrap/providers.php`

**Storage / File Administration**

- [ ] If the module manages files, it uses a dedicated Storage Port / adapter instead of raw `Storage` calls in Domain/Application
- [ ] `config/filesystems.php` default disk was reviewed against the project policy
- [ ] `.env.example` and storage-related config do not drift from the intended default disk policy
- [ ] Cloudflare R2 rule present when cloud file storage is required: `r2` disk configured or equivalent S3-compatible adapter documented
- [ ] If project policy requires R2 by default, `FILESYSTEM_DISK` / config fallback align to `r2`; otherwise deviation is explicitly justified
- [ ] File adapters use `Storage::disk('r2')` or configured disk from Infrastructure only
- [ ] Uploaded files/signatures/images are validated before persistence
- [ ] Public/temporary URLs are generated through adapter methods, not hardcoded string concatenation

**Design & Code Quality**

- [ ] SOLID respected overall, with explicit focus on SRP and dependency inversion
- [ ] SRP: each Handler / Service / Adapter has one clear responsibility
- [ ] KISS: no unnecessary abstraction, indirection, or speculative generic layers
- [ ] DRY: duplicated business logic extracted to the correct layer
- [ ] Clean Code: clear naming, small methods, minimal branching complexity
- [ ] DX: module is easy to navigate, naming/routes/contracts are predictable, errors are descriptive
- [ ] Repository Pattern is used consistently for aggregate persistence
- [ ] Code Review readiness: no dead code, debug leftovers, commented-out blocks, or inconsistent conventions

**Audit / Observability (§11)**

- [ ] Model uses `LogsActivity` trait with explicit `logOnly([...])`
- [ ] `logOnlyDirty()` + `dontSubmitEmptyLogs()` both present
- [ ] `AuditPort` called manually in CommandHandlers only when meaningful business actions justify explicit audit beyond model lifecycle logging
- [ ] NEVER `logAll()`, never log passwords/tokens/PII
- [ ] Structured logging via OTEL (never bare `Log::error('string')`)

**Security (§10)**

- [ ] No raw SQL with user input
- [ ] No `unserialize()` on external input
- [ ] `->whereUuid('uuid')` on UUID routes
- [ ] Permissions defined: `VIEW_X`, `CREATE_X`, `UPDATE_X`, `DELETE_X`, `RESTORE_X`
- [ ] `forgetCachedPermissions()` called BEFORE creating permissions

**Exports (§8) — if the module scope includes exports**

- [ ] `ExcelExport` implements `FromQuery, WithHeadings, WithMapping, ShouldAutoSize`
- [ ] Every CRUD PDF export has its own Blade under `resources/views/exports/pdf/`
- [ ] Soft-deletable CRUD PDF exports show a `Status` column derived only from `deleted_at`
- [ ] Soft-deletable CRUD PDF exports use only `Active` / `Suspended` labels, never `Inactive`
- [ ] Export route registered BEFORE `/{uuid}` in routes file
- [ ] Same `FilterDTO` reused for both Excel and PDF

**Tests (§7)**

- [ ] Feature — full HTTP CRUD flow for the declared module scope
- [ ] Unit/Domain — domain invariants when custom Value Objects or rules exist
- [ ] Unit/Application — handlers with mocked repository when handler logic is non-trivial
- [ ] Integration — DB round-trip via Mapper when mapper or persistence logic is non-trivial
- [ ] Tests cover critical business invariants for Value Objects / Domain rules / storage flows when applicable

**OpenAPI (§9) — only if the module exposes API endpoints**

- [ ] Every API method has `@OA\Get/Post/Put/Delete/Patch`
- [ ] Every DTO/Resource has `@OA\Schema`

---

## PHASE 2 — FIX

For each ❌ FAIL: apply the minimal fix following the exact rules in BACKEND-PHP.md.
Use context7 to confirm the correct package API if unsure.
Use tavily to look up current best practices or CVEs if a security item is flagged.
When fixing storage concerns, verify Laravel filesystem disk usage, review `config/filesystems.php` + `.env.example` alignment, and keep all cloud storage concerns inside Infrastructure adapters.
When fixing architecture concerns, prefer the smallest change that restores hexagonal boundaries, SRP, DRY, and CQRS separation.
If the module was misclassified, correct the baseline first instead of forcing simple CRUDs into intermediate architecture or stripping necessary complexity from intermediate modules.

---

## PHASE 3 — VERIFICATION CHECKLIST

After all fixes, re-run EVERY item from Phase 1. Expected result:

✅ ALL items PASS
📊 Score: X/Y items — target 100%

If any item remains ❌, repeat Phase 2 → Phase 3 until perfect score.
