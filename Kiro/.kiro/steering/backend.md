---
inclusion: auto
name: backend
description: Laravel 12 PHP 8.5 backend. Use when creating or modifying PHP files, controllers, handlers, domain entities, migrations, seeders, or routes.
---

Read the full skills before writing any backend code:
#[[file:.agents/skills/RULES-FULLSTACK.md]]
#[[file:.agents/skills/ARCHITECTURE-INTERMEDIATE-PHP.md]]
#[[file:.agents/skills/HOW-TO-USE.md]]

Key reminders:
- `declare(strict_types=1);` in every file. Explicit return types always.
- Domain in `src/Modules/{Module}/Domain/` — no Eloquent, no Laravel.
- Commands + Handlers for writes. Queries for reads. No logic in Controllers.
- `SoftDeletes` + `LogsActivity` on every EloquentModel.
- Dates in domain = ISO 8601 strings. Never Carbon in domain entities.
- Bindings in `{Module}ServiceProvider` registered in `bootstrap/providers.php`.
- Always `uuid`, never `id` in public-facing operations.