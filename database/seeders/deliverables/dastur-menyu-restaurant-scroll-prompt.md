# Dastur (restaurant) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code. One HTML file, GSAP 3.12.5 + ScrollTrigger from cdnjs, SVG art, no photos, no build step.

## 1. Scaffold
"Create ONE self-contained HTML file with GSAP 3.12.5 + ScrollTrigger from cdnjs and Cormorant Garamond + Manrope from Google Fonts. Palette: indigo #0a1b3f, cobalt #1c4e9a, cream #f6efe0, gold #e3a93b, pomegranate #b3263e. Sections: hero with a rotating girih-star medallion, pinned table story, tabbed menu, booking, footer."

## 2. The laid table
"A pinned section (6.6 viewport heights, scrub 0.7). Stage on the right: one SVG viewBox 800x800 with a camera group #cam. An embroidered cloth (rings of petals) unfolds, then five dishes land with back.out easing: plov centre, bread top, somsa right, shashlik bottom, tea left. Then for each dish the camera tweens x, y, scale so the dish is centred (x = -scale*(px-400)). Six story cards on the left crossfade. Clip the stage with overflow hidden so the zoom never covers the text."

## 3. Dishes as SVG (no photos)
"Generate each dish with a seeded random function: rice grains as tiny rotated ellipses, carrot sticks, chickpeas, raisins, meat, quail eggs and a garlic head on a plate with a cobalt petal rim; bread with stamped dimples and sesame; three somsa triangles; skewers with cubed meat, onion rings and tomato; a copper tray with teapot, two bowls and rock sugar."

## 4. Performance rule
"Keep the whole table under about 1500 SVG nodes and use radial-gradient shadows, never feGaussianBlur filters: a filter on a scaled SVG group drops the frame rate to 15 fps."

## 5. Menu and booking
"Four menu tabs rendered from a JS object with dotted leaders and prices. Booking widget: date, guest stepper, time chips (one disabled), name, and a client-side confirmation message only."

## Customisation prompts
- "Make it an Italian trattoria: replace the dishes with pizza, pasta, antipasti and tiramisu."
- "Switch to a light theme with a terracotta palette."
- "Add a fifth camera stop for desserts."
- "Add a drinks tab with a wine list."
- "Show a real photo for each dish in the story card, keeping the SVG table."
- "Translate all copy to my language."

## Pre-launch checklist
- Open it at 390 px wide and at 1440 px wide; nothing scrolls sideways.
- Turn on "reduce motion" in your OS: the page must show all content without the pinned animation.
- Run Lighthouse on mobile: keep LCP under 2.5 s (the hero text is the LCP element, so keep the font swap on).
- Replace every placeholder (brand, prices, address, copy). Brand names in this template are fictional.
- Wire the buttons and forms to your own backend or checkout; the template sends nothing.

## Licence
Use in your own and your clients' projects. Do not resell the prompt or the source as a template pack. Fonts are OFL (Google Fonts), GSAP is loaded from cdnjs under its standard free licence.
