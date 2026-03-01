---
inclusion: fileMatch
fileMatchPattern: "**/*.{tsx,css,ts}"
name: styles
description: CSS token system and design rules. Loaded when editing component or style files.
---

Read the full skill before writing any styles:
#[[file:.agents/skills/RULES-STYLES.md]]

Key reminders:
- NEVER hex, `bg-red-600`, or `bg-[#hex]` — only `var(--token)`.
- NEVER inline `rgba()` — use `color-mix(in srgb, var(--token) N%, transparent)`.
- All tokens in `resources/css/app.css`. Add missing tokens there first.
- Dark default. Light override in `[data-theme="light"]`.
- shadcn/ui components: use Tavily to search modern hover styles before implementing — adapt to project tokens.
- Deleted rows: `var(--deleted-row-bg)` + `var(--deleted-row-opacity)`.