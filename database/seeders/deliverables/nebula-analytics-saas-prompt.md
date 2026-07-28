# Nebula — Prompt Plan (build it with Claude Code)

Prompts to build the animated revenue-analytics dashboard from the live demo. Dependency-free charts.

**Stack:** single self-contained `index.html` (inline CSS + vanilla JS + inline SVG). Drop the JS data layer onto any backend later.

---

## 1. Layout & tokens
> Create a single-file analytics dashboard `index.html`. Dark theme: background `oklch(0.17 0.021 275)`, panels `oklch(0.205 0.026 275)`, violet accent `oklch(0.72 0.15 285)`, cyan secondary. Layout: left sidebar (logo, nav, plan-usage widget) + main area. Use tabular-nums for all numbers.

## 2. KPI cards with sparklines
> Add a row of 4 KPI cards (MRR, Active users, Churn, ARPU). Each shows a big value, an up/down delta pill (green/red, separate from the accent), and a tiny inline-SVG sparkline. Generate the sparkline points from a small deterministic PRNG so they look alive but stable.

## 3. Animated area chart
> Build the main MRR chart as an inline SVG area chart: gradient fill under a smooth stroke that animates in via stroke-dasharray on load. Add faint dashed gridlines. On mousemove, show a floating tooltip and a moving dot at the nearest data point.

## 4. Range toggle + donut + table
> Add a 7D/30D/90D/12M segmented control that swaps datasets and re-renders the chart + KPIs. Add an SVG donut for traffic sources with a legend, and a "Top products" table with mini progress bars and trend pills.

## 5. Live feel
> Add an "active now" counter that ticks every ~2s with small random deltas. Make it responsive (KPI grid 4→2 cols, panels stack) and respect `prefers-reduced-motion`.

---

**Tips**
- Keep one `renderChart(array)` function; the range toggle just feeds it new data.
- Compute SVG paths by hand (no chart library) — it stays tiny and fast.
- To wire real data, replace the `DATA` object with an API fetch returning the same shape.

*Included with your purchase. Questions? Reply to your receipt email.*
