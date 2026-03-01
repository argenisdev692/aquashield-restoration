# Master Development Rules — PHP 8.5 & Laravel 12 Enterprise (2026)

## 0. Assistant Behavior

- **Role**: Senior Software Architect in PHP 8.5 and Laravel 12
- **Language**: Always respond in ENGLISH
- **Style**: Concise, technical, pragmatic
- **UI/UX**: Always read and apply `rules-styles.md` for frontend components

---

## 1. Architectural Principles

- **Hexagonal Architecture**: Ports in `Domain/Ports/`, Adapters in `Infrastructure/Adapters/`
- **ServiceProvider Binding**: Auto-bind all Ports to Repositories in `register()`
- **DDD**: Bounded contexts, Aggregates, Entities, immutable Value Objects
- **CQRS**: Separate Commands (write) from Queries (read)
- **Domain Events**: Emit via `DomainEventPublisher`, handle sync/async

---

## 2. PHP 8.5 Features (Mandatory)

- **Pipe Operator (`|>`)**: Use for chained transformations, wrap arrow functions in parentheses
- **Immutability**: `readonly class` + `clone($object, ['property' => $newValue])`
- **Attributes**: `#[\NoDiscard]`, `#[\Override]`
- **Array Functions**: `array_first()`, `array_last()` over `reset()`/`end()`
- **URI Extension**: Use `Uri\Rfc3986\Uri` or `Uri\WhatWg\Url`
- **Strict Types**: `declare(strict_types=1);` in every file
- **Return Types**: Explicit return type on every method
- **Validation**: Use `spatie/laravel-data` DTOs

---

## 3. Database & Soft Deletes

- **Universal Timestamps**: Every entity has `created_at`, `updated_at`, `deleted_at`
- **No Hard Deletes**: `deleted_at` is soft delete marker
- **Default Filtering**: Auto-filter `deleted_at IS NULL` in repositories
- **Domain Method**: Expose `softDelete()` on Entities

---

## 4. Security (OWASP 2026)

- **Honeypot**: All public forms use `spatie/laravel-honeypot`
- **Rate Limiting**: Enforce via `throttle` middleware
- **SQLi Prevention**: Use Eloquent PDO binding only
- **XSS/CSRF**: Blade `{{ }}`, React `{ }`, Inertia auto-handles CSRF
- **Security Headers**: HSTS, CSP, X-Frame-Options via middleware

---

## 5. Authentication & Authorization

- **RBAC + ABAC**: Use `spatie/laravel-permission`
- **Authorization Steps**:
  1. Token verification (Sanctum/Session)
  2. Role check (middleware)
  3. Fine-grained permissions (Policies/Gates)
- Never hardcode permission checks in controllers

---

## 6. Secret Management

- Load all credentials from `.env` only
- Fail at boot if critical env vars missing
- Never commit `.env`

---

## 7. Observability

- **OpenTelemetry**: Instrument all critical flows
- **Traces**: Propagate Span Context in Queue/HTTP/Events
- **Structured Logging**: Include `trace_id` and context
- **Health Checks**: Monitor DB, queue, cache, Reverb

---

## 8. Frontend — Inertia 2.0 + React 19

### Navigation
- Use `<Link>` from `@inertiajs/react` for internal navigation
- Use `router.visit()`, `router.get()`, etc. (not deprecated `Inertia.*`)
- Link prefetching: `<Link prefetch>` or `prefetch="click"`

### Inertia 2.0 Features
- **Deferred Props**: Wrap in `<Suspense>`
- **WhenVisible**: Lazy-load on scroll
- **Polling**: `usePoll(3000)` for auto-refresh
- **useRemember**: Persist state across navigation

### State Management
- `usePage()` for shared props
- `useForm()` for all forms
- `useTransition()` for non-urgent updates
- Never use `document.getElementById`

---

## 9. TypeScript — Strict Mode

### tsconfig.json Required Flags
- `strict: true`
- `noUncheckedIndexedAccess: true`
- `noImplicitReturns: true`
- `noFallthroughCasesInSwitch: true`
- `exactOptionalPropertyTypes: true`

### Naming
- `interface` for objects, props, API contracts
- `type` for unions, intersections, aliases

### Banned Patterns
- No `any` — use `unknown` + type guards
- No type assertions unless at type boundary
- No `@ts-ignore` — use `@ts-expect-error` with comment
- No non-null assertions (`!`) without preceding check

### Typing Rules
- Inertia page props: augment in `types/inertia.d.ts`
- API responses: interfaces in `types/api.ts`
- Component props: always `interface`, explicit return types
- Prefer union types over TS `enum`

---

## 10. TanStack Query v5

### Setup
- Create `QueryClient` at module level
- Wrap app with `<QueryClientProvider>`
- Include `<ReactQueryDevtools />` in dev

### Query Rules
- Query keys: hierarchical arrays `['context', 'operation', ...params]`
- Use `useQuery` for reads, `useMutation` for writes
- `throwOnError` (renamed from `useErrorBoundary`)
- Single object signature only (no positional args)
- `isPending` not `isLoading`
- No `onSuccess`/`onError` on `useQuery` (use `useEffect`)
- `placeholderData: keepPreviousData` for pagination
- `HydrationBoundary` (renamed from `Hydrate`)
- Always invalidate queries after mutation

### Integration with Inertia
- TanStack Query = server state
- Inertia props = page state
- Never duplicate data in both

---

## 11. TanStack Table v8

### Core Principles
- Headless — follow `rules-styles.md` for styling
- Use `useReactTable` hook
- Use `flexRender()` for rendering

### Rules
- Memoize column definitions with `useMemo`
- Memoize data arrays
- Use `useTransition` for filter updates
- Server-side: set `manualPagination`, `manualSorting`, `manualFiltering`

---

## 12. React + Inertia Architecture

### Mandatory Workflow
1. READ `ARQUITECTURE-REACT-INERTIA.md` before creating any component
2. LOCATE correct directory
3. FOLLOW naming conventions
4. VERIFY against this rules file

### Component Checklist
- [ ] Correct directory per `ARQUITECTURE-REACT-INERTIA.md`
- [ ] File extension `.tsx` or `.ts`
- [ ] Props typed with `interface`
- [ ] Explicit return type
- [ ] No hardcoded colors — use `rules-styles.md` tokens
- [ ] Shadcn UI components only
- [ ] No `any`
- [ ] TanStack Query for server state
- [ ] Memoized columns/data for tables
- [ ] `useTransition` for filters
- [ ] `DataTableDateRangeFilter` on index pages
- [ ] `ExportButton` on index pages

---

## 13. Code Review Checklist

- [ ] `declare(strict_types=1)` + PHP 8.5 features
- [ ] Arrow functions in `|>` wrapped in parentheses
- [ ] Ports in Domain, Adapters in Infrastructure
- [ ] Entity has `deleted_at` + `softDelete()`
- [ ] Public forms have Honeypot + Rate Limiting
- [ ] OpenTelemetry context propagated
- [ ] Inertia: `router.*` (not `Inertia.*`)
- [ ] TanStack Query: typed generics, correct keys
- [ ] TypeScript: no `any`, no `.js`/`.jsx`
- [ ] Frontend follows `rules-styles.md` tokens

---

## 14. User Commands

### "module working successfully"
Automatically generate:
1. Comprehensive PEST tests
2. Postman/Insomnia collection
3. State: "✅ Backend ready — import collection and verify"

### "backend is solid"
Automatically generate:
1. Read `ARQUITECTURE-REACT-INERTIA.md`
2. Read `rules-styles.md`
3. Generate Inertia Pages, components, hooks, types
4. Include `DataTableDateRangeFilter` on index pages
5. Include `ExportButton` on index pages
6. Match backend DTO field names exactly

### "give me git commit"
Provide `git add` + `git commit` with Conventional Commits format

---

## 15. Eloquent ORM Performance

- Always use `with()`, `withCount()`, `withSum()` for eager loading
- Never rely on lazy loading
- N+1 queries are hard failures
