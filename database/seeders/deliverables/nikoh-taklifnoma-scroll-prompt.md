# Nikoh - Online Wedding Invitation (scroll website) - Build and Customise Prompts

Prompts to rebuild or personalise this page with Claude Code. One HTML file (about 0.9 MB): GSAP 3.12.5 + ScrollTrigger from cdnjs, Cormorant Garamond, Great Vibes and Jost from Google Fonts. The three arch backgrounds and the music track are embedded in the file.

## 1. Change the couple, date, venue and program (2 minutes)
Everything personal sits in the `CONFIG` object at the top of the `<script>`. Tell Claude:
"Open the HTML file. In CONFIG set bride to 'Malika', groom to 'Otabek', date to 2027-09-18T17:00:00+05:00. Venue name 'Registon Hall', address 'Samarkand, Registon street 5', mapUrl = <my Google Maps link>. Program: 15:00 Nikoh, 16:30 Ziyofat, 18:30 Cake, 19:30 Dancing. Keep the Uzbek and Russian text in sync."
- Texts for both languages live in the `I18N` table (`uz`, `ru`). Edit both or add a third language by copying one block and adding a button in `.lang`.
- Uzbek Latin letters: use the modifier letter `ʻ` (U+02BB) in oʻ and gʻ, and `ʼ` (U+02BC) for the tutuq belgisi.
- Personal link per guest: `...?to=Alisher` shows "Hurmatli Alisher" on the first screen. Add `&lang=ru` or `&theme=bordo` as needed.

## 2. Palettes
Three palettes (zumrad, osmon, bordo) are CSS variables at the top of the stylesheet plus one background image each. The small dial at the bottom left switches them; `?theme=osmon` sets one by link. To make a fourth:
"Add a palette 'anor' (pomegranate): copy the bordo block, change --paper, --ink, --gold, --deep, --p1..p3 and --bg. Add a swatch button and the name to the theme list in the script."

## 3. Your own backgrounds (optional, Higgsfield or any image tool)
The arch frame is a portrait image (about 9:16) with an empty cream centre. The page puts all text in the middle 60 percent of it. Prompt used for this style:
"Premium wedding invitation background, portrait 9:16, an ornate pointed arch frame with Uzbek suzani gold embroidery in emerald green, big cream and blush peonies and roses in the top corners and a few petals at the bottom, a large empty cream paper area inside the arch, soft window light, no text, no people."
Swap colours for sky blue (osmon) or burgundy (bordo). Compress before embedding: 900 px wide, WebP quality about 58, around 100-220 KB each. The build script `demos-src/nikoh/build.py` in the repository embeds `bg_*.webp` and `music.mp3` into `template.html`.

## 4. Music
Browsers block autoplay, so music starts when the guest taps to open the doors; a round button (top right) mutes it. The bundled track is an original 30-second synthesized piano-and-pad loop. To use your own:
"Replace the audio source with my mp3 (under 500 KB, mono, 48 kbps) or set CONFIG.musicUrl to a hosted file. Keep the tap-to-start behaviour."
Only use music you have the rights to.

## 5. Make the RSVP form real
The form is a front-end placeholder: it shows a thank-you message and sends nothing. To connect it:
"In the submit handler of #rsvpForm, POST name, guest count, yes/no and wish as JSON to <my endpoint> (a Telegram bot webhook, Google Apps Script, Formspree, or my own server). Show an error message if the request fails and keep the Uzbek and Russian texts."

## 6. The map
The map is a drawn placeholder. Paste a Google or Yandex Maps link into `CONFIG.venue.mapUrl` and the button opens it. For an embedded map, add the provider's iframe inside `.map` and keep the pin animation off.

## 7. How the animation works (for restyling)
- Doors: two 3D-rotating halves with an arch border, opened by one GSAP timeline on tap.
- Hero: a 3.4-screen pinned ScrollTrigger timeline with scrub. Flower clusters slide apart and open (each flower is four stacked SVG rings scaled with CSS transforms, no SVG origins), names are written with a clip-path wipe so the script letters stay joined, then date and countdown appear.
- Below: scroll reveals, a gold line that draws down the program, a pin that drops on the map, petals that fall only while the closing section is visible.
- Fallbacks: without GSAP (blocked CDN) or with prefers-reduced-motion the page is a plain scroll with everything visible and a simple fade for the doors.

## Pre-launch checklist
- Replace every placeholder (names, date, venue, program, map link, texts) and delete the demo footer line.
- Connect the RSVP form to your own receiver, or remove it.
- Test on a real phone (iOS Safari and Android Chrome) and with the volume button before sending links.
- Host the file on any static hosting (Netlify, Cloudflare Pages, GitHub Pages) and send each guest a link with `?to=Name`.
