---
inclusion: always
---

# [ABSOLUTE] Non-negotiable constraints — ALWAYS apply
- **Language:** Respond in English at all times.
- **CLI:** Use `./vendor/bin/sail artisan` — NEVER bare `php`.
- **TypeScript:** Strict mode enforced on ALL `.tsx` / `.ts` files.
- **Context7 (MCP):** Always resolve live docs — never rely on cached training knowledge.
- **Investigate / Investigar:** Run Tavily search immediately before responding.

---

# [MUST] Before writing any code — read the relevant skill

Your detailed skills are in `.agents/skills/`:
- `ARCHITECTURE-INTERMEDIATE-PHP.md` — PHP/Laravel backend architecture
- `ARCHITECTURE-REACT-INERTIA.md` — React/Inertia frontend architecture  
- `RULES-STYLES.md` — CSS token system and design rules
- `RULES-FULLSTACK.md` — Full stack coding standards
- `HOW-TO-USE.md` — CRUD module creation guide

These are automatically loaded via context-specific steering files (backend.md, frontend.md, styles.md).

---

# [MUST] CSS / Styles
- Follow `/RULES-STYLES` strictly.
- NEVER hardcode hex, `bg-red-600`, or `bg-[#hex]`. Use `var(--token)` only.
- All tokens defined in `resources/css/app.css`.

---

# [MUST] React / TypeScript
- Follow `/ARCHITECTURE-REACT-INERTIA` and `/RULES-FULLSTACK`.
- No `any`. No `@ts-ignore`. No hardcoded colors in components.
- Every page wrapped in correct Layout. State always explicitly typed.

---

# [MUST] Laravel / PHP
- Follow `/RULES-FULLSTACK` and `/HOW-TO-USE`.
- No business logic in Controllers. No `php` bare CLI.
- Web routes = primary (Inertia + session). API routes = secondary (mobile/Sanctum only).

---

# [SHOULD] General quality
- Mobile-first on every UI component.
- `font-family: var(--font-sans)` everywhere.
- Prefer descriptive names over comments.