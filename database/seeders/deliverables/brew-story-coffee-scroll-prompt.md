# Tamber Brew Story (cafe) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, GSAP 3.12.5 + ScrollTrigger from cdnjs, SVG art, no photos, no build step.

## 1. Scaffold
"Create ONE self-contained HTML file. Load GSAP 3.12.5 and ScrollTrigger from cdnjs and Fraunces + Inter from Google Fonts. Palette: espresso #1b110a, crema #e8d2ae, cream #f5ebd9, caramel #c98a43. Sections: hero, pinned story, bean cards, brew guide tabs, visit footer. Add a `js` class on <html> so content stays visible if scripts fail."

## 2. The pinned story
"One section, height 100svh, pinned for 5 viewport heights with scrub 0.7. Left: a giant word (Grind, Pour, Sip) that slides out and in, a caption per chapter, and a readout (grams, time, water temperature). Right: one SVG scene, viewBox 600x660, with three groups: a glass hopper grinder, a V60 dripper with kettle, and a cup on a saucer. Build the whole thing as ONE gsap timeline with chapters at t=0, 1, 2. Tween the section's --bg and --fg CSS variables (use rgb() strings) from dark roast to crema."

## 3. SVG details that sell it
"Scatter 60 bean <use> elements in the hopper (seeded random), scale the pile down as grounds rise in the jar. Tilt the kettle and scale a thin rect for the water stream. Raise the brew level with scaleY from the bottom. Latte art is a heart path that fades in. Steam is three dashed paths animated with stroke-dashoffset in CSS."

## 4. SVG origins
"Use gsap.set with svgOrigin in viewBox units for every rotated or scaled SVG part. Never rely on CSS transform-origin for SVG."

## 5. Cards, guide, mobile
"Three bean cards with SVG bags, tasting-note chips and an add-to-cart button that says Added. A tabbed brew guide (V60, French press, espresso) with a stats grid. At 860 px and below: stage on top 42%, copy below, nav gets a solid blurred background."

## Customisation prompts
- "Change the palette to a matcha green theme, keep contrast AA."
- "Replace the V60 chapter with an espresso machine pulling a shot."
- "Add a fourth chapter, Roast, with a drum and beans changing colour."
- "Swap the fonts for a geometric sans and a monospaced readout."
- "Add a subscribe-and-save toggle to the bean cards."
- "Translate all copy to my language and keep the layout."

## Pre-launch checklist
- Open it at 390 px wide and at 1440 px wide; nothing scrolls sideways.
- Turn on "reduce motion" in your OS: the page must show all content without the pinned animation.
- Run Lighthouse on mobile: keep LCP under 2.5 s (the hero text is the LCP element, so keep the font swap on).
- Replace every placeholder (brand, prices, address, copy). Brand names in this template are fictional.
- Wire the buttons and forms to your own backend or checkout; the template sends nothing.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts), GSAP is loaded from cdnjs under its standard free licence.
