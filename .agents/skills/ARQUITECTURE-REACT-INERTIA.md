# React + Inertia 2.0 · Enterprise Frontend Architecture (2026)

> Stack: React 19 · Inertia.js 2.0 · TypeScript 5 · TanStack Query v5 · TanStack Table v8 · Tailwind CSS · shadcn/ui

---

## Directory Structure Overview

```
resources/
├── css/
│   ├── app.css                          # Tailwind entry + CSS custom tokens
│   └── base/                            # Base resets, typography overrides
│
└── js/
    ├── app.tsx                          # Inertia createInertiaApp entry point
    ├── ssr.tsx                          # SSR entry point (if enabled)
    │
    ├── common/                          # 🔵 Generic, domain-agnostic UI primitives
    │   ├── button/
    │   │   └── Button.tsx
    │   ├── card/
    │   │   ├── Card.tsx
    │   │   ├── CardHeader.tsx
    │   │   └── CardContent.tsx
    │   ├── data-table/                  # Generic headless TanStack Table wrapper
    │   │   ├── DataTable.tsx            # Generic <DataTable columns={columns} data={data} />
    │   │   ├── DataTableToolbar.tsx
    │   │   ├── DataTablePagination.tsx
    │   │   ├── DataTableColumnHeader.tsx
    │   │   ├── DataTableBulkActions.tsx
    │   │   ├── DataTableDateRangeFilter.tsx  # 🔥 Reusable date-between picker (shadcn Calendar + Popover) — validates dateFrom ≤ dateTo
    │   │   └── DeleteConfirmModal.tsx        # 🔥 Glassmorphic full-screen modal replacing window.confirm() for deletes
    │   ├── form/                        # Generic form primitives
    │   │   ├── FormField.tsx
    │   │   ├── FormError.tsx
    │   │   └── FormSection.tsx
    │   ├── feedback/                    # Loading, empty, error states
    │   │   ├── Spinner.tsx
    │   │   ├── EmptyState.tsx
    │   │   ├── ErrorBoundary.tsx
    │   │   └── SkeletonRow.tsx
    │   ├── helpers/                     # Pure utility functions (no React)
    │   │   ├── cn.ts                    # clsx + tailwind-merge helper
    │   │   ├── formatDate.ts
    │   │   ├── formatCurrency.ts
    │   │   └── formatPhone.ts
    │   ├── export/                          # 🔥 Generic export controls (used by ALL index pages)
    │   │   ├── ExportButton.tsx             # Dropdown: Export Excel | Export PDF — forwards current filters
    │   │   └── ExportMenu.tsx              # Inner menu items with per-format loading state
    │   └── hooks/                       # Generic reusable hooks
    │       ├── useDebounce.ts
    │       ├── useLocalStorage.ts
    │       └── useIntersectionObserver.ts
    │
    ├── modules/                         # 🟡 Domain-specific shared components
    │   │                                # (used by multiple pages across contexts)
    │   │   # ─────────────────────────────────────────────────────────────────────────────
    │   │   # REFERENCE: auth/ and users/ are the complete reference modules.
    │   │   # For each new context, create: modules/{your-context}/components/ hooks/ types.ts
    │   │   # ─────────────────────────────────────────────────────────────────────────────
    │   │
    │   ├── auth/                        # 🔐 Complete reference — authentication
    │   │   ├── components/
    │   │   │   ├── Avatar.tsx
    │   │   │   └── PermissionGuard.tsx  # Conditional rendering by role/permission
    │   │   ├── hooks/
    │   │   │   └── useCurrentUser.ts    # Reads usePage().props.auth.user (Inertia shared prop)
    │   │   └── types.ts
    │   │
    │   ├── users/                       # 👤 Complete CRUD reference — use as a model for all modules
    │   │   ├── components/
    │   │   │   ├── UserStatusBadge.tsx  # Status badge — pattern for any entity
    │   │   │   ├── UserSummaryCard.tsx  # Summary card — pattern for show pages
    │   │   │   └── UserAvatar.tsx
    │   │   ├── hooks/
    │   │   │   ├── useUsers.ts          # TanStack Query: list — pattern useQuery<PaginatedResponse<T>>
    │   │   │   ├── useUser.ts           # TanStack Query: single — pattern useQuery<T>
    │   │   │   └── useUserMutations.ts  # TanStack Mutation: create/update/delete — pattern useMutation<T>
    │   │   ├── helpers/
    │   │   │   └── userStatusColor.ts
    │   │   └── types.ts
    │   │
    │   └── {your-context}/              # ⭐ TEMPLATE — Duplicate this block for each new module
    │       ├── components/
    │       │   ├── {YourEntity}StatusBadge.tsx
    │       │   └── {YourEntity}SummaryCard.tsx
    │       ├── hooks/
    │       │   ├── use{YourEntities}.ts        # TanStack Query: list (see pattern in users/)
    │       │   ├── use{YourEntity}.ts          # TanStack Query: single
    │       │   └── use{YourEntity}Mutations.ts # TanStack Mutation: create/update/softDelete
    │       ├── helpers/
    │       │   └── {yourEntity}StatusColor.ts
    │       └── types.ts                        # Local module interfaces
    │
    ├── pages/                           # 🟢 Inertia Page components
    │   │                                # Structure mirrors URL routes
    │   ├── layouts/
    │   │   ├── AppLayout.tsx            # Main authenticated layout (sidebar + header)
    │   │   ├── AuthLayout.tsx           # Unauthenticated layout (login, register)
    │   │   └── GuestLayout.tsx          # Public-facing layout
    │   │
    │   ├── dashboard/
    │   │   └── DashboardPage.tsx
    │   │
    │   ├── users/                       # 👤 Complete CRUD reference of pages
    │   │   ├── components/              # Private components of this page group
    │   │   │   ├── UserFilters.tsx          # Search + status dropdown + date range
    │   │   │   ├── UserDateRangeFilter.tsx  # Wrapper of DataTableDateRangeFilter
    │   │   │   ├── UserBulkActionsBar.tsx   # Bulk actions (delete, export selected)
    │   │   │   └── UserExportBar.tsx        # Wrapper of ExportButton with module filters
    │   │   ├── helpers/
    │   │   │   └── buildUserQueryParams.ts  # Serialize UserFilters → URLSearchParams
    │   │   ├── UsersIndexPage.tsx           # GET /users — table + filters + export
    │   │   ├── UserShowPage.tsx             # GET /users/{id}
    │   │   ├── UserCreatePage.tsx           # GET /users/create
    │   │   └── UserEditPage.tsx             # GET /users/{id}/edit
    │   │
    │   ├── auth/                        # /login, /register, etc.
    │   │   ├── LoginPage.tsx
    │   │   ├── RegisterPage.tsx
    │   │   └── ForgotPasswordPage.tsx
    │   │
    │   └── {your-context}/              # ⭐ TEMPLATE — Duplicate for each new module
    │       │   # Follows exactly the same pattern as pages/users/
    │       ├── components/
    │       │   ├── {YourEntity}Filters.tsx        # search + status + date range
    │       │   ├── {YourEntity}DateRangeFilter.tsx # Wrapper of DataTableDateRangeFilter
    │       │   ├── {YourEntity}BulkActionsBar.tsx
    │       │   └── {YourEntity}ExportBar.tsx       # Wrapper of ExportButton
    │       ├── helpers/
    │       │   └── build{YourEntity}QueryParams.ts
    │       ├── {YourEntities}IndexPage.tsx          # Index: DataTable + filters + ExportButton + DateRangeFilter
    │       ├── {YourEntity}ShowPage.tsx             # Show: full detail
    │       ├── {YourEntity}CreatePage.tsx           # Create: form
    │       └── {YourEntity}EditPage.tsx             # Edit: form + current data

    ├── shadcn/                          # 🔶 Auto-generated shadcn/ui components
    │   ├── button.tsx                   # DO NOT hand-edit these files
    │   ├── dialog.tsx                   # Regenerate via: npx shadcn@latest add
    │   ├── input.tsx
    │   ├── select.tsx
    │   ├── table.tsx
    │   ├── badge.tsx
    │   ├── calendar.tsx                 # Used by DateRangeFilter
    │   ├── popover.tsx
    │   └── dropdown-menu.tsx
    │
    └── types/                           # 🔷 Global TypeScript declarations
        ├── inertia.d.ts                 # Inertia PageProps augmentation
        ├── api.ts                       # API response interfaces (per context)
        ├── props.ts                     # Shared prop types (PropsWithClassName, etc.)
        └── globals.d.ts                 # Global ambient declarations (e.g., route())
```

---

## Layer Responsibilities

### `common/` — Generic primitives

Framework-agnostic, domain-agnostic. These could theoretically live in any project. Contains base UI components (`Button`, `Card`), the generic `DataTable` wrapper, pure helper functions, and reusable hooks with no domain knowledge.

**Rule:** Nothing in `common/` may import from `modules/` or `pages/`.

### `modules/` — Domain-specific shared code

Business-domain components and hooks used across **multiple pages**. Each module maps to a bounded context in the backend. Contains TanStack Query hooks, domain-specific components like `UserStatusBadge`, and the TypeScript types derived from backend DTOs.

**Rule:** Nothing in `modules/` may import from `pages/`. Modules may import from `common/` and from other modules only via their public `types.ts`.

### `pages/` — Inertia Page components

One directory per route group, mirroring the URL structure. Page components are the **only** components that use `usePage()`, `useForm()` from Inertia, and consume module-level hooks. Each page directory may contain local `components/`, `helpers/` — these are private to that page group and never imported from outside.

**Rule:** Pages import from `modules/` and `common/`. Never the reverse.

### `shadcn/` — UI library primitives

Auto-generated by the shadcn CLI. Never hand-edited. Wrap shadcn components in `common/` abstractions when the raw API is too verbose for application code.

### `types/` — TypeScript contracts

Single source of truth for all shared TypeScript interfaces. API response shapes here must mirror backend DTO field names exactly.

---

## File Naming Conventions

| What             | Convention                  | Example                       |
| ---------------- | --------------------------- | ----------------------------- |
| React components | `PascalCase.tsx`            | `{YourEntity}StatusBadge.tsx` |
| React contexts   | `PascalCase.tsx`            | `{YourContext}Context.tsx`    |
| Hooks            | `camelCase.ts`              | `useUsers.ts`                 |
| Helpers / utils  | `camelCase.ts`              | `formatCurrency.ts`           |
| Type files       | `camelCase.ts`              | `types.ts`, `api.ts`          |
| Directories      | `kebab-case`                | `data-table/`, `users/`       |
| Inertia Pages    | `{YourModule}IndexPage.tsx` | `{YourEntities}IndexPage.tsx` |
| Layouts          | `PascalCaseLayout.tsx`      | `AppLayout.tsx`               |

---

## Inertia Page Component Pattern

Every Inertia page follows this structure:

```tsx
// pages/users/UsersIndexPage.tsx
import { Head } from "@inertiajs/react";
import { usePage } from "@inertiajs/react";
import { AppLayout } from "@/pages/layouts/AppLayout";
import { UsersTable } from "./components/UsersTable";
import { UserFilters } from "./components/UserFilters";
import { useUsers } from "@/modules/users/hooks/useUsers";
import type { UsersIndexPageProps } from "@/types/api";

// ✅ Default export required — Inertia resolves by filename
export default function UsersIndexPage(): React.JSX.Element {
    const { filters } = usePage<UsersIndexPageProps>().props;

    return (
        <>
            <Head title="Users" />
            <AppLayout>
                <UserFilters initialFilters={filters} />
                <UsersTable />
            </AppLayout>
        </>
    );
}
```

**Rules:**

- Always `export default` — Inertia requires it.
- Always include `<Head title="..." />` for SEO/tab title.
- Layout wraps the page content — never wraps the entire component tree.
- Explicit return type `React.JSX.Element`.
- Typed via `usePage<PagePropsInterface>()` — never untyped.

---

## TanStack Query Hook Pattern

All server-state hooks live in `modules/{context}/hooks/`:

```ts
// modules/users/hooks/useUsers.ts
import { useQuery, keepPreviousData } from "@tanstack/react-query";
import type { PaginatedResponse, UserListItem, UserFilters } from "@/types/api";

async function fetchUsers(
    filters: UserFilters,
): Promise<PaginatedResponse<UserListItem>> {
    const params = new URLSearchParams(filters as Record<string, string>);
    const response = await fetch(`/api/users?${params}`);
    if (!response.ok) throw new Error("Failed to fetch users");
    return response.json() as Promise<PaginatedResponse<UserListItem>>;
}

export function useUsers(filters: UserFilters) {
    return useQuery<PaginatedResponse<UserListItem>, Error>({
        queryKey: ["users", "list", filters],
        queryFn: () => fetchUsers(filters),
        placeholderData: keepPreviousData, // ✅ v5 — replaces keepPreviousData option
        staleTime: 1000 * 60 * 2,
    });
}
```

**Rules:**

- Always type generics explicitly: `useQuery<TData, TError>`.
- Query key hierarchy: `['context', 'operation', ...params]`.
- `placeholderData: keepPreviousData` for paginated lists — prevents blank state during page changes.
- `fetchFn` defined outside the hook — pure async function, easy to test.
- Errors typed as `Error` — never `unknown` in the generic.

---

## TanStack Mutation Hook Pattern

```ts
// modules/users/hooks/useUserMutations.ts
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { CreateUserDTO, UserDetail } from "@/types/api";

async function createUser(data: CreateUserDTO): Promise<UserDetail> {
    const response = await fetch("/api/users", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
    });
    if (!response.ok) throw new Error("Failed to create user");
    return response.json() as Promise<UserDetail>;
}

export function useCreateUser() {
    const queryClient = useQueryClient();

    return useMutation<UserDetail, Error, CreateUserDTO>({
        mutationFn: createUser,
        onSuccess: () => {
            // ✅ Always invalidate after mutation
            queryClient.invalidateQueries({ queryKey: ["users"] });
        },
    });
}
```

---

## TanStack Table Pattern (Server-Side)

Generic `DataTable` lives in `common/data-table/`. Page-specific columns are defined locally:

````tsx
```tsx
// pages/users/components/UsersTable.tsx
import { useMemo, useState, useTransition } from "react";
import {
    getCoreRowModel,
    getSortedRowModel,
    type ColumnDef,
    type SortingState,
    type RowSelectionState,
    type OnChangeFn,
} from "@tanstack/react-table";
import { DataTable } from "@/components/ui/data-table";
import { DataTablePagination } from "@/common/data-table/DataTablePagination";
import UserStatusBadge from "@/modules/users/components/UserStatusBadge";
import type { UserListItem, UserFilters } from "@/types/api";

interface UsersTableProps {
    data: UserListItem[];
    isLoading: boolean;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
    onDeleteClick: (uuid: string, name: string) => void;
}

export function UsersTable({ data, isLoading, rowSelection, onRowSelectionChange, onDeleteClick }: UsersTableProps): React.JSX.Element {
    // ✅ Memoized — never redefined on each render
    const columns = useMemo<ColumnDef<UserListItem>[]>(() => [
        {
            id: "select",
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                />
            ),
        },
        { accessorKey: "userName", header: "Name" },
        { accessorKey: "email", header: "Email" },
        {
            accessorKey: "status",
            header: "Status",
            cell: ({ getValue }) => <UserStatusBadge status={getValue<string>()} />,
        },
        {
            id: "actions",
            header: "Actions",
            cell: ({ row }) => (
                <button onClick={() => onDeleteClick(row.original.id, row.original.userName)}>
                     Delete
                </button>
            )
        }
    ], [onDeleteClick]);

    return (
        <div>
            <DataTable
                columns={columns}
                data={data}
                isLoading={isLoading}
                rowSelection={rowSelection}
                onRowSelectionChange={onRowSelectionChange}
            />
        </div>
    );
}
````

**Rule for Soft Deletes:** Any row where the data object contains a truthy `deletedAt` or `deleted_at` field will automatically be styled by the `DataTable` with a red-tinted background and reduced opacity. No extra column definitions are needed for this behavior.

**Rule for Deletion:** Use the `DeleteConfirmModal` instead of `window.confirm()`. The table just emits `onDeleteClick`, and the parent (`IndexPage`) manages the `pendingDelete` state and renders the `DeleteConfirmModal`.

---

## TypeScript Types Structure

```ts
// types/inertia.d.ts — Inertia page props augmentation
import type { PageProps as InertiaPageProps } from "@inertiajs/core";

interface AuthUser {
    id: string;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
}

declare module "@inertiajs/core" {
    interface PageProps extends InertiaPageProps {
        auth: { user: AuthUser };
        flash: { success?: string; error?: string; warning?: string };
        ziggy: {
            url: string;
            port: number | null;
            routes: Record<string, unknown>;
        };
    }
}
```

```ts
// types/api.ts — API response contracts (mirrors backend DTOs exactly)

// ── Shared ────────────────────────────────────────────────────
export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

// ── Users ────────────────────────────────────────────────────
export type UserStatus =
    | "active"
    | "under_review"
    | "approved"
    | "rejected"
    | "in_progress"
    | "closed";

export interface UserListItem {
    id: string;
    userName: string;
    roleId: string;
    roleName: string;
    createdById: string | null;
    createdByName: string | null;
    status: UserStatus;
    profileUrl: string | null;
    createdAt: string; // ISO 8601
}

export interface UserDetail extends UserListItem {
    description: string;
    displayName: string;
    email: string;
    status: string;
    updatedAt: string;
}

export interface CreateUserDTO {
    roleId: string;
    email: string;
    description: string;
    displayName: string;
    status: string;
}

export interface UserFilters {
    page?: number;
    perPage?: number;
    search?: string;
    status?: UserStatus | ""; // Backed Enum value (active | inactive | suspended | banned)
    dateFrom?: string; // ISO 8601 date string 'YYYY-MM-DD' — validated: dateFrom ≤ dateTo
    dateTo?: string; // ISO 8601 date string 'YYYY-MM-DD'
    sortBy?: string;
    sortDir?: "asc" | "desc";
    createdById?: string;
}

// ── Shared Export Types ────────────────────────────────────────
export type ExportFormat = "excel" | "pdf";

export interface ExportParams {
    format: ExportFormat;
    dateFrom?: string;
    dateTo?: string;
    [key: string]: string | number | boolean | undefined;
}

// ── Page Props ─────────────────────────────────────────────────
export interface UsersIndexPageProps {
    filters: UserFilters;
}
```

```ts
// types/props.ts — Shared React prop utility types
import type { ClassValue } from "clsx";

export type PropsWithClassName<T = unknown> = T & { className?: ClassValue };
export type PropsWithChildren<T = unknown> = T & { children: React.ReactNode };
export type PropsWithOptionalChildren<T = unknown> = T & {
    children?: React.ReactNode;
};
```

---

## Import Conventions

Use **absolute path aliases** — never relative `../../` imports beyond 1 level:

```ts
// tsconfig.json paths
{
  "compilerOptions": {
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  }
}

// vite.config.ts
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  resolve: {
    alias: { '@': path.resolve(__dirname, 'resources/js') },
  },
});
```

```ts
// ✅ Correct — absolute alias
import { useUsers } from "@/modules/users/hooks/useUsers";
import { Button } from "@/common/button/Button";
import type { UserListItem } from "@/types/api";

// ❌ Wrong — relative path crossing directories
import { useUsers } from "../../../modules/users/hooks/useUsers";
```

**Import block order** (enforced by `prettier-plugin-sort-imports`):

1. Node built-ins
2. External library imports
3. Internal `@/` imports
4. Relative `./ ../` imports (same directory only)

---

## Component Rules

- **One component per file** — no multiple exports from one `.tsx` file.
- **Named exports** for all components except Inertia Pages (which require `export default`).
- **No barrel files (`index.ts`)** — they create import indirection and are hard to enforce.
- **Props via `interface`** — always `interface FooProps`, never inline `{ prop: string }`.
- **Explicit return types** — always `React.JSX.Element` or `React.ReactNode`.
- **`function` declaration over `const` arrow** for components — better stack traces and readability:

    ```tsx
    // ✅ Preferred
    export function UserStatusBadge({
        status,
    }: UserStatusBadgeProps): React.JSX.Element {}

    // ❌ Avoid for components
    export const UserStatusBadge = ({
        status,
    }: UserStatusBadgeProps): React.JSX.Element => {};
    ```

- **`className` and `children` always last** in props — consistent prop ordering.

---

## State Management Decision Tree

```
Is the data fetched from the server?
  → YES → TanStack Query (useQuery / useMutation)

Is the data page-level initial state from Laravel?
  → YES → Inertia usePage().props (read-only, don't cache in Query)

Is the data a user's in-progress form?
  → YES → Inertia useForm() for page-navigation forms
           OR useMutation for API-only mutations

Is the data UI state (open/closed, selected tab, filter values)?
  → YES → useState / useReducer locally in the component

Does the UI state need to survive navigation?
  → YES → Inertia useRemember() to persist across visits

Is the state shared across multiple unrelated components?
  → YES → React Context (sparingly) — create in modules/{context}/contexts/
```

**Golden Rule:** Never duplicate the same data in both Inertia props and TanStack Query cache. Pick one source of truth per data type.

---

## Multi-Zone Apps (Admin vs. Client)

For roofing insurance, multiple actor dashboards (Admin, Manager, Client, Guest) can be organized as separate apps:

```
resources/js/
├── apps/
│   ├── admin/
│   │   ├── pages/
│   │   └── app.tsx
│   ├── manager/
│   │   ├── pages/
│   │   └── app.tsx
│   └── client/
│       ├── pages/
│       └── app.tsx
├── common/                   # Shared across ALL apps
└── modules/                  # Shared domain modules across apps
```

---

## CSS & Styling

- **Tailwind only** — no inline styles, no CSS Modules, no styled-components.
- All design tokens defined in `app.css` as CSS custom properties — follow `@rules-styles.md`.
- Use `cn()` helper (`clsx` + `tailwind-merge`) for conditional class merging:
    ```ts
    // common/helpers/cn.ts
    import { type ClassValue, clsx } from "clsx";
    import { twMerge } from "tailwind-merge";
    export function cn(...inputs: ClassValue[]) {
        return twMerge(clsx(inputs));
    }
    ```
- **Never** use arbitrary Tailwind values like `bg-[#1a1a1a]` — use CSS token variables instead.

---

## Quick Reference: Where Does This File Go?

| What you're creating                         | Directory                                        |
| -------------------------------------------- | ------------------------------------------------ |
| Reusable UI primitive (Button, Badge, Modal) | `common/{name}/`                                 |
| Generic table wrapper component              | `common/data-table/`                             |
| Domain component used on multiple pages      | `modules/{context}/components/`                  |
| TanStack Query hook for a domain             | `modules/{context}/hooks/`                       |
| Inertia Page component                       | `pages/{route-group}/`                           |
| Component used only by one page group        | `pages/{route-group}/components/`                |
| Helper used only by one page group           | `pages/{route-group}/helpers/`                   |
| Global layout                                | `pages/layouts/`                                 |
| shadcn/ui component                          | `shadcn/` (CLI generated only)                   |
| Inertia PageProps interface                  | `types/inertia.d.ts`                             |
| API response / DTO interfaces                | `types/api.ts`                                   |
| Shared prop utility types                    | `types/props.ts`                                 |
| Generic export dropdown button               | `common/export/ExportButton.tsx`                 |
| Date range filter for any table              | `common/data-table/DataTableDateRangeFilter.tsx` |

---

## DataTableDateRangeFilter — Usage Contract

**File**: `common/data-table/DataTableDateRangeFilter.tsx`
**Dependencies**: `shadcn/calendar.tsx`, `shadcn/popover.tsx`, `shadcn/button.tsx`

```tsx
// Props contract
interface DataTableDateRangeFilterProps {
    dateFrom: string | undefined;
    dateTo: string | undefined;
    onChange: (range: { dateFrom?: string; dateTo?: string }) => void;
    disabled?: boolean;
    className?: string;
}

// Rules:
// - Both fields are OPTIONAL — empty state means "no date filter"
// - Client-side validation: if both are set, dateFrom MUST be ≤ dateTo
//   If dateFrom > dateTo: show inline error, do NOT fire onChange until corrected
// - Dates stored and emitted as ISO 8601 strings 'YYYY-MM-DD'
// - On change: wrap in useTransition() — non-urgent update
// - Renders a single trigger button showing the selected range or "Pick a date range"
// - Uses shadcn Calendar in range selection mode
// - Clear button resets both fields to undefined

// Usage in an index page:
const [, startTransition] = useTransition();
const [filters, setFilters] = useRemember<UserFilters>({}, "user-filters");

<DataTableDateRangeFilter
    dateFrom={filters.dateFrom}
    dateTo={filters.dateTo}
    onChange={({ dateFrom, dateTo }) =>
        startTransition(() =>
            setFilters((prev) => ({ ...prev, dateFrom, dateTo, page: 1 })),
        )
    }
/>;
```

**Rule**: Every `{Module}IndexPage` MUST include this component. `dateFrom` and `dateTo` MUST be part of the TanStack Query key so changing the range triggers a fresh fetch.

---

## ExportButton — Usage Contract

**File**: `common/export/ExportButton.tsx`
**Dependencies**: `shadcn/dropdown-menu.tsx`, `shadcn/button.tsx`, `common/feedback/Spinner.tsx`

```tsx
// Props contract
interface ExportButtonProps {
    endpoint: string; // '/api/users/export'
    filters: Record<string, string | number | boolean | undefined>; // current active filters
    formats?: ReadonlyArray<ExportFormat>; // default: ['excel', 'pdf']
    disabled?: boolean;
    className?: string;
}

// Rules:
// - Renders a single dropdown trigger button labeled "Export"
// - Menu items: "Export Excel (.xlsx)" and "Export PDF"
// - On click: builds URL = endpoint + '?format=excel|pdf&' + URLSearchParams(filters)
//   Triggers file download via window.open(url, '_blank') or <a download> technique
// - Tracks isPendingExcel / isPendingPdf independently — spinner only in active item
// - All current filters (dateFrom, dateTo, search, status, etc.) MUST be forwarded
// - On network error: display toast with message from response body
// - Button is never disabled due to unknown row count — always available when endpoint exists
// - ExportMenu.tsx contains only the rendered menu items (pure presentational)

// Usage:
<ExportButton
    endpoint="/api/users/export"
    filters={{
        dateFrom: filters.dateFrom,
        dateTo: filters.dateTo,
        status: filters.status,
        search: filters.search,
    }}
/>;
```

**Rule**: Every `{Module}IndexPage` MUST include this component, placed in the toolbar area alongside search/filter controls.
