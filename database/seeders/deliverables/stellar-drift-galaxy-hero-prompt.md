# Stellar Drift - Particle Galaxy Hero (prompt + source)

A cursor-reactive galaxy for a hero section: 90,000 stars drawn in one call, differential rotation, a gravity well that follows the pointer, and a click shockwave. Everything runs on the GPU. This file gives you (1) the master prompt to recreate it with Claude, (2) one-line prompts to customise it, (3) the tuning table, and (4) the complete single-file source.

You can use it in client and commercial projects. Do not resell the prompt or source as a template pack.

---

## 1. Master prompt (paste into Claude, Cursor, v0 or Lovable)

```
Build a single self-contained HTML file: a full-viewport particle galaxy hero using three.js r160 loaded as an ES module from
https://cdnjs.cloudflare.com/ajax/libs/three.js/0.160.0/three.module.min.js (use dynamic import() inside try/catch).

VISUAL
- Dark background #04050c. One THREE.Points object with 90,000 particles (38,000 on touch/narrow screens), additive blending, depthWrite off.
- Galaxy shape is computed ONLY in the vertex shader from per-particle attributes: aA = (radius01, armIndex, angleJitter, sizeRandom), aB = three gaussian-ish scatter values.
  Radius r = pow(radius01, 1.3) * R. Four spiral arms: angle = arm * 2PI/4 + r * twist + jitter * (0.34 - 0.24 * r/R) + time * omega, with omega = 0.55 / (0.30 + 0.95 r) so the core spins faster than the rim.
  Scatter grows with radius; add a central bulge by multiplying y by 1 + 2.6 * exp(-16 (r/R)^2).
- Colour by radius through three uniforms core -> mid -> rim. Default palette: #ffd9a0, #d653a8, #3c78ff. Brighten the core, twinkle with sin(time + random).
- Point sprite: soft round falloff in the fragment shader (discard outside radius 0.5, alpha = pow(1 - 2d, 1.8)). Clamp gl_PointSize to 1..36 px and scale it by viewport height / view depth.
- A flat additive plane under the galaxy with a radial glow (tight core + wide halo) and 2,200 distant stars (900 on mobile) on a far sphere.
- Camera: PerspectiveCamera fov 48 placed high and slightly to the side, camera.up rolled by 0.2 rad for a diagonal composition. On wide screens look at x = -1.5 so the galaxy sits right of the text.

INTERACTION (all as shader uniforms, no CPU per-particle work)
- Cursor gravity well: raycast the pointer to the y = 0 plane, smooth it (lerp 0.09). Particles near it (gaussian, sigma ~0.55) are pushed outward a little, swirled tangentially a lot, and lifted with sin(angle * 3 + time).
- Click / tap: spawn a shockwave ring at the hit point that expands at 4.2 units/s and fades with exp(-0.85 t), displacing particles outward and up and making them larger and brighter.
- After 3 s without input let the well wander along a slow Lissajous path (so phones, which have no hover, still look alive).
- Camera parallax: offset the camera by up to 0.5 / 0.35 units from the pointer.
- UI: three palette swatches (aurora, ember, glacier) that lerp the three colour uniforms.

HERO COPY (placeholder): small eyebrow, h1 "Give the first screen gravity.", one sentence, one pill CTA. Text sits bottom-left, pointer-events none except the button. Add a radial vignette and a left-to-right dark gradient for legibility.

PRODUCTION RULES (all required)
1. Cap pixel ratio at 2 (1.5 on touch). Render with antialias off.
2. Pause the render loop when the canvas is off screen (IntersectionObserver) or the tab is hidden. Also accept window.postMessage({ngbVisible: bool}) from a host page.
3. Adaptive quality: if the frame time stays above 22 ms for ~40 frames, lower the pixel ratio by 0.25 down to 1, then reduce geometry.setDrawRange in 70% steps down to 20,000 particles.
4. prefers-reduced-motion: render exactly one still frame (time fixed), no loop, no pointer reaction, no pulses. Re-render on resize and palette change.
5. If the three.js import fails, WebGL is unavailable, or the context is lost, show a CSS-only radial-gradient fallback instead of the canvas.
6. Resize through ResizeObserver; keep the galaxy framed on portrait phones.
7. The h1 must be real text in the DOM; the canvas is aria-hidden. All controls are real buttons with aria-labels and a visible focus ring.
8. No build step, no other dependencies, comments only where the maths is non-obvious.
```

---

## 2. Customise it (one line each, paste after the master prompt or into a follow-up)

- "Change it to a barred galaxy: two long straight arms from a bar of stars through the core, then four fainter arms."
- "Replace the click shockwave with a hyperspace jump: on click, stretch all particles along the view direction for 0.6 s and ease back."
- "Make the galaxy react to scroll: rotate and tilt it as the page scrolls past the hero, and fade the stars to 30% opacity."
- "Use my brand colours #0ea5e9, #6366f1, #f0abfc and put my logo, headline and CTA in the hero."
- "Convert it to a React component for Next.js: dynamic import with ssr:false, cleanup on unmount, props for colours and particle count."
- "Add a planet: a lit sphere orbiting the core on a tilted ring, depth-tested against the particles."

## 3. Tuning table (constants in the source)

| Constant / uniform | Default | Effect |
|---|---|---|
| `COUNT` | 90000 / 38000 | Star count. Biggest GPU cost. |
| `RADIUS` | 4.6 | Galaxy size in world units. |
| `uArms` | 4 | Number of spiral arms. |
| `uTwist` | 1.55 | How tightly the arms wind. |
| `uSize` | 0.024 | Base star size. |
| `uTime * omega * 0.32` | 0.32 | Spin speed. |
| `exp(-dist*dist/0.6)` | 0.6 | Width of the cursor well. |
| `uPulse * 4.2` | 4.2 | Shockwave speed. |
| `PALETTES` | 3 | Core / mid / rim colours. |

## 4. Where to use it

- Landing page hero: use the file in an `<iframe>`, or paste the `<canvas>` + script into your page. Give the canvas `position:absolute; inset:0` inside a relatively positioned hero.
- Next.js / React: see the "convert to React" prompt above. Import three dynamically and dispose geometry, material and renderer on unmount.
- WordPress / Webflow: embed as a Custom HTML block or an iframe.
- Keep the hero text outside the canvas so it stays indexable.

## 5. Quality checklist before you ship

- [ ] Test on a real mid-range phone (not just desktop devtools). It should hold 60 fps; if not, lower `COUNT`.
- [ ] Turn on "Reduce motion" in your OS: you must see one still frame.
- [ ] Disable WebGL or block the CDN: you must see the CSS fallback.
- [ ] Scroll the hero out of view and check that CPU/GPU drops to idle.
- [ ] Run Lighthouse; the canvas should not hurt LCP because the headline is plain text.

---

## 6. Complete source (single file, save as `index.html`)

````html
{{SOURCE}}
````

*Free from NextGenBeing. Questions? Reply to the email we sent you.*
