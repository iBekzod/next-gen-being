# Aureline Residences — Build Prompts

A step-by-step set of Claude Code prompts to reproduce this exact luxury real estate landing page: a single self-contained HTML file with a scroll-driven Three.js villa and a Canvas fallback.

## 1. Scaffold and constraints
Create ONE self-contained `<!doctype html>` document. In `<head>` add `<meta charset>`, `<meta viewport>`, a `<title>`, and exactly this script tag: `<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>`. No other external requests, no fonts, no images. System font stack only. No emoji anywhere; all icons are inline `<svg>` with `stroke=currentColor` and `stroke-width` ~1.8.

## 2. Design tokens
Define OKLCH CSS custom properties on `:root`. Accent hue is warm champagne gold near `oklch(0.76 0.09 78)` with lighter and deeper variants. Background is a tinted near-black `oklch(0.19 0.008 66)`. Add ink, muted, faint text tiers, hairline border tokens at low alpha, and translucent glass fills. Store one shared `--ease: cubic-bezier(.22,1,.36,1)`.

## 3. Backdrop layers
Add fixed full-screen `<canvas id="webgl">` and `<canvas id="fallback">` at z-index 0. Add a `.veil` radial + linear gradient overlay at z-index 1 for cinematic vignetting. Wrap all content in `.page` at z-index 2.

## 4. Navigation and hero
Build a fixed glass `<nav>` with a house-mark SVG logo, three anchor links, and a primary CTA, using backdrop-filter blur and a hairline bottom border. Build `<header class="hero">` at min-height 100svh, centered: an eyebrow pill with a glowing dot, a kinetic `<h1>` where each line is `<span class="line"><span>text</span></span>`, a lead paragraph, two CTA buttons, and a bottom scroll hint with an animated rail.

## 5. Sections
Add three sections with real, specific luxury-property copy (never lorem): a three-card feature grid (Immersive Tours, Concierge Enquiry, Neighborhood Intel); an interactive light-tour with clickable tabs (dawn / noon / dusk) that swap a glass detail panel; and a residences grid with three priced villas plus a final glass CTA band. Close with a multi-column `<footer>`.

## 6. Interactivity (real JS)
Wire the light-tour tabs to toggle an `.on` class on the matching panel view and shift a radial glow position. Add an IntersectionObserver that promotes `.rv` to `.in` for scroll reveals. Trigger the kinetic headline by adding a `.go` class to the `<h1>` about 140ms after load with staggered line transitions.

## 7. WebGL scene — init3D()
Using `window.THREE`, build a cinematic architectural massing model: six stacked slabs plus a translucent glass volume that assemble floor-by-floor driven by scroll progress (0..1). Add a `DirectionalLight` sun that arcs across the sky on scroll casting PCFSoft shadows, a metallic reflecting-pool circle, a ground plane, and a fresnel-rim `ShaderMaterial` torus ring. Drive camera orbit and height from scroll, lerp mouse to camera for parallax, and warm the sun color toward gold at low angles. Use a `THREE.Clock` for the loop.

## 8. Fallback — initFallback()
If THREE or WebGL is unavailable, hide the webgl canvas and draw an animated multi-blob Canvas 2D aurora in champagne tones using additive compositing, lightly reactive to scroll and mouse. Feature-detect and call the correct initializer inside a try/catch.

## 9. Reduced motion and robustness
Respect `prefers-reduced-motion`: render a single static 3D (or aurora) frame with no rAF loop, disable all CSS transitions and reveals. Ensure valid closed HTML, every `<script>` closed, no console errors, and that it works opened directly from a file. Keep it a single file.

## 10. Copy and brand
Invent the brand Aureline Residences, a boutique property studio releasing twelve villas by invitation. Write persuasive, understated headlines, feature descriptions, three named priced residences (Meridienne, Solene, Caldera), and a footer, all aimed at brokerages and agencies selling high-ticket homes.