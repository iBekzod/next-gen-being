# Gulzor (florist and wedding) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, GSAP 3.12.5 + ScrollTrigger from cdnjs, SVG art, no photos, no build step.

## 1. Scaffold
"Create ONE self-contained HTML file with GSAP 3.12.5 + ScrollTrigger from cdnjs and DM Serif Display + DM Sans from Google Fonts. Palette: ivory #fff9f3, blush #f8e4e2, rose #e48aa3, peony #d3607c, plum #3a1f2e, sage #86a287. Sections: pinned bloom hero, bouquet cards, bouquet builder, pinned wedding arch, delivery steps."

## 2. The blooming peony
"An SVG viewBox 800x800 with four layers of teardrop petals (15, 12, 9, 6) around (400,400) inside a translate(400 400) group, plus seven sage leaves behind. Do NOT tween each petal with GSAP svgOrigin. Drive ONE progress value in a scrubbed tween and, in onUpdate, set each petal's transform attribute to rotate() scale() computed from that value, with a stagger per layer. Closed bud: petals nearly vertical and narrow. Open: fanned around the circle. Show a live percent-in-bloom counter and fade stamens in at the end."

## 3. Generated flowers
"A flower(cx, cy, R, palette, seed) function makes three petal rings and a butter-yellow centre. Reuse it for the logo, bouquet cards, the builder preview and the arch. Place flowers in a bouquet with a phyllotaxis spiral (angle i*137.5 degrees, radius sqrt(i)) and draw a paper cone and bow under them."

## 4. Builder
"Colour swatches, size (7, 15, 25 stems), paper (kraft, ivory, black) and a card message. Redraw the SVG on every change with a quick scale-in, and compute the price live (size price + paper surcharge)."

## 5. Wedding arch
"A pinned section: an SVG arch path with 40 flowers and leaves placed along a semicircle plus two pillars. Scrub their scale from 0 to 1 in order of position so the arch grows from left to right. Set each flower group's svgOrigin from data-x / data-y attributes."

## Customisation prompts
- "Make it a minimal sage-green and white palette."
- "Add a fifth bouquet and a seasonal filter."
- "Add a gift-note step with a printable card preview."
- "Replace the peony with a rose or a sunflower."
- "Add delivery date and postcode checks."
- "Translate all copy to my language."

## Pre-launch checklist
- Open it at 390 px wide and at 1440 px wide; nothing scrolls sideways.
- Turn on "reduce motion" in your OS: the page must show all content without the pinned animation.
- Run Lighthouse on mobile: keep LCP under 2.5 s (the hero text is the LCP element, so keep the font swap on).
- Replace every placeholder (brand, prices, address, copy). Brand names in this template are fictional.
- Wire the buttons and forms to your own backend or checkout; the template sends nothing.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts), GSAP is loaded from cdnjs under its standard free licence.
