# Lumen Dental Studio — Build Prompts

A step-by-step set of Claude Code prompts to reproduce this exact award-tier WebGL dental landing page as one self-contained HTML file.

## 1. Scaffold
- Create a single `index.html`, `<!doctype html>` through `</html>`, no build step.
- In `<head>` add `<meta charset>`, `<meta viewport>`, a `<title>`, and the exact Three.js r128 CDN script tag. No other external requests; system font stack only.
- Establish OKLCH design tokens in `:root`: a sky-blue accent (`oklch(0.72 0.14 235)`), a soft-mint accent (`oklch(0.82 0.10 175)`), a tinted near-black background, muted ink, hairline border, and glass fill variables.

## 2. Backdrop layers
- Add fixed full-screen `<canvas id="webgl">` and `<canvas id="fallback">` at `z-index:0`.
- Add a `.veil` radial-gradient overlay at `z-index:1` and a `.page` wrapper at `z-index:2`.

## 3. Navigation
- Fixed glass `<nav>`: `backdrop-filter:blur`, translucent bg, 1px hairline bottom border.
- Logo with an inline SVG tooth mark, three anchor links, and a primary gradient CTA button.

## 4. Hero
- `<header class="hero">` at `min-height:100svh`, centered.
- Eyebrow pill, kinetic `<h1>` with each line wrapped `<span class="line"><span>…</span></span>`, lead paragraph, two CTAs, and a scroll hint with an animated mouse glyph.
- Copy: headline "The visit you'll actually look forward to." with a gradient second line.

## 5. Content sections
- Care: a 3-column glass card grid — Online Booking, Painless Technology, Transparent Pricing — each with a stroke-only inline SVG icon.
- Treatments: an interactive tab strip driving a `#panel` that renders description, checklist, and a live price box from a JS `TREATMENTS` array. Make it genuinely interactive with real click handlers.
- Trust: a 4-up stat grid.
- CTA band: a glowing gradient glass panel with final booking CTAs.

## 6. Footer
- Brand blurb, three link columns, and a bottom legal row separated by a hairline border.

## 7. WebGL scene — `init3D()`
- Build a tooth from a `LatheGeometry` profile plus a wireframe cross-section shell.
- Give the tooth a custom `ShaderMaterial` with a fresnel rim and animated enamel-layer bands; add ~900 additive `Points` as drifting air particles.
- Drive rotation, scale, shell opacity, and camera z with `window` scroll progress 0..1; lerp mouse to camera for parallax; animate via `THREE.Clock`.

## 8. Fallback — `initFallback()`
- Draw an animated multi-blob canvas aurora if THREE or WebGL is unavailable. Feature-detect and call the correct initializer.

## 9. Motion + robustness
- Add the `.go` class to the headline ~140ms after load for the staggered reveal.
- IntersectionObserver toggles `.rv` to `.in` for section reveals.
- Gate all motion behind `prefers-reduced-motion` (render one static 3D frame, no rAF loop).
- Verify valid closed HTML, every script closed, no console errors, works from `file://`.