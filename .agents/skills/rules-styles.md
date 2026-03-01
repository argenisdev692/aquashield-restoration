# Dark Developer UI — Implementation Guide

Style based on VS Code, Linear, Raycast, and Vercel aesthetics. Minimalist, high contrast, developer-oriented.

---

## 0. Token-First Principle

Never use hex values directly in components. Always reference CSS variables from `globals.css`:

```css
/* ✅ Correct */
background: var(--bg-card);
color: var(--text-muted);

/* ❌ Incorrect */
background: #1a1a2e;
```

For Tailwind, map all tokens in `tailwind.config.js` under `theme.extend`. Never use arbitrary values like `bg-[#1a1a2e]`.

---

## 1. Expected Token Structure

The `globals.css` file must expose these token groups:

### Backgrounds (darkest to lightest)
```
--bg-app        → Base application background
--bg-surface    → Main containers, sidebars
--bg-card       → Cards, inputs, code blocks
--bg-hover      → Hover state for interactive elements
```

### Borders
```
--border-subtle   → Nearly invisible dividers
--border-default  → Most common, cards and containers
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
--font-sans  → Narrative text (Inter or equivalent)
--font-mono  → Code (JetBrains Mono or equivalent)
```

### Radii and Transitions
```
--radius-sm  → Small rounded corners
--radius-md  → Standard buttons, inputs
--radius-lg  → Cards, modals, code blocks
--transition → Standard duration and easing
```

---

## 2. Theme Architecture — Dark/Light

Default to dark mode. Plan for light mode from day 1.

### Default (dark)
Define all tokens in `:root`

### Light mode override
Override tokens in `[data-theme="light"]`

### System preference fallback
```css
@media (prefers-color-scheme: light) {
    :root:not([data-theme]) {
        /* light mode tokens */
    }
}
```

### Toggle logic
Store in `localStorage`, set on `<html>` element

---

## 3. Accessibility — WCAG 2.2 AA

### `:focus-visible` (keyboard focus ring)
```css
:focus {
    outline: none;
}

:focus-visible {
    outline: 2px solid var(--accent-primary);
    outline-offset: 2px;
}
```

**Rules:**
- Focus ring must achieve ≥ 3:1 contrast ratio
- Focused element never obscured
- Never remove without alternative

### `prefers-reduced-motion`
```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

### Minimum target size — 24 × 24 CSS pixels
All interactive targets must have minimum 24×24px touch/click area.

---

## 4. Form Tokens

### Required form tokens in `globals.css`
```
--input-bg
--input-bg-disabled
--input-border
--input-border-hover
--input-border-focus
--input-border-error
--input-border-success
--input-text
--input-placeholder
--input-label
--input-helper
--input-error-text
--input-height
--input-padding-x
--input-font-size
--input-radius
```

---

## 5. Typography

Two font families: sans-serif for text, monospace for code.

### Size and weight scale
```
Main heading        22px   weight 800   letter-spacing: -0.5px
Section heading     18px   weight 700
Subheading          14px   weight 600
Body text           14px   weight 400   line-height: 1.8
Small text          12px   weight 500
Label / tag         11px   weight 600   uppercase   letter-spacing: 1.5px
```

Monospace for: code blocks, inline snippets, file paths, technical values

---

## 6. Core Components

### Cards / Containers
- Background: `var(--bg-card)`
- Border: `1px solid var(--border-default)`
- Border radius: `var(--radius-lg)`
- No box shadow

### Buttons
- Primary: `var(--accent-primary)` background
- Ghost: transparent background, `var(--border-default)` border
- Hover: `filter: brightness(0.88)` or `var(--bg-hover)`

### Badges / Tags
- Background: `color-mix(in srgb, var(--accent-primary) 13%, transparent)`
- Text: `var(--accent-primary)`
- Border: `color-mix(in srgb, var(--accent-primary) 27%, transparent)`

### Code Blocks
- Top bar with decorative dots (macOS style)
- Body with monospace font
- Background: `var(--bg-surface)`

### Info Boxes / Callouts
- Use `color-mix()` for tints
- Variants: info, warning, error
- Uppercase title, body text

### Nav Tabs
- Horizontal flex layout
- Active tab: tinted background with accent border
- Inactive: transparent, muted text

---

## 7. Rules and Constraints

- **Shadcn UI**: Every new component MUST be Shadcn UI
- **Tokens first**: Never use hex values directly
- **Borders**: Always `1px solid`, radius between `--radius-sm` and `--radius-lg`
- **Shadows**: Do not exist — use background layers and borders
- **Accent backgrounds**: Max ~13% opacity tints only
- **Pure white text**: Avoid `#ffffff`, use `var(--text-primary)`
- **Spacing**: 4px base, all multiples of 4
- **Transitions**: Always `var(--transition)`
- **Uppercase labels**: Use `text-transform: uppercase` with `letter-spacing: 1–2px`
- **Visual hierarchy**: Through text size, weight, and color only
- **`color-mix()` for tints**: Use instead of hardcoded hex with opacity

---

## 8. Framer Motion — Usage Rules

Motion lives exclusively in `apps/web` (Next.js). Never in `packages/ui` or `apps/api`.

### Core principle
Motion must feel calm and purposeful. Every animation must have a reason.

### Placement
```
apps/web/
  components/motion/    ← Reusable motion primitives
  lib/motion.ts         ← Shared variants and transitions
```

### Use cases
- **Page transitions**: `slideUp` or `fadeIn`
- **Modals/drawers**: `scaleIn` for modals, `slideDown` for dropdowns
- **Micro-interactions**: `whileHover`, `whileTap` (keep subtle)
- **Animated lists**: Staggered entry with `layoutId` for reorder

### Hard rules
- Never animate: background colors, font sizes, border widths, shadows (use CSS)
- Never use: `duration` above `0.4s`
- Never use: bounce/elastic springs on functional UI
- Always use: `AnimatePresence` for unmounting components
- Respect: `prefers-reduced-motion` with `useSafeTransition` hook
- SSR: Use `'use client'` on motion components

---

## 9. Notifications (Toast)

Use **Sileo** library for React.

### Rules
- Integrate tokens from `globals.css`
- Background: `var(--bg-card)` or `var(--bg-surface)`
- Borders: `var(--border-default)`
- Accents by state: success, error, warning, info
- Typography: `var(--font-sans)`, 13px or 14px
- Animations: Fluid and subtle, respect `prefers-reduced-motion`
