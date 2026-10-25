# Velluto Latte Pour (cafe) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, no photos, no build step. three.js 0.160 (cdnjs, ES module + importmap) renders every object live; fonts are Fraunces + Inter from Google Fonts.

## 1. Scaffold and layering
"Create ONE HTML file. A fixed full-screen transparent WebGL canvas (z-index 3, pointer-events none). Section backgrounds sit below it; text and cards sit above it (z-index 6). For pinned sections use TWO sticky 100vh layers in the same section: a background layer (colour, waves, the centre card) and a foreground layer with margin-top:-100vh and z-index 6 (titles, side cards). Palette: cream #f3ede4, ink #2a1d16, terracotta #b46c4a, sand #e6d3c1. Header: two-line serif logo, centred nav with underlined active link, pill search, dark pill Sign Up, burger; fixed dark chat bubble bottom-right."

## 2. 3D objects follow DOM anchors
"Every 3D object is bound to an empty absolutely positioned <span class=anc>. Each frame read its getBoundingClientRect and convert the centre to world units at z=0 (k = 2*camZ*tan(fov/2)/innerHeight); the anchor width sets the object's scale. All responsive layout is then plain CSS. Use RoomEnvironment through PMREMGenerator for reflections, ACES tone mapping, MeshPhysicalMaterial with clearcoat for glossy coffee, caramel and porcelain."

## 3. Hero
"Huge uppercase Fraunces 800 headline on the left, a glossy coffee sphere (vertical caramel-to-espresso gradient texture) floating in the middle with small chocolate balls orbiting it and a soft shadow ellipse below, a rounded caramel cube and a porcelain ring floating around, two product cards on the right (rendered once from small three.js scenes to JPEG data URLs), pagination dots."

## 4. Milk section (pinned, 420vh)
"Progress m. 0-0.2: the sphere falls from above the title into a cup of milk shown in the centre card. 0.2-0.4: the card fades and a milk pool grows from that point: a polar grid mesh (84 rings x 144 segments) displaced every frame with a crown (gaussian ring modulated by sin(11*theta)^4, droplets on the peaks), travelling ripples and an edge alpha fade. The sphere floats half-submerged. 0.66-1: the centre dips into a funnel, the sphere rises and a milk stream (open cylinder) runs from the funnel down to the latte cup in the next section."

## 5. Make your latte
"Terracotta section with cream corner circles (one with circular textPath text). Lathe-geometry speckled cup with a torus handle and a latte-art disc (canvas-drawn tulip hearts); it fills as the section slides in while the stream thins out. Vanilla flower (five bent ShapeGeometry petals + tube pods), broken chocolate bar (rounded boxes), caramel ribbon (TubeGeometry along a zigzag spiral). Labels VANILLA / CARAMEL / MOCHA are buttons: clicking one repaints the crema colour and pulses its ingredient."

## 6. Make it yours
"Chips for flavour, milk, temperature and size update a summary card (name, note, sample price). A three-item menu and a footer with placeholder address."

## Customisation prompts
- "Replace the coffee sphere with a matcha sphere and recolour the page green."
- "Swap the milk for hot chocolate and add a marshmallow."
- "Add a fourth flavour, cardamom, with its own 3D ingredient and crema colour."
- "Put my real menu, address and hours in; remove all placeholders."
- "Translate all copy to my language and keep the layout."

## Pre-launch checklist
- Open it at 400 px and 1440 px wide; nothing scrolls sideways.
- If WebGL is unavailable the page stays readable (CSS sphere and cup fallback).
- Replace every placeholder (brand, prices, address, hours, copy). Velluto is a fictional brand.
- "Add to order" does nothing until you connect your own checkout.
- Test on a real mid-range phone and in Safari; the template was checked in desktop Chrome only.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts); three.js is MIT.
