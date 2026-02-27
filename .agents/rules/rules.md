---
trigger: always_on
---

# [ABSOLUTE] Non-negotiable constraints — ALWAYS apply

- **Language:** Respond in English at all times.
- **CLI:** Use `./vendor/bin/sail artisan` — NEVER bare `php`.
- **TypeScript:** Strict mode enforced on ALL `.tsx` / `.ts` files.

---

# [MUST] Before writing any code — read the relevant skill

| Task type                | Required reading                               |
| ------------------------ | ---------------------------------------------- |
| PHP / Laravel backend    | `.agents/skills/ARQUITECTURE-PHP.md`           |
| React / Inertia frontend | `.agents/skills/ARQUITECTURE-REACT-INERTIA.md` |
| CSS / Styles / UI        | `.agents/skills/rules-styles.md`               |
| PHP coding rules         | `.agents/skills/RULES-PHP-2026.md`             |

> **Rule:** If a skill file covers the task, read it FIRST — no exceptions.

---

# [MUST] CSS / Styles — Token-First

- NEVER hardcode hex colors in components. Use `var(--token)`.
- NEVER use inline `rgba()` — use `color-mix(in srgb, var(--token) N%, transparent)`.
- All tokens are defined in `resources/css/globals.css`.
- Dark is the default theme. Light override lives in `[data-theme="light"]`.
- **CRUD Actions:** Buttons (View, Edit, Delete, Restore) MUST use `btn-action` utilities with modern backgrounds, borders, and rounded corners.
- **Add Buttons:** Main "Add" buttons in CRUDs MUST NOT be oversized. Use size-reduced padding (e.g., `px-4 py-2`) and `btn-modern-primary`.
- All table cards MUST use the `card-modern` class for consistent premium aesthetic.

---

# [MUST] React / TypeScript

- Every page must be wrapped in its correct Layout (`AppLayout`, `AuthLayout`, etc.).
- State typing: always explicit generics — `useState<string>('')`.
- Form handlers: validate client-side before any `router.post/put`.
- No `any`. No `@ts-ignore`. No inline styles with hardcoded colors.

---

# [MUST] Laravel / PHP

- Domain logic lives in `src/Contexts/<Module>/Domain/`.
- No business logic in Controllers — only orchestration.
- Use Commands + Handlers (CQRS) for writes, Queries for reads.
- Seeders: always call `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()` after batch-creating permissions.
- CRUD modules MUST include `DELETE /{uuid}` + `PATCH /{uuid}/restore` routes for soft-delete/restore in both web (Inertia) and API route groups. Always use `{uuid}`, never `{id}`.

---

# [SHOULD] General quality

- Prefer descriptive names over comments.
- Mobile-first responsive on every UI component.
- `font-family: var(--font-sans)` everywhere — Inter is the project font.
- Sidebar nav uses `AppLayout`; public/auth pages use their own layout.
