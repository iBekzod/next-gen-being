# Ember & Oak — Build Prompts (Restaurant / Hospitality Landing)

A step-by-step set of Claude Code prompts to reproduce this exact award-tier,
single-file WebGL landing page. No frameworks, no build step, one HTML file.

## 1. Scaffold and constraints
- "Create ONE self-contained HTML file (doctype through /html). In head add
  charset, viewport, a title, and exactly this tag:
  `<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>`.
  No other external requests. System font stack only. No emoji anywhere."

## 2. Design tokens
- "Define OKLCH CSS custom properties on :root. Accent hue is ember-orange
  (~oklch(0.68 0.17 45)) with a lighter highlight and a deep variant. Add a
  tinted near-black espresso background, warm cream text, muted/faint greys,
  and translucent glass + hairline border tokens. Add an --ease cubic-bezier."

## 3. Layering
- "Add fixed full-screen `<canvas id=webgl>` and `<canvas id=fallback>` at
  z-index 0, a `.veil` radial-gradient overlay at z-index 1, and a `.page`
  wrapper at z-index 2."

## 4. Navigation
- "Build a fixed glass nav: backdrop-filter blur + saturate, translucent bg,
  1px hairline bottom border. Logo with an inline SVG flame mark, three text
  links, and a primary CTA button."

## 5. Hero
- "Hero is min-height 100svh, centered. Add an eyebrow pill with a glowing dot,
  a kinetic h1 where each line is `<span class=line><span>text</span></span>`,
  a lead paragraph, two CTA buttons, and a scroll-to-explore hint with an
  animated rail at the bottom. Headline lines start translateY(112%); a .go
  class added ~140ms after load reveals them with a staggered transition."

## 6. Sections
- "Add three sections: a 3-card features grid (Live Reservations, Seasonal
  Menu, Private Events) each with a stroke=currentColor line-icon; an
  interactive seasonal menu panel with course tabs (First/Fire/Sweet/Pairings)
  that swap dish panes via real JS and aria-selected; and a final CTA band with
  a radial glow. Write specific, persuasive live-fire restaurant copy — never
  lorem. Cards lift and glow on hover."

## 7. Footer
- "Add a glass footer: brand blurb, three link columns (Visit / Room /
  Contact), and a base row with copyright."

## 8. WebGL scene (init3D)
- "Using window.THREE, render a slowly rotating plated signature dish: a metal
  plate disc, a glowing ember torus rim, and a food dome using a custom
  ShaderMaterial with fresnel rim + vertical subsurface glow and gentle vertex
  displacement. Add garnish icosahedra, sauce dots, and ~260 additive Points as
  rising, drifting steam. Warm directional key light plus an ember point light.
  Drive rotation, tilt, scale, camera dolly, fill intensity and steam opacity
  from window scroll progress 0..1, and lerp mouse to camera for parallax."

## 9. Fallback + robustness
- "Implement initFallback() that draws an animated multi-blob canvas aurora in
  the palette if THREE or WebGL is unavailable. Feature-detect and call the
  right one. Respect prefers-reduced-motion everywhere: render one static frame,
  no rAF loop, no transitions. Add an IntersectionObserver that toggles .rv to
  .in for section reveals. Ensure valid closed HTML, no console errors, and that
  it works opened directly from a file. Keep it a single file, 14-24 KB."