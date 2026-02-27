# Dark Developer UI — Implementation Guide

Style based on the aesthetics of tools like VS Code, Linear, Raycast, and Vercel. Minimalist, high contrast, developer-oriented. This document defines the tokens, patterns, components, and rules to reproduce it in any project.

> **Important:** Color values are NOT hardcoded here. All colors are read from the CSS custom properties defined in the project's `globals.css` file (or equivalent: `global.css`, `variables.css`, `theme.css`). Before implementing any component, read that file and use the available tokens. If a token does not exist, add it to the global file before using it in components.

---

## 0. Token-First Principle

Never use hex values directly in components. Always reference CSS variables:

```css
/* ✅ Correct */
background: var(--bg-card);
color: var(--text-muted);
border: 1px solid var(--border-default);

/* ❌ Incorrect */
background: #1a1a2e;
color: #9ca3af;
```

For Tailwind, all tokens from `globals.css` must be mapped in `tailwind.config.js` under `theme.extend`. Always use the resulting utility classes — never arbitrary values like `bg-[#1a1a2e]` unless the token does not yet exist.

---

## 1. Expected Token Structure

The `globals.css` file must expose at least these token groups. The names listed here are what this design system recognizes — if the project uses different names, map them mentally before implementing:

### Backgrounds (stackable layers, darkest to lightest)

```
--bg-app        → Base application background
--bg-surface    → Main containers, sidebars
--bg-card       → Cards, inputs, code blocks
--bg-hover      → Hover state for interactive elements
```

### Borders

```
--border-subtle   → Nearly invisible border, dividers
--border-default  → Most commonly used, cards and containers
--border-hover    → On interaction
```

### Text (most to least prominent)

```
--text-primary    → Main content
--text-secondary  → Subtitles, supporting text
--text-muted      → Descriptions, labels, placeholders
--text-disabled   → Metadata, inactive text
```

### Accent Colors

```
--accent-primary   → Primary accent, CTAs, active elements
--accent-secondary → Secondary accent, callouts
--accent-success   → Success, positive states
--accent-warning   → Warnings, notes
--accent-error     → Errors, alerts
--accent-info      → Information, links
```

### Typography

```
--font-sans  → Narrative text font (Inter or equivalent)
--font-mono  → Code font (JetBrains Mono or equivalent)
```

### Radii and transitions

```
--radius-sm  → Small rounded corners (inline buttons, chips)
--radius-md  → Standard buttons, inputs
--radius-lg  → Cards, modals, code blocks
--transition → Standard duration and easing for hover/active states
```

---

## 2. Theme Architecture — Dark/Light from Day 1

This design system defaults to **dark mode**. However, every enterprise product shipping in 2026 must plan for light mode from the start, even if it ships dark-only at launch. The token structure makes this trivial — all color values live in `:root` and are overridden via `[data-theme="light"]`.

### Default (dark)

```css
:root {
  color-scheme: light dark;

  /* ── backgrounds ── */
  --bg-app: #0a0a1a;
  --bg-surface: #12122a;
  --bg-card: #1a1a3e;
  --bg-hover: #252550;

  /* ── borders ── */
  --border-subtle: rgba(255, 255, 255, 0.06);
  --border-default: rgba(255, 255, 255, 0.1);
  --border-hover: rgba(255, 255, 255, 0.18);

  /* ── text ── */
  --text-primary: #e8e8ed;
  --text-secondary: #b0b0c0;
  --text-muted: #7a7a90;
  --text-disabled: #4a4a5e;

  /* ── accents ── */
  --accent-primary: #6366f1;
  --accent-secondary: #a78bfa;
  --accent-success: #22c55e;
  --accent-warning: #f59e0b;
  --accent-error: #ef4444;
  --accent-info: #38bdf8;

  /* ... remaining tokens (fonts, radii, transition) ... */
}
```

> The values above are reference examples. Each project defines its own palette, but the variable names and layering order must be respected.

### Light mode override

```css
[data-theme="light"] {
  --bg-app: #f8f8fc;
  --bg-surface: #ffffff;
  --bg-card: #f1f1f6;
  --bg-hover: #e8e8f0;

  --border-subtle: rgba(0, 0, 0, 0.05);
  --border-default: rgba(0, 0, 0, 0.1);
  --border-hover: rgba(0, 0, 0, 0.18);

  --text-primary: #1a1a2e;
  --text-secondary: #3a3a52;
  --text-muted: #6a6a82;
  --text-disabled: #9a9ab0;

  --accent-primary: #4f46e5;
  --accent-secondary: #7c3aed;
  --accent-success: #16a34a;
  --accent-warning: #d97706;
  --accent-error: #dc2626;
  --accent-info: #0284c7;
}
```

### System preference fallback

If the user has _not_ manually toggled the theme, respect the OS setting:

```css
@media (prefers-color-scheme: light) {
  :root:not([data-theme]) {
    /* identical to [data-theme="light"] block above */
  }
}
```

### Toggle logic (JavaScript)

The toggle must be stored in `localStorage` so the preference persists. The attribute is set on `<html>` (not `<body>`) for full cascading:

```ts
function setTheme(mode: "light" | "dark" | "system") {
  const root = document.documentElement;
  if (mode === "system") {
    root.removeAttribute("data-theme");
    localStorage.removeItem("theme");
  } else {
    root.setAttribute("data-theme", mode);
    localStorage.setItem("theme", mode);
  }
}

// On page load — apply before first paint (place in <head>)
const saved = localStorage.getItem("theme") as "light" | "dark" | null;
if (saved) document.documentElement.setAttribute("data-theme", saved);
```

### `light-dark()` shorthand (optional, modern browsers)

For individual values that differ minimally between modes, the CSS `light-dark()` function is valid since Chrome 123 / Firefox 120 / Safari 17.5. Use it sparingly alongside the token system:

```css
.decorative-line {
  border-color: light-dark(rgba(0, 0, 0, 0.08), rgba(255, 255, 255, 0.08));
}
```

> **Rule:** `light-dark()` is acceptable only for one-off decorative values. All semantic colors must still use tokens from `:root` / `[data-theme]`.

---

## 3. Accessibility — Focus, Motion & Target Size

Enterprise UIs in 2026 must comply with **WCAG 2.2 AA** at minimum. This section codifies the three most implementation-critical requirements.

### `:focus-visible` (keyboard focus ring)

All interactive elements must show a visible focus indicator when navigated via keyboard. Mouse/touch users do not see the ring.

```css
/* Global reset — remove default outline for pointer, keep for keyboard */
:focus {
  outline: none;
}

:focus-visible {
  outline: 2px solid var(--accent-primary);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}
```

Component-level overrides are allowed when the default ring doesn't fit (e.g., cards that need an inset ring):

```css
.card:focus-visible {
  outline-offset: -2px;
  outline-color: var(--accent-info);
}
```

**Rules:**

- The focus ring must achieve **≥ 3:1 contrast ratio** against the adjacent background (WCAG 2.4.13).
- The focused element must **never be obscured** by sticky headers, modals, or drawers (WCAG 2.4.11).
- Never remove `:focus-visible` styles without providing an equally visible alternative.

### `prefers-reduced-motion`

All CSS transitions and Framer Motion animations must respect the user's motion preference:

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

> For Framer Motion, use the `useSafeTransition` hook documented in Section 10 (Framer Motion).

### Minimum target size — 24 × 24 CSS pixels (WCAG 2.5.8)

All interactive targets (buttons, links, form controls, icons acting as buttons) must have a minimum touch/click area of **24 × 24 CSS px**. Exceptions per WCAG 2.2: inline inline-text links, user-agent-controlled elements, and elements essential to the information conveyed.

```css
/* Utility: ensure minimum tap target on small icon buttons */
.tap-target {
  position: relative;
  min-width: 24px;
  min-height: 24px;
}

.tap-target::after {
  content: "";
  position: absolute;
  inset: -4px; /* enlarge hit area without affecting visual size */
  /* use larger negative values if the visible element < 24px */
}
```

---

## 4. Form Tokens — Inputs, Selects & Textareas

Forms are the **most token-dense surface** in any enterprise application — login, registration, OTP, profile settings, admin CRUD. The base token set in Section 1 covers general UI; the tokens below cover form-specific needs.

### Required form tokens in `globals.css`

```css
:root {
  /* ── Input backgrounds ── */
  --input-bg: var(--bg-card);
  --input-bg-disabled: color-mix(in srgb, var(--bg-card) 50%, var(--bg-app));

  /* ── Input borders ── */
  --input-border: var(--border-default);
  --input-border-hover: var(--border-hover);
  --input-border-focus: var(--accent-primary);
  --input-border-error: var(--accent-error);
  --input-border-success: var(--accent-success);

  /* ── Input text ── */
  --input-text: var(--text-primary);
  --input-placeholder: var(--text-muted);
  --input-label: var(--text-secondary);
  --input-helper: var(--text-muted);
  --input-error-text: var(--accent-error);

  /* ── Input sizing ── */
  --input-height: 40px;
  --input-padding-x: 12px;
  --input-font-size: 14px;
  --input-radius: var(--radius-md);
}
```

> Light mode overrides: these tokens alias base tokens, so they update automatically when `[data-theme="light"]` redefines the base values. If a form token needs a light-specific override, add it inside the `[data-theme="light"]` block.

### Base input component

```css
.input {
  width: 100%;
  height: var(--input-height);
  padding: 0 var(--input-padding-x);
  background: var(--input-bg);
  color: var(--input-text);
  border: 1px solid var(--input-border);
  border-radius: var(--input-radius);
  font-size: var(--input-font-size);
  font-family: var(--font-sans);
  transition: var(--transition);
}

.input::placeholder {
  color: var(--input-placeholder);
}

.input:hover:not(:disabled) {
  border-color: var(--input-border-hover);
}

.input:focus-visible {
  border-color: var(--input-border-focus);
  outline: 2px solid
    color-mix(in srgb, var(--input-border-focus) 25%, transparent);
  outline-offset: 1px;
}

.input--error {
  border-color: var(--input-border-error);
}
.input--error:focus-visible {
  outline-color: color-mix(in srgb, var(--input-border-error) 25%, transparent);
}

.input--success {
  border-color: var(--input-border-success);
}

.input:disabled {
  background: var(--input-bg-disabled);
  color: var(--text-disabled);
  cursor: not-allowed;
  opacity: 0.6;
}
```

### Helper & error text

```css
.input-helper {
  font-size: 12px;
  color: var(--input-helper);
  margin-top: 4px;
}

.input-error-msg {
  font-size: 12px;
  color: var(--input-error-text);
  margin-top: 4px;
}

.input-label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: var(--input-label);
  margin-bottom: 6px;
}
```

### OTP / Code input pattern

For OTP screens (Fortify, 2FA), individual digit inputs share these tokens but get tighter sizing:

```css
.otp-input {
  width: 48px;
  height: 52px;
  text-align: center;
  font-size: 20px;
  font-weight: 700;
  font-family: var(--font-mono);
  letter-spacing: 2px;
  /* inherits all .input tokens via @extend or class chaining */
}
```

---

## 5. Typography

Exactly two font families are used: sans-serif for narrative text and monospace for code. Both must be declared in `globals.css` as `--font-sans` and `--font-mono`.

### Size and weight scale

```
Main heading        22px   weight 800   letter-spacing: -0.5px
Section heading     18px   weight 700
Subheading          14px   weight 600
Body text           14px   weight 400   line-height: 1.8
Small text          12px   weight 500
Label / tag         11px   weight 600   text-transform: uppercase   letter-spacing: 1.5px
```

Monospace is used exclusively for:

- Code blocks
- Inline snippets
- File paths
- Technical variables and values

---

## 6. Core Components

### Cards / Containers

```css
.card {
  background: var(--bg-card);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  padding: 16px;
}
```

Cards have no box shadow. The subtle border is sufficient to define them against the background.

### Buttons

```css
/* Primary */
.btn-primary {
  background: var(--accent-primary);
  color: var(--text-primary);
  border: none;
  border-radius: var(--radius-md);
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}
.btn-primary:hover {
  filter: brightness(0.88);
}

/* Secondary / Ghost */
.btn-ghost {
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}
.btn-ghost:hover {
  background: var(--bg-hover);
  color: var(--text-secondary);
  border-color: var(--border-hover);
}
```

### Badges / Tags

Badges use a very subtle tint of the accent color as background, the accent color as text, and a border with slightly more opacity. The `globals.css` file should define tint variables for these uses (e.g. `--accent-primary-tint`, `--accent-primary-border`), or alternatively use modern CSS `color-mix()`:

```css
.badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid;
  background: color-mix(in srgb, var(--accent-primary) 13%, transparent);
  color: var(--accent-primary);
  border-color: color-mix(in srgb, var(--accent-primary) 27%, transparent);
}
```

### Code Blocks

The code block has a top bar simulating a window (with decorative dots) and the body with the code.

```css
.code-block {
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--border-default);
  background: var(--bg-surface);
}

.code-block__header {
  padding: 8px 16px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-default);
  display: flex;
  align-items: center;
  gap: 8px;
}

.code-block__dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
/* Dot colors are decorative macOS-standard — these can be hardcoded */
.code-block__dot--red {
  background: #ff5f57;
}
.code-block__dot--yellow {
  background: #febc2e;
}
.code-block__dot--green {
  background: #28c840;
}

.code-block__title {
  margin-left: 12px;
  color: var(--text-disabled);
  font-size: 12px;
  font-family: var(--font-mono);
}

.code-block__body {
  padding: 16px;
  overflow-x: auto;
  color: var(--text-secondary);
  font-size: 13px;
  line-height: 1.7;
  font-family: var(--font-mono);
  white-space: pre-wrap;
  word-break: break-word;
}
```

### Info Boxes / Callouts

```css
.callout {
  border-radius: var(--radius-md);
  padding: 14px;
  border: 1px solid;
}

.callout__title {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 6px;
}

.callout__body {
  font-size: 12px;
  line-height: 1.8;
}

/* Variants — use color-mix for tints or dedicated tokens from globals.css */
.callout--info {
  background: color-mix(in srgb, var(--accent-info) 8%, transparent);
  border-color: color-mix(in srgb, var(--accent-info) 25%, transparent);
}
.callout--info .callout__title {
  color: var(--accent-info);
}
.callout--info .callout__body {
  color: color-mix(in srgb, var(--accent-info) 70%, var(--text-muted));
}

.callout--warning {
  background: color-mix(in srgb, var(--accent-warning) 8%, transparent);
  border-color: color-mix(in srgb, var(--accent-warning) 25%, transparent);
}
.callout--warning .callout__title {
  color: var(--accent-warning);
}
.callout--warning .callout__body {
  color: color-mix(in srgb, var(--accent-warning) 70%, var(--text-muted));
}

.callout--error {
  background: color-mix(in srgb, var(--accent-error) 8%, transparent);
  border-color: color-mix(in srgb, var(--accent-error) 25%, transparent);
}
.callout--error .callout__title {
  color: var(--accent-error);
}
.callout--error .callout__body {
  color: color-mix(in srgb, var(--accent-error) 70%, var(--text-muted));
}
```

### Nav Tabs (horizontal)

```css
.nav-tabs {
  display: flex;
  gap: 4px;
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border-subtle);
  padding: 8px 16px;
  overflow-x: auto;
}

.nav-tab {
  background: transparent;
  border: 1px solid transparent;
  border-radius: var(--radius-md);
  padding: 8px 14px;
  color: var(--text-disabled);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: var(--transition);
}

.nav-tab:hover {
  background: var(--bg-hover);
  color: var(--text-muted);
}

.nav-tab--active {
  background: color-mix(in srgb, var(--accent-primary) 13%, transparent);
  border-color: color-mix(in srgb, var(--accent-primary) 27%, transparent);
  color: var(--accent-primary);
  font-weight: 600;
  filter: brightness(1.15);
}
```

### Card Grid

```css
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

@media (min-width: 768px) {
  .info-grid--3 {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

---

## 7. Rules and Constraints

**Tokens first:** Never use hex values directly in components. All colors come from `globals.css`.

**Borders:** Always `1px solid`. Never thicker. Border radius always between `var(--radius-sm)` and `var(--radius-lg)`.

**Shadows:** They do not exist in this style. Depth is achieved solely through background layers and subtle borders.

**Accent colors on large backgrounds:** Accent colors only go on text, borders, and very soft tints (max ~13% opacity as background). A solid accent-colored background breaks the style.

**Pure white text:** Avoid `#ffffff`. Use `var(--text-primary)` which should be an off-white defined in `globals.css`.

**Spacing:** 4px base. All padding and margin is a multiple of 4: 4, 8, 12, 16, 20, 24, 32. Never arbitrary values.

**Transitions:** Always `var(--transition)` on interactive elements. Never faster or slower than what is defined in the token.

**Uppercase labels:** Small text functioning as a label or category uses `text-transform: uppercase` with `letter-spacing: 1–2px` and `font-weight: 600`.

**Visual hierarchy:** Achieved solely through text size, font weight, and color (darker to lighter by importance). No heavy horizontal dividers or separators.

**`color-mix()` for tints:** Use `color-mix(in srgb, var(--accent-X) N%, transparent)` instead of hardcoded hex with opacity. Requires modern browsers (Chrome 111+, Firefox 113+, Safari 16.2+) — fully valid in 2026.

---

## 8. Full Example — Card with Code Block

```html
<div class="card">
  <div
    style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;"
  >
    <span class="label">Example</span>
    <span class="badge">TypeScript</span>
  </div>
  <p
    style="color:var(--text-muted); font-size:14px; line-height:1.8; margin:0 0 16px;"
  >
    Description of what this code does.
  </p>
  <div class="code-block">
    <div class="code-block__header">
      <span class="code-block__dot code-block__dot--red"></span>
      <span class="code-block__dot code-block__dot--yellow"></span>
      <span class="code-block__dot code-block__dot--green"></span>
      <span class="code-block__title">example.ts</span>
    </div>
    <pre class="code-block__body">const greeting = "hello";</pre>
  </div>
  <div class="callout callout--info" style="margin-top:16px;">
    <div class="callout__title">💡 Note</div>
    <div class="callout__body">Note text goes here.</div>
  </div>
</div>
```

```css
/* globals.css — minimum expected structure (values to be defined per project) */
:root {
  --bg-app: /* ... */;
  --bg-surface: /* ... */;
  --bg-card: /* ... */;
  --bg-hover: /* ... */;

  --border-subtle: /* ... */;
  --border-default: /* ... */;
  --border-hover: /* ... */;

  --text-primary: /* ... */;
  --text-secondary: /* ... */;
  --text-muted: /* ... */;
  --text-disabled: /* ... */;

  --accent-primary: /* ... */;
  --accent-secondary: /* ... */;
  --accent-success: /* ... */;
  --accent-warning: /* ... */;
  --accent-error: /* ... */;
  --accent-info: /* ... */;

  --font-sans: "Inter", sans-serif;
  --font-mono: "JetBrains Mono", monospace;

  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --transition: 0.2s ease;
}

body {
  background: var(--bg-app);
  color: var(--text-primary);
  font-family: var(--font-sans);
}

.label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-disabled);
  text-transform: uppercase;
  letter-spacing: 1.5px;
}
```

---

## 9. Common Adaptations

**For React with Tailwind:** Map all tokens from `globals.css` in `tailwind.config.js` under `theme.extend.colors` and `theme.extend.fontFamily`. Always use the resulting utility classes — no arbitrary values. Include the form tokens (`inputBg`, `inputBorder`, etc.) in the extend block.

**For light theme:** Follow Section 2 (Theme Architecture). The `[data-theme="light"]` block is already defined there. Do **not** define ad-hoc light overrides scattered across components.

**For animations:** This style is intentionally calm. All motion is handled exclusively via Framer Motion in `apps/web` — see Section 10 below. Never mix CSS transitions with Framer Motion on the same element. CSS `var(--transition)` is reserved for simple hover/border/color state changes only (not transforms or layout shifts). All motion respects `prefers-reduced-motion` (see Section 3).

**For `color-mix()` with fallback:** If legacy browser support is needed (rare in 2026 for developer tools), define explicit tint variables in `globals.css`:

```css
--accent-primary-tint: /* fallback value */;
--accent-primary-border: /* fallback value */;
```

**For accessible authentication (WCAG 2.2 SC 3.3.8):** Login and registration flows must not rely solely on cognitive function tests. Support password managers (no blocking of paste), offer passwordless options (magic links, passkeys, biometric), and ensure OTP fields allow paste from authenticator apps.

---

## 10. Framer Motion — Usage Rules

Framer Motion lives exclusively in `apps/web` (Next.js). It is never installed in `packages/ui` or `apps/api`. All animation logic stays in the web app layer.

### Core principle

Motion must feel **calm and purposeful** — it communicates state changes, not decoration. Every animation must have a reason: something appeared, disappeared, moved, or changed state. If you can remove the animation and the UI still makes sense, remove it.

### Placement in `apps/web`

```
apps/web/
  components/
    motion/           ← Reusable motion primitives (FadeIn, SlideUp, etc.)
  lib/
    motion.ts         ← Shared variants and transition presets
```

Define all `variants` and `transition` presets in `lib/motion.ts` and import them into components. Never define inline variants inside JSX — keep animation logic out of render output.

```ts
// lib/motion.ts

export const transitions = {
  default: { duration: 0.2, ease: "easeOut" },
  smooth: { duration: 0.35, ease: [0.4, 0, 0.2, 1] },
  spring: { type: "spring", stiffness: 300, damping: 30 },
  list: { duration: 0.2, ease: "easeOut" },
} as const;

export const variants = {
  fadeIn: {
    hidden: { opacity: 0 },
    visible: { opacity: 1 },
  },
  slideUp: {
    hidden: { opacity: 0, y: 8 },
    visible: { opacity: 1, y: 0 },
  },
  slideDown: {
    hidden: { opacity: 0, y: -8 },
    visible: { opacity: 1, y: 0 },
  },
  scaleIn: {
    hidden: { opacity: 0, scale: 0.96 },
    visible: { opacity: 1, scale: 1 },
  },
  listItem: {
    hidden: { opacity: 0, x: -6 },
    visible: { opacity: 1, x: 0 },
  },
} as const;
```

### Use cases and patterns

**Page transitions / route changes**

Wrap page content with `AnimatePresence` at the layout level. Use `slideUp` or `fadeIn` — never anything that shifts the layout significantly.

```tsx
// app/layout.tsx or a MotionLayout wrapper
<AnimatePresence mode="wait">
  <motion.div
    key={pathname}
    variants={variants.slideUp}
    initial="hidden"
    animate="visible"
    exit={{ opacity: 0, y: -4 }}
    transition={transitions.smooth}
  >
    {children}
  </motion.div>
</AnimatePresence>
```

**Component mount/unmount — modals, drawers, dropdowns**

Always wrap with `AnimatePresence` so exit animations run. Use `scaleIn` for modals, `slideDown` for dropdowns, `slideUp` for bottom drawers.

```tsx
<AnimatePresence>
  {isOpen && (
    <motion.div
      variants={variants.scaleIn}
      initial="hidden"
      animate="visible"
      exit={{ opacity: 0, scale: 0.96 }}
      transition={transitions.default}
    >
      {/* modal content */}
    </motion.div>
  )}
</AnimatePresence>
```

**Micro-interactions — hover, tap, press**

Use `whileHover` and `whileTap` directly on `motion` elements. Keep values minimal — this style does not do dramatic transforms.

```tsx
<motion.button
  whileHover={{ scale: 1.02 }}
  whileTap={{ scale: 0.97 }}
  transition={transitions.spring}
>
  Click me
</motion.button>
```

Never use `whileHover` scale above `1.04` or below `0.95`. This style is subtle.

**Animated lists / reorder**

Use `motion.li` with `variants` and a staggered parent for list entry animations. Use `layoutId` for reorder animations.

```tsx
// Parent
<motion.ul
  variants={{ visible: { transition: { staggerChildren: 0.06 } } }}
  initial="hidden"
  animate="visible"
>
  {items.map((item) => (
    <motion.li
      key={item.id}
      variants={variants.listItem}
      transition={transitions.list}
      layout
    >
      {item.label}
    </motion.li>
  ))}
</motion.ul>
```

### Hard rules

**Never animate:** background colors, font sizes, border widths, or shadows via Framer Motion — use CSS `var(--transition)` for those.

**Never use:** `duration` above `0.4s` for UI feedback animations. If it takes longer than that, it feels slow.

**Never use:** bounce, elastic, or overshoot springs on functional UI elements (buttons, form inputs). Springs are acceptable only on decorative or drag interactions.

**Always use `AnimatePresence`** when a component can unmount — otherwise exit animations are skipped silently.

**Respect `prefers-reduced-motion`:** Wrap animation-heavy components with a motion preference check:

```ts
// lib/motion.ts
import { useReducedMotion } from "framer-motion";

export function useSafeTransition(transition: object) {
  const reduce = useReducedMotion();
  return reduce ? { duration: 0 } : transition;
}
```

**SSR / Next.js App Router:** Use `'use client'` on any component that uses Framer Motion. Never use motion components in Server Components.

---

## 11. Notificaciones (Toast)

Para las notificaciones tipo Toast (como `success`, `error`, `warning`, `info`, entre otras), se debe utilizar la librería **Sileo** de React.

### Reglas de Uso de Sileo

- **Integración de Tokens:** Configura Sileo para usar los tokens definidos en `globals.css`. El fondo debe ser `var(--bg-card)` o `var(--bg-surface)` con bordes `var(--border-default)`.
- **Acentos por Estado:** Utiliza los colores de acento correspondientes a cada tipo de notificación:
  - `success`: `var(--accent-success)`
  - `error`: `var(--accent-error)`
  - `warning`: `var(--accent-warning)`
  - `info`: `var(--accent-info)`
- **Tipografía y Estilo:** Mantén un diseño minimalista y sin sombras intrusivas, usando `var(--font-sans)` y tamaños de texto previamente definidos (ej. 13px o 14px).
- **Animación:** Las animaciones de entrada y salida proporcionadas por la librería deben ser fluidas y sutiles, acatando de ser posible las preferencias de `prefers-reduced-motion`.
