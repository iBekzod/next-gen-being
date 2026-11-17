# Sartor (dark bespoke atelier) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, GSAP 3.12.5 + ScrollTrigger from cdnjs, SVG art, no photos, no build step.

## 1. Scaffold
"Create ONE self-contained HTML file with GSAP 3.12.5 + ScrollTrigger from cdnjs and Bodoni Moda + Manrope from Google Fonts. Dark palette: ink #0b0a0c, charcoal #141216, gold #c9a45c, bone #efe7da, wine #6b2434. Sections: hero with a gold thread that draws itself, marquee, pinned pattern-to-jacket stage, pinned horizontal cloth room, live jacket configurator, timeline with counters, appointment form."

## 2. Pattern pieces assemble into a jacket
"One SVG viewBox 800x860. Define the jacket as 10 pieces in a JS array (two fronts, two sleeves, two lapels, collar, two pocket flaps, a lining strip); right-hand pieces reuse the left path inside a group with transform translate(800 0) scale(-1 1). Each piece has a centre and a scatter offset (dx, dy, rotation). Pin the section with ScrollTrigger (end = innerHeight * 3.4, scrub 0.6). In onUpdate compute each piece's progress with a stagger and write a transform ATTRIBUTE (translate, rotate around its centre, scale), not GSAP svgOrigin. Early on pieces are dashed gold outlines; later the fill opacity rises to 1 and the dashes disappear. Then fade in stitch lines and pop the buttons in one by one. Show a live stitch counter and a step list that highlights the current step."

## 3. Cloth swatches without images
"Define four SVG <pattern> fills in a hidden svg (pinstripe, herringbone, glen check, diagonal twill). Use them as swatch cards AND as the jacket fill, so one id drives both."

## 4. Horizontal cloth room
"Pin a section and translate a flex track by -(scrollWidth - innerWidth) with scrub. Wrap it in gsap.matchMedia for min-width 821px; below that the cards stack vertically with no pin."

## 5. Configurator
"Chip groups for cloth, lining, buttons plus a 3-letter monogram. Keep one state object. On change, re-fill the preview jacket SVG, update the quote (base + extras, formatted with toLocaleString) and the lead time. Show line items under the price."

## 6. Hero thread
"An SVG path with a gold gradient stroke. Set stroke-dasharray to its length, tween the offset to 0 and move a small circle with getPointAtLength in onUpdate. Afterwards switch to a dashed stroke."

## Customisation prompts
- "Swap the jacket for a coat, shirt or dress by changing the piece array."
- "Make a light cream version with navy and brass accents."
- "Add a fifth cloth and a size selector."
- "Translate all copy to my language."

## Pre-launch checklist
- Open it at 390 px and 1440 px; nothing scrolls sideways.
- Enable reduce motion in your OS: the finished jacket and all content must show without pinned effects.
- Replace brand, prices, address and copy; wire the form to your own backend (the template sends nothing).
- Run Lighthouse on mobile.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts), GSAP is loaded from cdnjs under its standard free licence.
