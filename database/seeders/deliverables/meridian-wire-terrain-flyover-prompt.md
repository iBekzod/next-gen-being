# Meridian - Wireframe Terrain Flyover (prompt + source)

An endless ridge line drawn in light. One static grid, a ridged-noise height field scrolled in the vertex shader, a solid occluder that hides lines behind peaks, a sky with a low sun and stars. The pointer banks the camera and bends the horizon. Zero per-frame CPU geometry work.

Included in this download: the master prompt (section 1), customisation prompts (2), the tuning table (3), and the full source file `scene.html` that sits next to this document. You may use it in client and commercial projects. Do not resell the prompt or source as a template pack.

---

## 1. Master prompt (paste into Claude, Cursor, v0 or Lovable)

```
Build a single self-contained HTML file: a full-viewport wireframe terrain flyover hero with three.js r160 loaded as an ES module from
https://cdnjs.cloudflare.com/ajax/libs/three.js/0.160.0/three.module.min.js (dynamic import() in try/catch).

GEOMETRY (one shared position attribute, two index buffers)
- Build ONE flat grid BufferAttribute: 132 columns x 100 rows (90 x 70 on touch), width 46, depth 78, row i at z = -i * (78/100).
- Geometry A: LineSegments index (horizontal + longitudinal edges) -> the glowing wireframe.
- Geometry B: triangle index over the same attribute -> a solid "occluder" mesh that hides lines behind ridges (polygonOffset 1,1).
- All height work is in the vertex shader. Shared GLSL terrain(p): zw = p.z + mod(uScroll, rowSpacing) so the grid rows travel with the terrain; nz = zw - uScroll is the noise coordinate;
  valley = smoothstep(2.2, 11, abs(x)) keeps a flat canyon floor in the middle; height = ridged 4-octave value noise (1 - abs(2n - 1))^2 summed, * 8 * valley^1.25, plus a little fine noise;
  x += uBend * zw*zw * 0.0042 so the horizon curves with the pointer; y = height - 3.1.
- Wire fragment (additive, transparent, depthWrite off): colour = mix(lowColour, highColour, pow(height/8.5, 1.4)) * (0.55 + 0.9 t), plus a slow light sweep
  pow(0.5 + 0.5 sin(nz*0.11 - time*0.55), 10) tinted with the sun colour; alpha fades with distance (smoothstep 14..70) and near the camera.
- Occluder fragment: dark ground colour that fades into the horizon colour with distance so the far edge melts into the sky.

SKY (full-screen quad, clip-space vertex shader, depthTest off)
- Vertical gradient horizon -> zenith, a low sun/moon glow on the horizon (anisotropic gaussian, narrower on portrait screens), hashed stars with twinkle that fade near the horizon, film grain.
- Pass the horizon's uv height from the camera pitch: 0.5 - 0.5 * tan(pitch) / tan(fov/2).

CAMERA AND INTERACTION
- fov 52 (66 on portrait), position (0, -0.2, 4.5), pitch +0.11 rad so the horizon sits in the lower half and the headline stays clear of the ridges.
- Pointer: smooth it (0.04). Pointer x moves the camera sideways, yaws and rolls it slightly and sets uBend = -x * 0.9. Pointer y pitches and lifts a little.
- After 3.5 s idle, drift the pointer on a slow sine path. Forward speed 3.4 units/s.
- Three palette swatches (Meridian ice/amber, Verdant mint/lime, Rose pink/violet): zenith, horizon, low line, high line and sun colours, lerped.

HERO COPY (placeholder): centered glass eyebrow, h1 "Fly low over your data.", one sentence, two buttons, top of the screen.

PRODUCTION RULES (all required)
1. Cap pixel ratio at 2 (1.5 on touch), antialias on.
2. Pause when off screen (IntersectionObserver), tab hidden, or when a host posts {ngbVisible:false}.
3. Adaptive quality: frame time above 22 ms for ~40 frames lowers the pixel ratio by 0.25 down to 1.
4. prefers-reduced-motion: render one still frame, no loop, no pointer response.
5. three.js import failure / no WebGL / context lost -> CSS-only fallback (gradient sky with a repeating-line floor).
6. ResizeObserver sizing; the h1 is real DOM text, canvas aria-hidden, controls are labelled buttons with visible focus.
7. No build step, no other dependencies.
```

---

## 2. Customise it

- "Replace the ridged noise with a data terrain: take an array of 60 values and draw them as ridges along the canyon walls, labelled with HTML tooltips."
- "Make it an ocean at night: gentle sine swell instead of ridges, moon reflection on the grid."
- "Add a glowing path down the valley that the camera follows, with tiny pulse lights moving along it."
- "Recolour for my brand (#0b1020 background, #22d3ee lines, #f59e0b sun) and slow the flight to 2 units/s."
- "Convert to a React component for Next.js with props for speed, colours and ridge height."
- "Link the forward speed to scroll velocity so the flyover accelerates when the user scrolls."

## 3. Tuning table

| Constant | Default | Effect |
|---|---|---|
| `COLS`, `ROWS` | 132 x 100 / 90 x 70 | Line density and vertex cost. |
| `SPEED` | 3.4 | Flight speed (units per second). |
| `* 8.0 * pow(valley, 1.25)` | 8.0 | Ridge height. |
| `smoothstep(2.2, 11.0, abs(x))` | 2.2 / 11 | Width of the flat canyon floor. |
| `p.x * 0.085`, `nz * 0.07` | 0.085 / 0.07 | Ridge size and stretch. |
| `uBend * ... * 0.0042` | 0.0042 | How much the horizon bends. |
| `smoothstep(14, 70, dist)` | 14 / 70 | Distance fade of the lines. |
| `pitch` | 0.11 | Where the horizon sits on screen. |
| `PALETTES` | 3 | Zenith, horizon, low, high, sun colours. |

## 4. Where to use it

- Full-bleed hero background: canvas `position:absolute; inset:0`, text above it in the DOM.
- Section divider or footer: lower the height and reduce `ROWS`.
- React / Next.js: client component, dynamic import, dispose geometries and renderer on unmount.

## 5. Quality checklist

- [ ] Real mid-range phone: should hold 60 fps. If not, lower `COLS` / `ROWS` or the pixel ratio cap.
- [ ] OS "Reduce motion" on: one still frame.
- [ ] WebGL off / CDN blocked: CSS fallback appears.
- [ ] Hero out of view: GPU goes idle.
- [ ] Headline is clear of the ridges at your breakpoints (adjust `pitch`).

The complete source is in `scene.html` in this download.

*Included with your purchase. Questions? Reply to your receipt email.*
