# Meridian Pay — Build Prompts (Claude Code)

A step-by-step prompt sequence to reproduce this exact single-file WebGL landing page for a fintech / digital-banking brand. No emoji anywhere; inline SVG line icons only; system font stack only.

## 1. Scaffold the document
- Create ONE self-contained `index.html` (`<!doctype html> ... </html>`).
- In `<head>` add `<meta charset>`, `<meta viewport>`, a `<title>`, and exactly this tag: `<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>`.
- No other external requests — no fonts, images, or extra CDNs.

## 2. Design tokens
- Define OKLCH CSS custom properties on `:root`: a deep-teal accent (`oklch(0.74 0.13 185)`), a cold-mint `--accent-2`, a tinted near-black graphite background, muted/faint text, translucent `--glass` fills, and 1px hairline border colors.
- Set a `--sp` fluid page gutter via `clamp()`. Use system-ui font stack, `font-synthesis:none`.

## 3. Backdrop layers
- Add fixed full-screen `<canvas id="webgl">` and `<canvas id="fallback">` at `z-index:0`.
- Add a `.veil` radial-gradient overlay at `z-index:1` (vignette + soft teal glow).
- Wrap all content in `.page` at `z-index:2`.

## 4. Chrome and hero
- Fixed glass `<nav>`: brand mark (inline SVG), 3 links, primary CTA; blurred with hairline bottom border.
- `<header class="hero">` at `min-height:100svh`, centered: an eyebrow pill, a kinetic `<h1>` with each line wrapped `<span class="line"><span>…</span></span>`, a lead paragraph, two CTAs, and a "scroll to explore" hint.
- Headline copy: "Banking without the friction." / "Send, spend, and grow in one tap." Oversized grotesque: weight 800, letter-spacing -0.04em, `clamp()` sizing.

## 5. Sections
- Features: 3-card glass grid (Instant Settlement, Bank-Grade Security, Smart Insights) with SVG icons and hover lift.
- Interactive pricing: a glass panel with three tabs (Send money / Currency exchange / Card spend), each with a range slider that live-updates fees, savings, cashback via real JS. Pair with persuasive copy and check-mark ticks.
- Security: a centered trust strip of compliance chips (SOC 2, PCI-DSS, FDIC, 256-bit).
- Final CTA band inside a glowing glass panel; then a multi-column `<footer>`.

## 6. WebGL scene — init3D()
- Use `window.THREE`. Build a rounded-rect credit card via `Shape` + `ExtrudeGeometry`, centered.
- Give it a custom `ShaderMaterial`: fresnel rim in mint, brushed anisotropic streaks, and a moving caustic sweep in teal.
- Add a gold chip plane, then ~7 additive `Line` "transaction ribbons" on `CatmullRomCurve3` paths flowing to/from the chip, plus a faint additive particle dust field.
- Animate with a `Clock`: scroll progress (0..1) drives rotation, scale, and camera dolly; lerp mouse to camera and card tilt for parallax. Pulse ribbon opacity.

## 7. Fallback and motion
- `initFallback()` draws an animated multi-blob Canvas 2D aurora using `lighter` composite.
- Feature-detect WebGL + `window.THREE`; call the right init, wrapping init3D in try/catch.
- Kinetic headline: add `.go` ~140ms after load. IntersectionObserver toggles `.rv -> .in`.
- Gate ALL motion behind `prefers-reduced-motion`: render one static frame, no rAF loop, no transitions.

## 8. Verify
- Valid closed HTML, every `<script>` closed, zero console errors, opens from `file://`. Keep it one file.
