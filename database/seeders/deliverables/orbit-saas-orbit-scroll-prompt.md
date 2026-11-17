# Orbit (light SaaS landing) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, GSAP 3.12.5 + ScrollTrigger from cdnjs, no images, no build step.

## 1. Scaffold
"Create ONE self-contained HTML file with GSAP 3.12.5 + ScrollTrigger from cdnjs and Sora + Inter from Google Fonts. Light palette: bg #f6f5ff, ink #14122b, indigo #5b4bff, violet #9b6bff, coral #ff7a59, mint #16c79a. Sections: floating pill nav, hero with blurred blobs and a tilting dashboard mock, pinned orbit-to-inbox stage on a dark panel, bento feature grid, counters, pricing with monthly/yearly toggle, ROI calculator, FAQ, gradient CTA."

## 2. Orbit that settles into an inbox
"Pin a dark section (end = innerHeight * 3.2). Create 8 coloured rounded-square nodes (generic names: Chat, Mail, Calendar, Code, Payments, Design, Docs, Tickets) on two dashed rings of different radius. Write one function renderOrbit(progress): for progress 0 to 0.55 the rings spin in opposite directions; from 0.5 each node eases from its orbit position to a row slot inside a white 'Unified inbox' card (position = card left + 40, card top + 82 + index * 40) and scales to 0.58, while the core shrinks and fades and row labels slide in with a stagger. Set transforms directly in onUpdate; measure the stage on refresh."

## 3. Dashboard tilt
"Start the dashboard at rotationX 14 and scrub it flat as it enters. On mousemove tilt up to 6 degrees; reset on mouseleave."

## 4. Pricing toggle
"A PLANS array with monthly and yearly prices. A two-button pill with a sliding indicator. On toggle, tween each price number with gsap and update the ROI calculator, which reads the Team yearly price."

## 5. ROI calculator
"Three range inputs (team size, tenths of an hour saved per person per week, hourly cost). Value = people * hours * 48 * rate; cost = people * team yearly price * 12; show the multiple. Colour the range track with a --p custom property."

## 6. Bento details
"Toggle switches that really toggle, bars that grow when scrolled into view, a typed search query that cycles two questions, a small code block."

## Customisation prompts
- "Swap the eight nodes for tools in my industry."
- "Make a dark version of the hero and keep the orbit panel."
- "Add a customer logos strip and a testimonial slider."
- "Translate all copy to my language."

## Pre-launch checklist
- Check 390 px and 1440 px; no sideways scroll.
- Reduce motion shows the finished inbox and all content.
- Replace the fictional name, prices and figures; connect the email form to your own tool (it sends nothing).
- Run Lighthouse on mobile.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts), GSAP is loaded from cdnjs under its standard free licence.
