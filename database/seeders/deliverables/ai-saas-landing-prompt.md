# Cortex AI — Build Prompts (Claude Code)

A step-by-step prompt sequence to reproduce the Cortex AI landing page: a single self-contained HTML file with a WebGL neural-network hero, glassmorphism, and an interactive capabilities widget. No emoji, system fonts only, one allowed CDN (Three.js r128).

## 1. Scaffold the document
"Create one self-contained HTML5 file. In the head add charset, viewport, a title, and exactly this tag: Three.js r128 from cdnjs. No other external requests, system font stack only. Define a CSS custom-property theme using OKLCH: a vivid-indigo accent (~oklch(0.66 0.19 274)), soft lavender secondary, a tinted near-black navy background, and ink/dim/faint text tokens. Add a shared cubic-bezier(0.16,1,0.3,1) easing var."

## 2. Backdrop layers
"Add a fixed full-screen canvas#webgl and canvas#fallback at z-index 0, a .veil radial-gradient overlay at z-index 1 (indigo bloom top-center, lavender bottom-right, fade to bg), and a .page wrapper at z-index 2. Add a .wrap container maxing at 1160px."

## 3. Navigation
"Build a fixed glass nav: blurred translucent background, 1px hairline bottom border. Left: an inline-SVG node-graph logo mark plus 'Cortex AI'. Center: Product, Capabilities, Pricing links. Right: a gradient 'Start free' primary button. Hide center links under 860px."

## 4. Hero
"Create header.hero, min-height 100svh, centered. Include an eyebrow pill with a glowing dot, an h1 with two lines each wrapped as span.line > span (line 2 uses a gradient text clip), a lead paragraph, two CTA buttons (primary + ghost with an arrow icon), and a bottom 'Scroll to explore' hint with an animated falling track. Headline copy: 'Work smarter, not louder.' / 'AI that drafts, decides, and delivers.'"

## 5. Kinetic headline
"Each headline inner span starts at translateY(110%). On window load, after ~140ms add a .go class to the h1 that transitions them to 0 with a staggered delay on line 2. Gate all motion behind prefers-reduced-motion."

## 6. Features grid
"Add a 3-column features grid of glass cards, each with a rounded inline-SVG line icon (stroke-width 1.8), title, and description. Use the exact three: Natural Language, Workflow Automation, Enterprise-Ready with the provided copy. Hover: lift 6px plus a corner glow."

## 7. Interactive capabilities widget
"Build a glass panel with a real tab widget (Draft, Automate, Retrieve). Each pane is a two-column layout: a description with a checklist on the left, and a fake product 'viz' on the right (a monospace prompt line with a blinking caret plus status rows with badges). Wire tabs in vanilla JS toggling aria-selected and an .active pane. Add a four-item stats row below."

## 8. Pricing + CTA + footer
"Add a 3-tier pricing grid (Starter free, Team $29 featured with a Popular tag, Enterprise custom), each with a checklist and CTA. Then a centered glowing CTA band 'Give your team a second brain.' Finish with a 4-column footer (brand blurb + Product/Company/Trust link columns) and a bottom copyright bar."

## 9. Reveal + robustness
"Add .rv -> .in IntersectionObserver reveals for section blocks. Ensure valid closed HTML, every script closed, no console errors, works from file://."

## 10. WebGL scene
"Implement init3D() with window.THREE: a Group holding a fibonacci-sphere of ~70 additive glowing point orbs (radial-gradient sprite texture), LineSegments synapse threads connecting near neighbors, and a single 'firing' pulse orb that travels segment-to-segment in sequence. Each frame, morph node positions to pull toward the lerped mouse point, update the line buffer to follow, rotate/scale the group by scroll progress (0..1), drift line hue on scroll, and lerp the camera for mouse parallax with lookAt(0,0,0). Drive a THREE.Clock."

## 11. Fallback + gating
"Implement initFallback() drawing an animated multi-blob canvas aurora in the brand palette. Feature-detect WebGL and window.THREE; call init3D in a try/catch that falls back on error, else initFallback. Under prefers-reduced-motion render a single static frame with no rAF loop, for both renderers."