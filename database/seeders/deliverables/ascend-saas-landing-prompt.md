# Ascend — Prompt Plan (build it with Claude Code)

Prompts to build the high-converting SaaS marketing landing from the live demo, including the live pricing toggle.

**Stack:** single self-contained `index.html` (inline CSS + vanilla JS). Deployable to any static host.

---

## 1. Structure & tokens
> Create a single-file SaaS landing `index.html`. Light theme: cool near-white paper `oklch(0.985 0.006 230)`, navy ink `oklch(0.22 0.035 255)`, emerald accent `oklch(0.60 0.13 165)`. Sections: sticky nav, hero, trust logos, features, pricing, FAQ, footer. Inter/system font.

## 2. Hero with animated counters
> Build a centered hero with an eyebrow pill, a bold headline (clamp), a subhead, two CTAs (solid emerald + ghost), and a radial accent glow behind it. Add 3 stat counters (e.g. "12,000 teams", "99% uptime", "40h saved/week") that count up with an ease-out cubic on load; respect `prefers-reduced-motion`.

## 3. Features grid
> Add a 3-column feature grid (6 cards): soft icon tile, title, one-line description, lift-on-hover. Collapses to one column on mobile.

## 4. Live pricing toggle
> Build a 3-tier pricing section (Starter / Growth[popular] / Scale) with a Monthly/Yearly switch. Toggling updates each price (store monthly + yearly values as data-attributes) and shows a "Save 20%" badge. The popular tier is visually elevated.

## 5. FAQ + ship
> Add an accordion FAQ (max-height transition), a footer, and make the whole page responsive and self-contained (no external requests). Wire the CTA/plan buttons to a small toast for the demo.

---

**Tips**
- Keep monthly/yearly prices in `data-m`/`data-y` attributes so the toggle is trivial.
- The counter animation + pricing toggle are the interactive hooks buyers remember.
- Swap copy, logos, and prices; the structure converts as-is.

*Included with your purchase. Questions? Reply to your receipt email.*
