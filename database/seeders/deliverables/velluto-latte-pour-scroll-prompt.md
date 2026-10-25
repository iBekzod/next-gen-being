# Velluto Latte Pour (cafe) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, no libraries, no images, no build step. Fonts: Instrument Serif + Manrope from Google Fonts.

## 1. Scaffold
"Create ONE self-contained HTML file. A fixed full-screen <canvas> sits behind all content (z-index 0); sections and nav sit above it. Palette: espresso #160d08, mocha #2b1a10, latte #e6cfa8, foam #fbf1dc, ink #24150c. Add a `js` class on <html> so content stays visible if scripts fail. Smooth the scroll value in a requestAnimationFrame loop (cur += (scrollY-cur)*(1-exp(-dt*11))) and drive every canvas frame from it."

## 2. The drop and the splash (hero)
"Hero section 440svh tall with a sticky 100svh stage. Progress p = scroll / (heroHeight - stageHeight). p 0-0.16: a coffee ball hovers. 0.16-0.5: it falls with ease-in (t^2.1) and a slight vertical stretch. Draw the ball as a radial-gradient sphere plus ~46 specks given spherical coordinates and rotated each frame, so it really spins. At p=0.5 it hits a milk pool: a wave travels from the impact point, 54 droplets fly on ballistic paths (x = x0+vx*T, y = y0+vy*T+0.5gT^2, seeded random), a jet rises and falls, three ripple ellipses expand. Every effect is a pure function of p, so scrolling backwards plays it in reverse."

## 3. Bloom and flood
"After impact the ball sinks and fades under a semi-transparent milk layer. A radial coffee-cloud gradient grows inside the milk (clip to the milk path). From p=0.78 the surface rises above the top of the screen with easeInOut; the milk colour settles on the next section's background so there is no jump."

## 4. The milk stream
"Collect elements with data-s="l|r|end". Build a cubic-bezier polyline through their document positions (vertical tangents), store cumulative length. Draw it in the canvas with translate(0,-scrollY): shadow stroke, milk stroke, highlight stroke, plus beads travelling along it. The visible head follows scroll (cur + 0.64*innerHeight). On phones keep every waypoint on the left gutter. The last waypoint is the centre of the cup."

## 5. Section backgrounds
"Do not use CSS backgrounds. Read every [data-bg] section's top/bottom once on resize and paint each as a canvas band with a sine-wave top edge, so chapters meet like milk lines instead of hard cuts."

## 6. Make your latte (interactive)
"Top-view SVG cup: saucer, cup, liquid circle, foam art, drizzle ring, ice cubes, steam. State {flavour, milk, serve, size} -> CSS variables (--liq, --foam, --acc), toggle five latte-art groups (heart, rosetta, swirl, tulip, wings), show ice when iced, scale the cup for size, retrigger a ripple ring, swap the six floating ingredient icons, and lerp a canvas glow behind the cup toward the flavour accent. Radio-group buttons with aria-checked. Pointer position tilts the cup in 3D and shifts floating items by depth."

## Customisation prompts
- "Replace the coffee ball with a matcha ball and recolour the whole page to a green palette."
- "Swap the milk pool for hot chocolate and the ball for a marshmallow."
- "Add a sixth flavour, cardamom, with its own latte art and icon."
- "Add a Tashkent/Dubai/Berlin address, hours and your real menu; keep the placeholders removed."
- "Translate all copy to my language and keep the layout."

## Pre-launch checklist
- Open it at 400 px and 1440 px wide; nothing scrolls sideways.
- Turn on "reduce motion" in your OS: the hero shows a still ball and all content is visible.
- Replace every placeholder (brand, prices, address, hours, copy). Velluto is a fictional brand.
- The "Add to order" button only shows a message. Connect your own checkout before launch.
- Test on a real mid-range phone and in Safari; the template was checked in Chrome only.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts).
