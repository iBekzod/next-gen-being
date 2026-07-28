# LinkFolio — Prompt Plan (build it with Claude Code)

Prompts to generate the striking one-page developer portfolio from the live demo. Pure HTML/CSS/JS, no build step.

**Stack:** single self-contained `index.html` (inline CSS + vanilla JS). Deployable to any static host.

---

## 1. Structure & tokens
> Create a single-file `index.html` developer portfolio. Define CSS custom properties for a deep plum-black background (`oklch(0.165 0.018 300)`), warm coral accent (`oklch(0.72 0.18 40)`), and off-white text. System font stack. Sections: sticky nav, hero, skills marquee, selected work, contact CTA, footer.

## 2. Animated hero
> Build the hero with an oversized display headline (clamp 2.6rem–5rem, letter-spacing -0.04em) where one word uses an outline text-stroke in the accent. Add a `<canvas>` behind it rendering 3 slow-moving radial-gradient blobs (a "gradient mesh") animated with requestAnimationFrame; respect `prefers-reduced-motion`.

## 3. Work grid + magnetic glow
> Add a filterable project grid (chips: All / Web / App / AI). Each card has a radial "glow" element that follows the cursor within the card on mousemove. Cards lift and border-accent on hover. Filtering toggles a `hidden` class.

## 4. Skills marquee & interactions
> Add an infinite horizontal marquee of skills (duplicate the track for a seamless loop, CSS keyframes). Wire a small toast system for the Hire/CV/email/social buttons. The contact email reveals + copies on click.

## 5. Ship
> Make it fully responsive (grid collapses to one column under 640px), add smooth-scroll nav, and ensure it works opened directly from the filesystem (no external requests).

---

**Tips**
- Keep everything in one file so a buyer can drop it anywhere.
- Swap the projects array and the name/links; the design does the rest.
- The canvas mesh is the "wow" — keep blob count low (3) for performance.

*Included with your purchase. Questions? Reply to your receipt email.*
