# Liquid Glass - Morphing Blob Hero (prompt + source)

A single shader-driven glass blob over a living gradient: it breathes on its own, leans toward the cursor, pushes back when pressed, and refracts the background with a hint of dispersion. Two draw calls, no render targets, so it stays cheap on phones.

Included in this download: the master prompt (section 1), customisation prompts (2), the tuning table (3), and the full source file `scene.html` that sits next to this document. You may use it in client and commercial projects. Do not resell the prompt or source as a template pack.

---

## 1. Master prompt (paste into Claude, Cursor, v0 or Lovable)

```
Build a single self-contained HTML file: a full-viewport "liquid glass" blob hero with three.js r160 loaded as an ES module from
https://cdnjs.cloudflare.com/ajax/libs/three.js/0.160.0/three.module.min.js (dynamic import() in try/catch).

SCENE (two meshes, two draw calls, no post-processing, no render targets)
1. Background: a full-screen quad (PlaneGeometry(2,2), vertex shader writes clip-space directly, depthTest off, renderOrder -1).
   Its fragment shader calls bgColor(uv): a dark base colour plus three soft radial colour fields that drift slowly (smoothstep falloff),
   a vignette, and film grain in the quad only. Defaults: base #0d0716, fields #ff8a5c, #d63ca0, #34bec8. Put bgColor() in a shared GLSL string
   because the blob reuses it.
2. Blob: IcosahedronGeometry(1, 56) (36 on touch). Custom ShaderMaterial.
   - Vertex: displace each vertex along its normal by field(p) = (noise3(p*1.25 + t) * 0.64 + noise3(p*2.6 - t) * 0.3 - 0.47) * amp + bulge,
     using a small hash-based 3D value noise with quintic interpolation (no textures). Recompute the normal analytically by displacing two
     neighbouring points along tangent vectors (eps 0.025) and taking the cross product, so there are no flat-shading seams.
     bulge = pow(max(dot(p, uDir), 0), 5) * uBulge where uDir is the direction toward the cursor.
   - Fragment: fake glass. Refract by sampling bgColor() at screen uv minus N.xy * (0.12 + 0.36 * (1 - N.V)^1.4), one sample per RGB channel with
     offsets 1.00 / 1.07 / 1.15 for dispersion. Darken and enrich the body (col*0.5 + col*col*0.35), add an environment reflection
     bgColor(reflect(-V,N).xy*0.5+0.5) * fresnel, thin-film iridescence 0.5+0.5*cos(2PI*(vec3(0,.33,.67) + N.V*1.3 + time*0.03 + p.y*0.25))
     on the edges, two Blinn-style key lights (sharp glint pow 70 plus a broad pow 9 sheen), a caustic glow on the lit-away side, and a bright rim.

MOTION AND INTERACTION
- Idle: morph with time, amp 0.34. Camera fov 34 at z = 7.
- Pointer: compute the pointer direction relative to the blob's on-screen centre (project the blob position), build uDir, ease it (0.08). Bulge strength 0.05 idle, up to 0.25 when the pointer is active. The background fields also shift slightly with the pointer.
- Press / tap: a "punch" value that decays with pow(0.04, dt), adds up to +0.22 to amp with a fast wobble and +5% scale.
- After 3.5 s without input wander the pointer on a Lissajous path so touch screens stay alive.
- Layout: blob on the right on wide screens (scale 1.12), upper centre on phones (scale = min(1, 1.37 * aspect)), text left / bottom.
- Three palette swatches (dusk, lagoon, ink) that lerp the four colour uniforms.

HERO COPY (placeholder): glass eyebrow chip, h1 "Interfaces that feel alive.", one sentence, primary + secondary button. Add a dark gradient behind the text for contrast (left on desktop, bottom on mobile).

PRODUCTION RULES (all required)
1. Cap pixel ratio at 2 (1.5 on touch).
2. Pause when off screen (IntersectionObserver), when the tab is hidden, or when a host page posts {ngbVisible:false}.
3. Adaptive quality: if frame time stays above 22 ms for ~40 frames, step the pixel ratio down by 0.25 to a minimum of 1.
4. prefers-reduced-motion: draw one still frame, no loop, no pointer response.
5. If three.js fails to load, WebGL is unavailable or the context is lost, show a CSS radial-gradient fallback.
6. ResizeObserver for sizing. The h1 is real DOM text; the canvas is aria-hidden; controls are real buttons with labels and focus rings.
7. No build step and no other dependencies.
```

---

## 2. Customise it

- "Make the blob metallic chrome instead of glass: remove dispersion, reflect a studio-style gradient environment."
- "Turn the single blob into three smaller blobs that merge when the cursor is between them."
- "Make the blob react to audio: take a microphone/analyser level and drive amp and bulge from the low frequencies."
- "Swap the palette to my brand colours #111827, #f97316, #22d3ee, #a78bfa and keep text contrast AA."
- "Convert to a React Three Fiber component with props for colours, amplitude and detail."
- "Add scroll linkage: the blob moves from the hero to the pricing section and shrinks as the page scrolls."

## 3. Tuning table

| Constant | Default | Effect |
|---|---|---|
| `IcosahedronGeometry(1, 56)` | detail 56 / 36 | Smoothness vs vertex cost. |
| `uAmp` base | 0.34 | How wobbly the blob is. |
| `p * 1.25`, `p * 2.6` | 1.25 / 2.6 | Size of the large and small lumps. |
| `uTime * 0.32` | 0.32 | Morph speed. |
| `0.12 + 0.36 * ...` | 0.12 / 0.36 | Refraction strength (center / edges). |
| `1.00 / 1.07 / 1.15` | dispersion | Colour fringing at edges. |
| `fres * 0.65` | 0.65 | Iridescent rim strength. |
| `PALETTES` | 3 | Base + three light colours. |

## 4. Where to use it

- Hero background or hero side-art: absolute-position the canvas in a relatively positioned section; keep your headline as plain DOM text above it.
- Next.js / React: use a client component, import three dynamically, dispose on unmount.
- Webflow / WordPress / Framer: embed as an iframe or custom HTML.

## 5. Quality checklist

- [ ] Real mid-range phone: should hold 60 fps. If not, lower the icosahedron detail or the pixel ratio cap.
- [ ] OS "Reduce motion" on: one still frame.
- [ ] WebGL off / CDN blocked: CSS fallback appears.
- [ ] Hero out of view: GPU goes idle.
- [ ] Text contrast: check the lead paragraph against your palette (AA).

The complete source is in `scene.html` in this download.

*Included with your purchase. Questions? Reply to your receipt email.*
