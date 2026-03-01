---
inclusion: auto
name: frontend
description: React 19 Inertia 2.0 frontend. Use when creating or modifying TSX components, pages, hooks, or TanStack Query hooks.
---

Read the full skills before writing any frontend code:
#[[file:.agents/skills/ARCHITECTURE-REACT-INERTIA.md]]
#[[file:.agents/skills/RULES-FULLSTACK.md]]

Key reminders:
- Every Inertia page: `export default`, `<Head title />`, correct Layout, typed via `usePage<T>()`.
- `<Link>` from `@inertiajs/react` always — never native `<a>` for internal nav.
- `router.*` API — `Inertia.visit()` is deprecated in v2.
- TanStack Query v5: `isPending` (not `isLoading`), `placeholderData: keepPreviousData`.
- Toast notifications: **Sileo** (`sileo`) only — use `sileo.success()`, `sileo.error()`, etc.
- Icons: `lucide-react` only — 14px tables, 18px menus/sidebar.
- Every Index page: total records count, 3 action icons, `DeleteConfirmModal`, `RestoreConfirmModal`, soft-deleted rows via `var(--deleted-row-bg)`.
- After completing a module: add sidebar nav item with `<PermissionGuard>`.