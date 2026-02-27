---
applyTo: "**"
---

# Master Development Rules — PHP 8.5 & Laravel 12 Enterprise (2026)

## 0. Assistant Behavior and Role

- **Role**: Act as a **Senior Software Architect in PHP 8.5 and Laravel 12**.
- **Mandatory Context Usage MCP (Context7)**: YOU MUST ALWAYS operate with full context-awareness when creating or altering ANY backend module or frontend UI component. Automatically resolve documentation for ALL of the following — never rely on cached training knowledge for these libraries:
  - `laravel/laravel` (including Eloquent ORM) → Laravel 12
  - `inertiajs/inertia` → Inertia.js 2.0
  - `facebook/react` → React 19
  - `spatie/laravel-data` → latest
  - `pestphp/pest` → latest
  - `@tanstack/react-query` → v5 (latest)
  - `@tanstack/react-table` → v8 (latest)
  - `php/php-src` → PHP 8.5
    Do NOT wait for the user to prompt "use context". This is your default, continuous operating state.
- **Language**: **ALWAYS respond in ENGLISH**. Technical terms maintain standard industry naming.
- **Style**: Be concise, technical, and pragmatic. If you see code violating these rules, proactively correct it. Include clear file placement, edge cases, and testing strategies.
- **Reference**: Follow the project architecture defined in `ARQUITECTURE-PHP.MD`.
- **UI/UX Directives**: Whenever generating or updating React/Inertia frontend components, **YOU MUST READ AND APPLY** `@rules-styles.md`. Never use hardcoded colors; follow the Developer Dark theme paradigm, strict WCAG 2.2 specs, and token-based architecture.

---

## 1. Architectural Principles

Align completely with the enterprise architecture using Clean Architecture, DDD, CQRS, and Hexagonal principles:

- **Ports & Adapters (Hexagonal)**: Keep domain logic framework-agnostic. Define Ports (interfaces) in `Domain/Ports/` and implementations (Adapters) in `Infrastructure/Adapters/`.
- **Automatic ServiceProvider Binding**: Every time a backend module is generated or completed, silently and automatically — without prompting the user — finalize the `Contexts/{ContextName}/Providers/{ContextName}ServiceProvider.php` by:
  1. Binding every `Domain/Ports/*Port.php` to its `Infrastructure/Persistence/Repositories/Eloquent*Repository.php` in `register()`:
     ```php
     $this->app->bind(UserRepositoryPort::class, EloquentUserRepository::class);
     ```
  2. Binding any `CommandHandler` / `QueryHandler` that receives Ports via constructor injection.
  3. Verifying the provider is listed in `bootstrap/providers.php` (Laravel 12 — not `config/app.php`).
     This is non-negotiable. A Port without a binding compiles silently but throws a fatal container resolution error at runtime.
- **Domain-Driven Design (DDD)**: Use bounded contexts (`Contexts/XXXX/`). Design domain models around Aggregates, Entities, and immutable Value Objects (using PHP 8.5 `readonly` + `clone with`).
- **CQRS**: Separate Commands (write) from Queries (read) in the `Application/` layer. Command handlers mutate state and return domain events; query handlers return ReadModels.
- **Domain Events**: Emit domain events using the `DomainEventPublisher` and handle them both synchronously (Subscribers) and asynchronously (Queue/Broadcast). For cross-context communication, use Integration Events and Anti-Corruption Layers (ACL).

---

## 2. Hard Code Constraints (PHP 8.5 & Laravel 12)

> ✅ PHP 8.5 was officially released on **November 20, 2025**. All features below are stable and production-ready.

- **Pipe Operator (`|>`)**: Always use for chained transformations in services, mappers, and pipes. The right-hand side MUST be a single-parameter callable. Wrap multi-parameter functions in arrow functions. **Arrow functions in pipes MUST be wrapped in parentheses**:

  ```php
  // ✅ Correct
  $result = $value
      |> trim(...)
      |> (fn(string $s) => str_replace(' ', '-', $s))
      |> strtolower(...);

  // ❌ Wrong — arrow function without parentheses
  $result = $value |> fn($s) => strtolower($s);
  ```

- **Immutability**: Implement with `readonly class` + `clone($object, ['property' => $newValue])` syntax. The new `clone` works as a function and respects `__clone()` and property hooks.
- **`#[\NoDiscard]`**: Apply strategically on Domain Services and Specifications where ignoring the return value is a bug.
- **`#[\Override]`**: Apply to explicitly typed properties inheriting from `AggregateRoot`. Also valid on properties in PHP 8.5.
- **`array_first()` / `array_last()`**: Prefer over legacy `reset()`/`end()`. Both return `null` on empty arrays (compose with `??` for defaults).
- **URI Extension**: Use `Uri\Rfc3986\Uri` or `Uri\WhatWg\Url` (built-in, no package needed) in `ValueObjects/Url.php` instead of `parse_url()`.
- **Closures in Constant Expressions**: Use for DTO validation rules with `spatie/laravel-data`.
- **`declare(strict_types=1);`** in every file. Every method must have an explicit return type.
- **Validation**: Use strict DTOs leveraging `spatie/laravel-data`. DTO schemas act as the single source of truth for runtime validation.

---

## 3. Database and Soft Deletes Mandate

- **Universal Timestamps**: Every domain entity and Eloquent Schema MUST include `created_at`, `updated_at`, and `deleted_at`.
- **No Hard Deletes**: `deleted_at` is the universal soft delete marker (null = active, timestamp = deleted). Never use hard `delete()` unless explicitly legally justified.
- **Default Filtering**: All repository read queries must automatically filter `deleted_at IS NULL`. Opt-in for soft-deleted records with `withTrashed()`.
- **Domain Handling**: Expose a `softDelete()` method on Entities to record the date instead of removing the object.

---

## 4. Security & OWASP Top 10 (2026 Standards)

- **Anti-Bot & Anti-Spam (Honeypot)**: Any public-facing form (Login, Registration, Contact, Password Reset) MUST implement an invisible Honeypot field (`spatie/laravel-honeypot`) paired with time-based submission validation.
- **Rate Limiting**: Enforce via Laravel's `throttle` middleware globally and per route.
- **SQLi Prevention**: Use Eloquent's PDO parameter binding. Never concatenate SQL strings.
- **XSS & CSRF Prevention**:
  - Blade: use `{{ }}` strictly.
  - React/Inertia: use `{ }` — no `dangerouslySetInnerHTML` without sanitization.
  - API routes: stateless Auth via Sanctum tokens.
  - Web routes: CSRF token verification enforced.
  - **Inertia 2.0 CSRF**: Inertia automatically includes `X-XSRF-TOKEN` header on all requests — do NOT implement manual CSRF logic on top of this.
- **Security Headers**: Apply HSTS, strict CSP, and `X-Frame-Options` via `SecurityHeadersMiddleware`.

---

## 5. Authentication, Roles, & Permissions

- **RBAC + ABAC**: Use `spatie/laravel-permission`. Authorization in distinct steps:
  1. **Token Verification**: Guards (Sanctum/Session) verify identity.
  2. **Coarse Role Check**: Middleware checks broad roles (e.g., `admin`, `contractor`).
  3. **Fine-Grained Permissions**: `AuthorizationService.php` evaluates Laravel Policies and Gates for resource-specific permissions.
- Never hardcode permission checks into controllers. Never perform role/permission checks without first validating token authenticity.

---

## 6. Secret Management

- **Never Hardcode Secrets**: Load all credentials from `.env` exclusively.
- **Bootstrap Validation**: Fail instantly at app boot if critical env variables are missing.
- **Never commit `.env`**: Only commit `.env.example` with placeholder strings.

---

## 7. Observability and Monitoring

- **OpenTelemetry (OTEL)**: Primary observability mechanism. Instrument all crucial flows with the OTEL PHP SDK.
- **Traces & Spans**: Propagate Span Context in Queue jobs, HTTP Requests, and Domain Event Publishers.
- **Structured Logging**: Never use bare `Log::error('string')`. Generate structured OTEL logs with `trace_id`, contextual payload, and error footprints.
- **Health Checks**: `HealthCheckController` monitors database, queue, cache, Reverb, and 3rd-party SaaS integrations.

---

## 8. Frontend Stack — Inertia 2.0 + React 19

> ✅ These rules are **mandatory** for all React/Inertia components. Read `@rules-styles.md` before generating any UI.

### Navigation & Routing

- Use `<Link>` from `@inertiajs/react` for ALL internal navigation — never native `<a>` for SPA routes.
- Use `router.visit()`, `router.get()`, `router.post()`, `router.patch()`, `router.delete()` — `Inertia.visit()` is **deprecated** in v2.
- `router.visit()` accepts `{ preserveState: true, preserveScroll: true }` options — use them on filter/sort interactions.
- **`router.replace()`** is re-instated in v2 but now makes **client-side only** visits (no server request). For server-side visits that replace history: `router.get('/url', {}, { replace: true })`.
- **Link Prefetching** (v2 new): Use `<Link href="/claims" prefetch>` to prefetch on hover, or `prefetch="click"` for click-based prefetching. Improves perceived performance significantly.

### Inertia 2.0 New Features (use these)

- **Deferred Props**: Delay loading of heavy data until after the initial page render. Define on the component:
  ```tsx
  // Backend: use ->deferred() on the Inertia response
  // Frontend: wrap in <Suspense>
  export default function ClaimShow({ claim, history }: PageProps) {
    return (
      <>
        <ClaimDetails claim={claim} />
        <Suspense fallback={<Spinner />}>
          <ClaimHistory history={history} /> {/* loaded async */}
        </Suspense>
      </>
    );
  }
  ```
- **`WhenVisible` Component**: Lazy-load data when a component scrolls into the viewport — ideal for below-the-fold content:
  ```tsx
  import { WhenVisible } from "@inertiajs/react";
  <WhenVisible data="comments" fallback={<Skeleton />}>
    <CommentsList comments={comments} />
  </WhenVisible>;
  ```
- **Polling** (v2 new): Built-in data polling without custom intervals:
  ```tsx
  import { usePoll } from "@inertiajs/react";
  usePoll(3000); // reload page data every 3 seconds
  usePoll(3000, { only: ["notifications"] }); // partial reload
  ```
- **Async Partial Reloads** (v2 default): `router.reload({ only: ['users'] })` is now async by default — multiple partial reloads can run concurrently without canceling each other.
- **`useRemember`** (renamed from `remember`): Persist component state across navigations:
  ```tsx
  import { useRemember } from "@inertiajs/react"; // ❌ was: import { remember }
  const [filters, setFilters] = useRemember({ status: "all" }, "claim-filters");
  ```

### Page Props & State

- Use `usePage()` hook from `@inertiajs/react` to access shared props — avoid prop drilling page-level data.
- Use `useForm()` from `@inertiajs/react` for ALL forms — never raw `fetch()` or `axios` directly in components.
- Access `usePage().props.auth.user` for authenticated user — do not pass auth as component props.

### React 19 Specifics

- Use `useTransition()` for non-urgent state updates (filters, sorting, pagination) — prevents UI blocking.
- `useSuspenseQuery` from TanStack Query is preferred over manual `useEffect` + `useState` for async data.
- Server Components are NOT available in Inertia architecture — Inertia handles SSR via `createInertiaApp` with `resolve`.
- Never use `document.getElementById` or direct DOM manipulation — use React refs (`useRef`) when DOM access is required.

### Forms

- `useForm()` from `@inertiajs/react` handles loading state, errors, and CSRF automatically.
- For complex async mutations (non-page-navigation), combine `useForm()` with `useMutation` from TanStack Query.
- Never use HTML `<form action="">` with page reload — always intercept with Inertia's form helpers.

---

## 9. TypeScript — Strict Mode (Mandatory)

> ⛔ Zero `.js` or `.jsx` files in the frontend. Every file is `.ts` or `.tsx`. No exceptions.

### tsconfig.json — Required Flags

```json
{
  "compilerOptions": {
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "exactOptionalPropertyTypes": true
  }
}
```

- `strict: true` enables: `strictNullChecks`, `strictFunctionTypes`, `strictPropertyInitialization`, `noImplicitAny`, `noImplicitThis`, and more.
- `noUncheckedIndexedAccess`: array index access returns `T | undefined` — forces null checks.
- `exactOptionalPropertyTypes`: `{ prop?: string }` means the key may be absent — not `string | undefined`.

### Naming Conventions

- **`interface`** for all domain shapes, props, and API response contracts — never `type` for objects.
- **`type`** only for unions, intersections, aliases, and utility types:

  ```ts
  // ✅ interface for objects
  interface Claim {
    id: string;
    status: ClaimStatus;
  }

  // ✅ type for unions / aliases
  type ClaimStatus = "pending" | "approved" | "rejected";
  type ClaimId = string;
  ```

- Never use `type MyProps = { ... }` for React component props — use `interface MyProps { ... }`.

### Banned Patterns

- **`any` is forbidden** — use `unknown` + type guards for truly unknown data:

  ```ts
  // ❌
  function parse(data: any) {
    return data.id;
  }

  // ✅
  function parse(data: unknown): string {
    if (typeof data === "object" && data !== null && "id" in data) {
      return String((data as { id: unknown }).id);
    }
    throw new Error("Invalid data shape");
  }
  ```

- **No type assertions (`as SomeType`)** unless you own the type boundary (e.g., parsing API responses). Always prefer type guards.
- **No `@ts-ignore`** — use `@ts-expect-error` with a comment explaining why, as a last resort.
- **No non-null assertions (`!`)** unless you can guarantee non-null with a preceding check.

### Inertia Page Props Typing

Always declare typed page props — never use the default `PageProps` with `any`:

```ts
// resources/js/types/inertia.d.ts
import { PageProps as InertiaPageProps } from "@inertiajs/core";

interface AuthUser {
  id: string;
  name: string;
  roles: string[];
}

declare module "@inertiajs/core" {
  interface PageProps extends InertiaPageProps {
    auth: { user: AuthUser };
    flash: { success?: string; error?: string };
  }
}
```

### API Response Typing

All API responses consumed by TanStack Query must be typed with interfaces — never inferred from `fetch`:

```ts
interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    lastPage: number;
    total: number;
    perPage: number;
  };
}

interface ClaimListItem {
  id: string;
  claimNumber: string;
  status: ClaimStatus;
  createdAt: string; // ISO 8601
}

// Usage
useQuery<PaginatedResponse<ClaimListItem>, Error>({
  queryKey: ["claims", "list", filters],
  queryFn: () => fetchClaims(filters),
});
```

### React Component Typing

```tsx
// ✅ Always type props with interface, never inline anonymous type
interface ClaimRowProps {
  claim: ClaimListItem;
  onSelect: (id: string) => void;
}

export function ClaimRow({
  claim,
  onSelect,
}: ClaimRowProps): React.JSX.Element {
  return <tr onClick={() => onSelect(claim.id)}>...</tr>;
}
```

- Always declare explicit return type `React.JSX.Element` or `React.ReactNode` on components.
- Use `React.FC` sparingly — prefer explicit function declarations with typed props.
- `children` must be explicitly typed as `React.ReactNode` — never assume it exists.

### Enums vs Union Types

- Prefer **union types** over TypeScript `enum` for domain status values — enums generate runtime code:

  ```ts
  // ✅ Union type — zero runtime cost
  type ClaimStatus = 'pending' | 'under_review' | 'approved' | 'rejected' | 'closed';

  // ❌ Avoid TS enum — generates runtime object
  enum ClaimStatus { Pending = 'pending', ... }
  ```

### File Organization

- `resources/js/types/` — all global interfaces and type declarations.
- `resources/js/types/inertia.d.ts` — Inertia page props augmentation.
- `resources/js/types/api.ts` — API response interfaces per context.
- Co-locate component-specific types in the same `.tsx` file when used only there.

---

## 10. TanStack Query v5 (Server State Management)

> Package: `@tanstack/react-query@^5` + `@tanstack/react-query-devtools`

### Setup

- Create `QueryClient` at the **module level** — never inside a component (resets cache on every render).
- Configure global defaults:
  ```tsx
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 1000 * 60 * 5, // 5 minutes
        retry: 1,
        throwOnError: false,
      },
    },
  });
  ```
- Wrap the Inertia app with `<QueryClientProvider client={queryClient}>`.
- Include `<ReactQueryDevtools />` in development only.

### Query Rules

- **Query Keys**: Always use descriptive, hierarchical arrays — `['claims', claimId]`, `['claims', 'list', filters]`.
- Use `useQuery` for reads, `useMutation` for writes. Never use `useQuery` to trigger side effects.
- **`throwOnError`**: Renamed from `useErrorBoundary` in v5 — use `throwOnError: true` with React Error Boundaries.
- **Single object signature only**: v5 removed overloads — always use object form:

  ```tsx
  // ✅ v5 correct
  useQuery({ queryKey: ["claims"], queryFn: fetchClaims });

  // ❌ v4 positional args — removed in v5
  useQuery(["claims"], fetchClaims);
  ```

- **`isPending` not `isLoading`**: The `loading` status was renamed to `pending` in v5. Use `isPending` and `isFetching` — `isLoading` now means `isPending && isFetching`. `isInitialLoading` is **deprecated**.

  ```tsx
  // ✅ v5 correct
  const { data, isPending, isFetching } = useQuery({ ... });

  // ❌ v4 — isLoading is deprecated behavior in v5
  const { data, isLoading } = useQuery({ ... });
  ```

- **No `onSuccess`/`onError`/`onSettled` on `useQuery`**: These callbacks were **removed** from `useQuery` and `QueryObserver` in v5. Handle side effects with `useEffect`:

  ```tsx
  // ✅ v5 correct
  const { error } = useQuery({ queryKey: ["claims"], queryFn: fetchClaims });
  useEffect(() => {
    if (error) toast.error(error.message);
  }, [error]);

  // ❌ removed in v5
  useQuery({
    queryKey: ["claims"],
    queryFn: fetchClaims,
    onError: (e) => toast.error(e.message),
  });
  ```

  Note: `onSuccess`, `onError`, `onSettled` still exist on `useMutation` — only removed from `useQuery`.

- **`keepPreviousData` removed**: Use `placeholderData: keepPreviousData` instead:
  ```tsx
  import { keepPreviousData } from "@tanstack/react-query";
  useQuery({
    queryKey: ["claims", page],
    queryFn: fetchPage,
    placeholderData: keepPreviousData,
  });
  ```
- **`Hydrate` renamed to `HydrationBoundary`**:
  ```tsx
  // ✅ v5
  import { HydrationBoundary } from "@tanstack/react-query";
  // ❌ removed
  import { Hydrate } from "@tanstack/react-query";
  ```
- Use `useSuspenseQuery` when working with React 19 Suspense boundaries for cleaner loading states.
- **Optimistic Updates**: Use the `variables` returned from `useMutation` + `onMutate`/`onError`/`onSettled` pattern.
- **Invalidation after mutation**: Always call `queryClient.invalidateQueries({ queryKey: ['resource'] })` in `onSuccess` or `onSettled` of `useMutation`.
- **Parallel queries**: Use `useQueries` with the `combine` option to merge results into a single value.
- **Infinite queries**: Use `maxPages` option to cap cached pages and prevent memory bloat. Always declare `initialPageParam` explicitly (default `undefined` was removed).

### Integration with Inertia

- TanStack Query manages **server state** (API data, async fetches).
- Inertia props (`usePage().props`) manage **page state** (initial SSR data, flash messages, auth).
- Do not duplicate Inertia shared props into TanStack Query cache — use one or the other per data type.

---

## 11. TanStack Table v8 (Data Grids)

> Package: `@tanstack/react-table@^8`

### Core Principles

- TanStack Table is **headless** — it provides zero styles. All rendering is your responsibility following `@rules-styles.md` tokens.
- Use `useReactTable` hook (renamed from `useTable` in v7).
- Use `flexRender()` for all cell and header rendering — never `cell.render('Cell')`.

### Column Definitions

```tsx
// ✅ Correct v8 column definition
const columns: ColumnDef<Claim>[] = [
  {
    accessorKey: "claimNumber", // string accessor
    header: "Claim #",
    cell: ({ getValue }) => getValue<string>(),
  },
  {
    accessorFn: (row) => row.homeowner.name, // function accessor
    id: "homeownerName",
    header: "Homeowner",
  },
];
```

### Table Features

- **Sorting**: Enable with `enableSorting: true` on column or table level. Use `getSortedRowModel()`.
- **Filtering**: Column filters use `filterFn` returning `boolean`. Use `getFilteredRowModel()`.
- **Pagination**: Use `getPaginationRowModel()`. Expose `table.setPageIndex()` and `table.setPageSize()` to UI controls.
- **Column Resizing**: Enable with `enableColumnResizing: true` + `columnResizeMode: 'onChange'`.
- **Row Selection**: Use `enableRowSelection: true` + `onRowSelectionChange` for checkbox tables.
- **Virtualization**: For tables with 500+ rows, combine with `@tanstack/react-virtual` to render only visible rows.

### Integration with TanStack Query

- Fetch paginated data with `useQuery` — pass server-side `pageIndex`, `pageSize`, `sorting`, and `filters` as query key parameters so each unique state triggers a fresh fetch.
- For server-side operations, set `manualPagination: true`, `manualSorting: true`, `manualFiltering: true` on the table and drive state from query params.

### Performance Rules

- Memoize column definitions with `useMemo` — avoid redefining columns on every render.
- Memoize data arrays passed to `useReactTable` — use `useMemo` or keep them stable with TanStack Query's cached data.
- Use `useTransition` (React 19) when updating table filters to keep the UI responsive.

---

## 12. React + Inertia Component Architecture

> ⛔ **MANDATORY**: Before creating or modifying ANY React component, page, layout, or hook, you MUST read `ARQUITECTURE-REACT-INERTIA.md` and follow its structure exactly.

### When This Rule Applies

This rule triggers for ALL of the following:

- Creating a new Inertia **Page** component (`resources/js/Pages/**/*.tsx`)
- Creating a new reusable **UI component** (`resources/js/Components/**/*.tsx`)
- Creating a new **Layout** (`resources/js/Layouts/**/*.tsx`)
- Creating a **custom hook** (`resources/js/hooks/**/*.ts`)
- Creating a **query/mutation hook** (`resources/js/queries/**/*.ts`)
- Creating a **type definition** (`resources/js/types/**/*.ts`)
- Creating a **utility or helper** (`resources/js/utils/**/*.ts`)

### Workflow for Every Component Task

```
1. READ   → `ARQUITECTURE-REACT-INERTIA.md` (before writing any code)
2. LOCATE → Identify the correct directory for the file type
3. FOLLOW → Apply the naming conventions, file structure, and patterns defined there
4. VERIFY → Cross-check against this rules file for TS, TanStack, and Inertia compliance
```

### Non-Negotiable File Placement

Never place files outside the structure defined in `ARQUITECTURE-REACT-INERTIA.md`. If the correct location is ambiguous, ask before creating the file.

### Component Checklist (per file)

Before delivering any React component, verify:

- [ ] File is in the correct directory per `ARQUITECTURE-REACT-INERTIA.md`
- [ ] File extension is `.tsx` (components/pages) or `.ts` (hooks/utils/types)
- [ ] Props typed with `interface`, not inline anonymous type or `type`
- [ ] Explicit return type declared (`React.JSX.Element` or `React.ReactNode`)
- [ ] No hardcoded colors — follows `@rules-styles.md` tokens
- [ ] No `any` — all external data typed via interfaces in `types/api.ts`
- [ ] TanStack Query used for server state, Inertia props for page state
- [ ] Columns and data arrays memoized if using TanStack Table
- [ ] `useTransition` used for non-urgent filter/sort/pagination state updates
- [ ] If it is an index/list page: `DataTableDateRangeFilter` is included, `dateFrom`/`dateTo` are part of the TanStack Query key, and client-side validation enforces `dateFrom` ≤ `dateTo`
- [ ] If it is an index/list page: `ExportButton` (from `common/export/`) is included and forwards current active filters — including date range — to both Excel and PDF export endpoints

---

## 13. Code Review & PR Generation Checklist

Before generating responses or analyzing code, verify:

- [ ] `declare(strict_types=1)` + PHP 8.5 features (`readonly`, `clone with`, `|>`, `#[\NoDiscard]`, `#[\Override]`, `array_first/last`, URI extension)
- [ ] Arrow functions in `|>` chains are wrapped in parentheses
- [ ] Layer Integrity: Domain dictates, Infrastructure implements. Ports in Domain, Adapters in Infrastructure.
- [ ] Entity has `deleted_at` + `softDelete()` method. No hard deletes.
- [ ] Public forms have Honeypot + Rate Limiting.
- [ ] Passwords/Tokens follow OWASP standards.
- [ ] OpenTelemetry context propagated on all operations.
- [ ] Inertia: `router.*` used (not deprecated `Inertia.*`), `useForm()` for forms, `usePage()` for shared props.
- [ ] TanStack Query: object signature only, typed generics `useQuery<T, Error>`, correct query keys, invalidation on mutations.
- [ ] TanStack Table: `flexRender()`, `ColumnDef<T>` typed, `accessorKey`/`accessorFn`, memoized columns and data.
- [ ] TypeScript: zero `any`, zero `@ts-ignore`, no `.js`/`.jsx` files, `interface` for objects, union types for enums, explicit component return types.
- [ ] Inertia page props augmented in `types/inertia.d.ts` — no untyped `usePage().props`.
- [ ] API responses typed with interfaces in `types/api.ts` — no inferred `fetch` returns.
- [ ] Frontend designs follow `@rules-styles.md` token system — no hardcoded hex colors.

---

## 14. User Commands & Workflow Triggers

- **"módulo trabajando correctamente" / "module working successfully"**: Automatically:
  1. Write comprehensive **PEST tests** for that module (happy paths + edge cases + boundary conditions). Cover: authentication/authorization, validation failures, successful CRUD, soft delete, export endpoints, and date range filtering.
  2. **Generate a Postman / Insomnia JSON collection** (Postman Collection v2.1 format) for every endpoint in the module with:
     - Variable `{{base_url}}` (default `http://localhost`) and `{{token}}` for Bearer auth
     - Sample request bodies matching the module's DTOs exactly — include both **valid** and **invalid** payloads (e.g. missing required field, wrong type, `dateFrom` > `dateTo`)
     - Query param examples for paginated list: `page`, `perPage`, `search`, `status`, `dateFrom` (ISO 8601), `dateTo` (ISO 8601), `sortBy`, `sortDir`
     - Expected HTTP status codes and response shape in the request description
     - Grouped folders: **List**, **Show**, **Create**, **Update**, **Delete**, **Export Excel**, **Export PDF**
  3. Do not wait for a separate prompt. Both **PEST tests AND Postman collection** are delivered in one response.
  4. Only after providing both artifacts state: `✅ Backend ready — import the collection into Postman/Insomnia and verify all endpoints before proceeding to frontend.`

- **"backend está sólido" / "backend is solid"**: When the user confirms the backend module is stable and tested, automatically generate the full UI component(s) for that module following this strict workflow:
  1. READ `ARQUITECTURE-REACT-INERTIA.md` to determine correct file placement.
  2. READ `@rules-styles.md` to apply the correct design tokens.
  3. Generate the Inertia Page, reusable components, TanStack Query hooks, and TypeScript interfaces derived directly from the backend's DTOs and query responses.
  4. **MANDATORY — Date Range Filter on every index page**: Every list/index page MUST include a `DataTableDateRangeFilter` component (from `common/data-table/`) wired to `dateFrom`/`dateTo` in the TanStack Query key. Validate client-side: `dateFrom` ≤ `dateTo` before firing the query. Both fields are optional — when empty, no date filter applies.
  5. **MANDATORY — Export Buttons on every index page**: Every list/index page MUST include an `ExportButton` component (from `common/export/`) with two options: **Export Excel** and **Export PDF**. Both pass current active filters (including date range) to the respective export endpoint.
  6. All generated types MUST match the actual backend DTO field names exactly — no speculative typing.
  7. Do not wait for a separate prompt. Generate everything in one response.

- **"dame commit git" / "give me git commit"**: Provide a `git add` + `git commit` block strictly isolated to that module's files. Use Conventional Commits (`feat(claims):`, `fix(billing):`, `test(inspection):`).
- **"investigate" / "investiga"**: Use the **Tavily web search tool** to gather up-to-date documentation before responding.
- **MCP GitHub**: For PR creation, branch context, and issue resolution — always reference the current branch and open PRs before suggesting changes to existing files.

---

## 15. Eloquent ORM — Performance & Optimization Rules

> These rules apply to **every** QueryHandler, Repository method, and ReadModel across all Contexts. N+1 is a hard failure — never merge code that triggers it.

### Eager Loading — Mandatory on All Relations

- Always use `with()`, `withCount()`, or `withSum()` when the returned model renders related data in the UI.
- Any repository method returning a collection MUST declare its eager loads explicitly. Never rely on lazy loading in HTTP-context queries.
- Use Laravel **Telescope** or **Debugbar** in the `local` environment to audit query counts before merging.

  ```php
  // ✅ Correct — explicit eager loading
  ClaimEloquentModel::query()
      ->with(['homeowner:id,name,email', 'contractor:id,name'])
      ->withCount('documents')
      ->select(['id', 'claim_number', 'status', 'homeowner_id', 'contractor_id', 'created_at'])
      ->whereNull('deleted_at')
      ->get();

  // ❌ Wrong — lazy loading fires N+1
  $claims = ClaimEloquentModel::all();
  foreach ($claims as $claim) {
      echo $claim->homeowner->name; // query per row
  }
  ```

### Column Selection — Never SELECT \*

- Always call `->select([...columns])` in list/index read queries. Never load columns that the ReadModel or DTO does not use.
- **List query** → load only the fields rendered in the table (IDs, display names, status, dates).
- **Detail query** → may load all columns needed for the show page.

  ```php
  // ✅ List — minimal projection
  ->select(['id', 'claim_number', 'status', 'estimated_amount', 'created_at', 'homeowner_id'])

  // ✅ Relation constraint — only load needed columns from relation
  ->with(['homeowner:id,name'])
  ```

### Query Scopes — Standard Filter Mechanism

- Encapsulate all reusable filter logic as **local query scopes** on the Eloquent Model, not inside repositories.
- Date range filters MUST be implemented as a `scopeInDateRange` scope and reused across list + export queries.
  ```php
  // On the Eloquent Model
  public function scopeInDateRange(
      Builder $query,
      ?string $from,
      ?string $to,
      string  $column = 'created_at',
  ): Builder {
      return $query
          ->when($from, fn(Builder $q) => $q->whereDate($column, '>=', $from))
          ->when($to,   fn(Builder $q) => $q->whereDate($column, '<=', $to));
  }
  ```

### Conditional Chaining with `->when()`

- Use `->when($condition, $callback)` for every optional filter — never build conditional query strings or duplicate base queries.
  ```php
  // ✅ Correct — PHP 8.5 modern Eloquent chain
  $query = ClaimEloquentModel::query()
      ->select(['id', 'claim_number', 'status', 'created_at', 'homeowner_id'])
      ->with(['homeowner:id,name'])
      ->whereNull('deleted_at')
      ->when($filters->status,   fn($q) => $q->where('status', $filters->status))
      ->when($filters->search,   fn($q) => $q->where('claim_number', 'like', "%{$filters->search}%"))
      ->when($filters->dateFrom || $filters->dateTo,
             fn($q) => $q->inDateRange($filters->dateFrom, $filters->dateTo))
      ->orderBy($filters->sortBy ?? 'created_at', $filters->sortDir ?? 'desc');
  ```

### Pagination Strategy

- Use `->paginate($perPage)` for standard index pages with total count.
- Use `->cursorPaginate($perPage)` for infinite-scroll UIs or tables with 100k+ rows (no `COUNT(*)` overhead).
- **Never** call `->get()` on unbounded collections in HTTP-context handlers.

### Bulk & Export Operations — Memory Safety

- Use `->chunk(500, $callback)` or `->chunkById(500, $callback)` for queue jobs processing large datasets.
- Use `->cursor()` for streaming to Excel/PDF exports — O(1) memory regardless of row count.
  ```php
  // ✅ Memory-safe export via cursor
  ClaimEloquentModel::query()
      ->select(['id', 'claim_number', 'status', 'estimated_amount', 'created_at'])
      ->with(['homeowner:id,name'])
      ->whereNull('deleted_at')
      ->inDateRange($filters->dateFrom, $filters->dateTo)
      ->orderBy('created_at', 'desc')
      ->cursor(); // LazyCollection — feeds row-by-row into Excel/PDF writer
  ```

### Code Review Checklist — Eloquent

- [ ] No `->get()` on unbounded collection in any HTTP handler
- [ ] All relations accessed in loops/templates declared in `with()`
- [ ] `select()` called on every list query
- [ ] Optional filters use `->when()` — no raw conditional chaining
- [ ] Export methods use `->cursor()` or `->chunkById()`
- [ ] Date range filters use `scopeInDateRange()` — no duplicate date logic

---

## 16. Export Architecture — Excel (maatwebsite/excel) + PDF (barryvdh/laravel-dompdf)

> Package versions: `maatwebsite/excel ^3.1` · `barryvdh/laravel-dompdf ^3.1`

### Every CRUD Module MUST Expose Export Endpoints

- Route pattern: `GET /api/{context}/{module}/export?format=excel|pdf&dateFrom=&dateTo=&...filters`
- The **same `FilterDTO`** used for list queries is reused for export — zero duplication of filter logic.
- Exports are **streamed** (not stored on disk) for files under 50 MB. For larger exports use a queued job that stores the file in S3 and notifies the user via broadcast.
- Export endpoints are **protected**: require the same auth + permissions as the list endpoint.

### Export Controller Pattern

```php
// Infrastructure/Adapters/Http/Controllers/Api/{Module}ExportController.php
declare(strict_types=1);

namespace Contexts\{Context}\Infrastructure\Adapters\Http\Controllers\Api;

use Contexts\{Context}\Application\DTOs\{Module}FilterDTO;
use Contexts\{Context}\Infrastructure\Adapters\Http\Export\{Module}ExcelExport;
use Contexts\{Context}\Infrastructure\Adapters\Http\Export\{Module}PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

final class {Module}ExportController
{
    public function __invoke(Request $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = {Module}FilterDTO::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'excel' => Excel::download(
                new {Module}ExcelExport($filters),
                '{module}-export-' . now()->format('Y-m-d') . '.xlsx',
            ),
            'pdf'   => app({Module}PdfExport::class, ['filters' => $filters])->stream(),
            default => response()->json(['error' => 'Invalid format'], 422),
        };
    }
}
```

### Excel Export Class — maatwebsite/excel v3 Interfaces

Use only these concerns. Combine them as needed per module:

| Concern Interface | Purpose                                                                 |
| ----------------- | ----------------------------------------------------------------------- |
| `FromQuery`       | Provides an Eloquent `Builder` — package handles chunking automatically |
| `WithHeadings`    | First row = column headers                                              |
| `WithMapping`     | Transform each model row → flat array                                   |
| `ShouldAutoSize`  | Auto-fit column widths                                                  |
| `WithTitle`       | Sheet tab name                                                          |
| `WithStyles`      | Cell styling (bold headers, etc.)                                       |
| `ShouldQueue`     | Queue the export — required for large datasets                          |

```php
// Infrastructure/Adapters/Http/Export/{Module}ExcelExport.php
declare(strict_types=1);

namespace Contexts\{Context}\Infrastructure\Adapters\Http\Export;

use Contexts\{Context}\Application\DTOs\{Module}FilterDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class {Module}ExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    public function __construct(
        private readonly {Module}FilterDTO $filters,
    ) {}

    public function query(): Builder
    {
        // ✅ Uses scopeInDateRange + ->when() pattern from Section 15
        return {Module}EloquentModel::query()
            ->select(['id', '...columns...'])
            ->with(['relation:id,name'])
            ->whereNull('deleted_at')
            ->when($this->filters->status,   fn($q) => $q->where('status', $this->filters->status))
            ->when($this->filters->dateFrom || $this->filters->dateTo,
                   fn($q) => $q->inDateRange($this->filters->dateFrom, $this->filters->dateTo))
            ->orderBy('created_at', 'desc');
        // Note: maatwebsite/excel calls ->cursor() internally on FromQuery — memory-safe
    }

    public function headings(): array
    {
        return ['ID', 'Field 1', 'Field 2', 'Status', 'Created At'];
    }

    /** @param {Module}EloquentModel $row */
    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->field_1,
            $row->field_2,
            $row->status->value,           // PHP 8.5 Backed Enum
            $row->created_at->format('Y-m-d H:i'),
        ];
    }

    public function title(): string
    {
        return ucfirst('{module}') . ' Export';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
```

### PDF Export Class — barryvdh/laravel-dompdf v3

```php
// Infrastructure/Adapters/Http/Export/{Module}PdfExport.php
declare(strict_types=1);

namespace Contexts\{Context}\Infrastructure\Adapters\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Contexts\{Context}\Application\DTOs\{Module}FilterDTO;
use Contexts\{Context}\Domain\Ports\{Module}RepositoryPort;
use Illuminate\Http\Response;

final class {Module}PdfExport
{
    public function __construct(
        private readonly {Module}RepositoryPort $repository,
        private readonly {Module}FilterDTO       $filters,
    ) {}

    public function stream(): Response
    {
        // ✅ cursor() for memory-safe PDF generation (Section 15)
        $rows = $this->repository
            ->queryForExport($this->filters)
            ->cursor();

        $pdf = Pdf::loadView("pdf.{context}.{module}-export", [
            'rows'        => $rows,
            'filters'     => $this->filters,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        // v3 note: enable_remote is false by default — use base64 for images
        return $pdf->stream('{module}-export-' . now()->format('Y-m-d') . '.pdf');
    }
}
```

### PDF Blade Template Rules

- **Location**: `resources/views/pdf/{context}/{module}-export.blade.php`
- Pure HTML + **inline CSS only** (dompdf does not process external CSS files or JavaScript).
- Must include: company logo (base64-encoded `src`), report title, filter summary row, generation timestamp, and page numbers via `@page` CSS rule.
- Use partials: `@include('pdf.partials.header', ['title' => '...'])` and `@include('pdf.partials.footer')`.
- Color values in PDF templates use **hardcoded hex** — CSS custom properties (`var(--token)`) are a browser feature unavailable in dompdf.

```blade
{{-- resources/views/pdf/{context}/{module}-export.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 20mm 15mm; size: A4 landscape; }
  body   { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }
  table  { width: 100%; border-collapse: collapse; }
  th     { background: #6366f1; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
  td     { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
  tr:nth-child(even) td { background: #f8f8fc; }
  .header { display: flex; justify-content: space-between; margin-bottom: 12px; }
  .filter-summary { background: #f1f1f6; border: 1px solid #e2e8f0; padding: 6px 10px;
                    border-radius: 4px; margin-bottom: 10px; font-size: 9px; color: #6a6a82; }
</style>
</head>
<body>
@include('pdf.partials.header', ['title' => '{Module} Export Report'])

<div class="filter-summary">
  <strong>Filters applied:</strong>
  @if($filters->dateFrom) From: {{ $filters->dateFrom }} @endif
  @if($filters->dateTo)   To: {{ $filters->dateTo }} @endif
  @if($filters->status)   Status: {{ $filters->status }} @endif
  &nbsp;|&nbsp; Generated: {{ $generatedAt }}
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Field 1</th>
      <th>Status</th>
      <th>Created At</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $row)
    <tr>
      <td>{{ $row->id }}</td>
      <td>{{ $row->field_1 }}</td>
      <td>{{ $row->status->value }}</td>
      <td>{{ $row->created_at->format('Y-m-d') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@include('pdf.partials.footer')
</body>
</html>
```

### Frontend ExportButton Contract

```tsx
// common/export/ExportButton.tsx
interface ExportButtonProps {
  endpoint: string; // e.g. '/api/claims/export'
  filters: Record<string, string | number | boolean | undefined>; // current active filters
  formats?: ReadonlyArray<"excel" | "pdf">; // defaults to both
  disabled?: boolean;
  className?: string;
}
// Behavior:
// - Renders a dropdown with "Export Excel" and "Export PDF" options
// - On click: appends format + serialized filters to the endpoint as query params
// - Triggers file download via window.location or <a download> technique
// - Tracks isPending per format separately — shows spinner in the active option
// - On error: shows toast notification via the app's notification system
// - Never disabled when table has data, even if row count is unknown
```

### Package Publish Commands (run once during setup)

```bash
# Excel
./vendor/bin/sail artisan vendor:publish \
  --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# DomPDF
./vendor/bin/sail artisan vendor:publish \
  --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

## 17. Audit & Activity Log — spatie/laravel-activitylog

> Package: `spatie/laravel-activitylog ^4.0` · Architecture mapping: `Core/Shared/Infrastructure/Audit/SpatieActivityLogAdapter.php`

### Two Mechanisms — Both Are Required

Activity logging works at two levels. **Both must be used** — they are complementary, not alternatives.

| Level                                                                | Mechanism                              | Trigger                   |
| -------------------------------------------------------------------- | -------------------------------------- | ------------------------- |
| Model lifecycle (`created`, `updated`, `deleted`)                    | `LogsActivity` trait on EloquentModel  | Automatic once configured |
| Business actions (`suspend`, `approve`, `revoke`, `login`, `export`) | `AuditPort` injected in CommandHandler | Manual — always explicit  |

---

### Level 1 — Automatic: `LogsActivity` Trait on Every EloquentModel

Every `*EloquentModel.php` that represents a domain entity **MUST** include the `LogsActivity` trait and define `getActivitylogOptions()`. This captures CRUD at the persistence layer automatically.

```php
// Infrastructure/Persistence/Eloquent/Models/{Module}EloquentModel.php
declare(strict_types=1);

namespace Contexts\{Context}\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** @internal */
final class {Module}EloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field_1', 'field_2', 'status'])  // ← never log sensitive fields (passwords, tokens)
            ->logOnlyDirty()                              // ← only changed attributes, not the full model
            ->dontSubmitEmptyLogs()                       // ← skip if nothing actually changed
            ->useLogName('{context}.{module}');           // ← namespaced log for easy filtering
    }
}
```

**Rules for `getActivitylogOptions()`:**

- `logOnly([...])` — **always explicit**. Never use `logAll()` in production — it leaks sensitive data.
- Never include: `password`, `remember_token`, `two_factor_secret`, `two_factor_recovery_codes`, API tokens, or any hashed credential.
- `logOnlyDirty()` — mandatory. Logging unchanged values is noise.
- `dontSubmitEmptyLogs()` — mandatory. Prevents empty audit rows on touch-only updates.
- `useLogName('{context}.{module}')` — mandatory naming convention. Enables filtering by context in the audit UI.

---

### Level 2 — Manual: `AuditPort` in CommandHandlers

Any business action beyond basic CRUD **MUST** be logged manually via `AuditPort`. The trait does not know about business intent — only about column changes. `AuditPort` captures **why** something happened.

**Mandatory audit triggers:**

- Auth: `login`, `logout`, `password_changed`, `password_reset`, `otp_verified`, `2fa_enabled`, `2fa_disabled`, `token_revoked`
- User management: `user_suspended`, `user_activated`, `user_created_by_admin`, `admin_password_reset`
- Any state transition with business significance: `approved`, `rejected`, `published`, `archived`
- Permission changes: `role_assigned`, `role_removed`, `permission_granted`
- Exports: `export_excel`, `export_pdf` — log who exported what with which filters

```php
// Application/Commands/SuspendUser/SuspendUserHandler.php
declare(strict_types=1);

namespace Contexts\{Context}\Application\Commands\SuspendUser;

use Contexts\{Context}\Domain\Ports\{Module}RepositoryPort;
use Core\Shared\Domain\Ports\AuditPort;

final class SuspendUserHandler
{
    public function __construct(
        private readonly {Module}RepositoryPort $repository,
        private readonly AuditPort              $audit,
    ) {}

    public function handle(SuspendUserCommand $command): void
    {
        $user = $this->repository->findById($command->userId);
        $user->suspend();
        $this->repository->save($user);

        // ✅ Explicit audit — who did it, to what, why
        $this->audit->log(
            causer:      $command->actingUserId,     // who performed the action
            subject:     $command->userId,            // who/what was affected
            description: 'user.suspended',            // dot-notation event name
            properties:  ['reason' => $command->reason],
        );
    }
}
```

**`AuditPort::log()` parameter contract:**

- `causer` — always the authenticated user's domain ID (`UserId`). Never `null` on authenticated actions.
- `subject` — the entity affected (its domain ID). Can differ from causer (admin acting on another user).
- `description` — dot-notation string: `'{context}.{action}'` (e.g. `'auth.login'`, `'users.suspended'`). Always lowercase, always namespaced.
- `properties` — contextual array. Include only data relevant to investigating the event. Never include passwords, tokens, or full model dumps.

---

### AuditPort Interface — for reference

```php
// Core/Shared/Domain/Ports/AuditPort.php
declare(strict_types=1);

namespace Core\Shared\Domain\Ports;

interface AuditPort
{
    public function log(
        string $causer,
        string $subject,
        string $description,
        array  $properties = [],
    ): void;
}
```

The binding `AuditPort::class → SpatieActivityLogAdapter::class` is registered in `Core\Providers\AuditServiceProvider`.

---

### What NOT to Log

- Never log inside **QueryHandlers** — reads are not auditable events.
- Never log inside **Eloquent Observers** directly — use the trait or the Port, not both for the same event.
- Never log raw exceptions — that belongs to OpenTelemetry structured logging (Section 7), not the audit trail.
- Never log the full `$request->all()` as properties — sanitize first.

---

### Code Review Checklist — Audit

- [ ] Every `*EloquentModel.php` has `LogsActivity` trait + `getActivitylogOptions()`
- [ ] `logOnly([...])` is explicit — `logAll()` is absent
- [ ] Sensitive fields (`password`, tokens, secrets) are excluded from `logOnly()`
- [ ] `logOnlyDirty()` and `dontSubmitEmptyLogs()` are set
- [ ] `useLogName('{context}.{module}')` follows the naming convention
- [ ] Every CommandHandler with a business state transition injects `AuditPort` and calls `$this->audit->log()`
- [ ] `description` uses dot-notation namespaced format
- [ ] Auth events (`login`, `logout`, `password_changed`) are logged in their respective CommandHandlers
- [ ] Export actions (`export_excel`, `export_pdf`) are logged with filter properties
- [ ] No audit calls inside QueryHandlers

---

_Follow these rules as a strict protocol. Use "investigate" via Tavily or MCP Context searches anytime external API documentation, PHP 8.5 nuances, or UI libraries require current verification._
