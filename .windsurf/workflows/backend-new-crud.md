---
description: Generates a simple Laravel 12 / PHP 8.5 CRUD module for standard entities without enterprise overengineering. 5-line summary.
---

# BACKEND NEW SIMPLE CRUD AGENT — PHP 8.5 + Laravel 12

## PHASE 0 — QUALIFY THE REQUEST

Before writing any code, you MUST:

1. Read `.windsurf/skills/SKILLS-FULL-STACK/BACKEND-PHP.md`
2. Read `.windsurf/skills/SKILLS-FULL-STACK/ARCHITECTURE-SIMPLE-CRUD-PHP.md`
3. Call context7 to resolve current docs for: Laravel 12, Spatie Laravel Data 4.x, Spatie Permission 6.x, Spatie Activitylog, Pest 3
4. Call tavily to verify the latest stable versions of all packages you will touch

Only continue if the requested module qualifies as a simple CRUD:

- One aggregate / one main entity
- Around 3 to 8 persisted fields
- Standard CRUD + restore
- `bulk delete` is allowed when the table UI needs batch selection, as long as it remains a simple soft-delete batch action
- No files/media, exports, queues, WebSockets, external integrations, or complex cross-module orchestration unless explicitly requested
- No rich workflow/state machine that would justify the intermediate architecture

If the request does NOT qualify, STOP and instruct the user to use `/backend-new` instead.

---

## PHASE 1 — PLAN (produce checklist)

Before generating files, produce a checklist and mark each item as:

- ✅ DONE
- ❌ SKIPPED (with reason)
- ⚠️ WARN

### Required Checklist

**PHP 8.5**

- [ ] `declare(strict_types=1)` in every `.php` file
- [ ] Explicit return type on every method
- [ ] Use modern PHP 8.5 syntax when it naturally improves the file
- [ ] Do NOT force `|>`, `clone(...)`, `#[\NoDiscard]`, or other features artificially when the file does not benefit from them

**Architecture (ARCHITECTURE-SIMPLE-CRUD-PHP.md)**

- [ ] Module lives in `src/Modules/{Name}/` with `Domain / Application / Infrastructure`
- [ ] Application folders stay flat: `DTOs`, `Commands`, `Queries`
- [ ] Domain imports nothing from Laravel or Infrastructure
- [ ] Domain contains the entity, ID Value Object, and repository port
- [ ] Additional Value Objects are created only for fields with real invariants
- [ ] DTOs extend `Spatie\LaravelData\Data` and are NOT `readonly`
- [ ] Mapper is the ONLY contact point between Domain and Eloquent
- [ ] One repository port + one Eloquent repository implementation
- [ ] Controllers stay thin
- [ ] ServiceProvider is registered in `bootstrap/providers.php`
- [ ] `bulk delete` uses one request/DTO + one command handler when the UI requires batch deletion

**Audit / Observability**

- [ ] Model uses `LogsActivity` with explicit `logOnly([...])`
- [ ] `logOnlyDirty()` + `dontSubmitEmptyLogs()` both present
- [ ] `AuditPort` is added only if the module has meaningful business actions beyond simple lifecycle logging
- [ ] No passwords, tokens, or sensitive fields are logged

**Security**

- [ ] No raw SQL with user input
- [ ] No `unserialize()` on external input
- [ ] `->whereUuid('uuid')` on UUID routes
- [ ] Permissions defined: `VIEW_X`, `CREATE_X`, `UPDATE_X`, `DELETE_X`, `RESTORE_X`
- [ ] `forgetCachedPermissions()` called before creating permissions

**Routes**

- [ ] Web routes are primary
- [ ] Data endpoints live under `/data/admin/*` when JSON CRUD is needed for the web app
- [ ] `POST /bulk-delete` exists only when the module exposes batch selection/delete in the UI
- [ ] API routes are only added if explicitly requested

**Tests**

- [ ] Feature tests cover the HTTP CRUD flow
- [ ] `bulk delete` has feature coverage when the endpoint exists
- [ ] Unit tests exist for custom Value Objects or domain invariants when present
- [ ] Integration tests exist only when mapper logic or persistence rules are non-trivial

**Conditional Sections — only if explicitly requested**

- [ ] Exports
- [ ] If PDF export is requested for a CRUD, create a dedicated Blade under `resources/views/exports/pdf/`
- [ ] If the CRUD uses `SoftDeletes`, PDF exports must show `Status` derived only from `deleted_at`: `Active` when `deleted_at === null`, `Suspended` when `deleted_at !== null`
- [ ] `Inactive` is forbidden as the soft-delete label in CRUD PDF exports
- [ ] File storage / uploads
- [ ] API + OpenAPI annotations
- [ ] Queue / events / listeners
- [ ] WebSockets / Reverb

---

## PHASE 2 — GENERATE

Generate only the minimal files required by the request.

Rules:

- Do not create `Storage`, `Queue`, `WebSocket`, `Export`, `ExternalServices`, `Api`, listeners, or events unless the request truly needs them.
- Do not create speculative abstractions for a 5-field CRUD.
- Prefer one clear flow over a “perfect” enterprise tree.
- Keep controllers thin and business rules out of controllers.
- Keep the domain model clean, but do not invent fake complexity.
- If `bulk delete` is requested, keep it as one simple batch command over UUIDs, not as a separate sub-architecture.
- If PDF export is requested, create a dedicated CRUD Blade under `resources/views/exports/pdf/`.
- In soft-deletable CRUD PDF exports, derive `Status` only from `deleted_at` using `Active` / `Suspended` labels.
- Use context7 to confirm package APIs before writing framework-specific files.
- Use tavily when a security-sensitive or package-version-sensitive decision is involved.

---

## PHASE 3 — VERIFY

After generating files, re-run the Phase 1 checklist.

Expected result:

- ✅ All required items PASS
- 📊 Score: X/Y required items
- 📝 Conditional items clearly marked as DONE or SKIPPED with reason

If any required item fails, fix the minimum necessary code and verify again.

Then respond with EXACTLY 5 lines:

```text
✅ Simple CRUD {Name} generated — {N} files with minimal Domain / Application / Infrastructure.
🧱 Entity, repository port, mapper, handlers, controllers, requests, and routes are in place.
🔐 Permissions seeded: VIEW_{X}, CREATE_{X}, UPDATE_{X}, DELETE_{X}, RESTORE_{X}.
🧪 Tests: Feature CRUD {plus optional Unit/Integration if justified}.
📊 {score}/{total} required rules passed · Simple CRUD baseline preserved.
```
