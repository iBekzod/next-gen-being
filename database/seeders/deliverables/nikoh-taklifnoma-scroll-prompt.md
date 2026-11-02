# Nikoh - Online Wedding Invitation (scroll website) - Build and Customise Prompts

Prompts to rebuild or personalise this invitation with Claude Code. One HTML file (about 1.1 MB): GSAP 3.12.5 + ScrollTrigger from cdnjs, Cormorant Garamond, Great Vibes and Jost from Google Fonts. Ten sample photos and the music loop are embedded in the file.

The demo shows placeholder names (Zarina and Jasur), a placeholder date and a placeholder Tashkent venue. Everything personal sits in one `CONFIG` object at the top of the `<script>`.

## 1. Change the couple, date, venue and program (2 minutes)
"Open the HTML file. In CONFIG set bride to 'Malika', groom to 'Otabek', date to 2027-09-18T17:00:00+05:00. Venue name 'Registon Hall', address 'Samarkand, Registon street 5', lat 39.6547 lng 66.9758. Program: 15:00 Nikoh, 16:30 Ziyofat, 18:30 Cake, 19:30 Dancing. Edit the story, notes and wishes. Keep the Uzbek and Russian text in sync. Update the <title> and the og: and twitter: meta tags in <head> to the same names."
- Texts for both languages live in the `I18N` table (`uz`, `ru`). Add a third language by copying one block and adding a button in `.lang`.
- Uzbek Latin letters: use the modifier letter `ʻ` (U+02BB) in oʻ and gʻ, and `ʼ` (U+02BC) for the tutuq belgisi. The page shrinks very long names to fit; Great Vibes covers Latin and Cyrillic.
- `showPaletteDial: false` hides the palette switcher on a real invitation. Delete nothing else from the demo; there are no demo labels on the page.

## 2. Personal links per guest
`...?g=Aziz-aka&n=2` greets "Hurmatli Aziz aka" on the first screen, on the door seal and in the RSVP form, and shows "Sizni 2 kishi bilan kutamiz". Use `-` or `+` for spaces. Add `&lang=ru` or `&theme=osmon` as needed. The Share button always shares the clean link without the guest name.

## 3. Photos (real couple photos, swap in two minutes)
Slots in `CONFIG.photos`: `hero` (tall portrait about 3:5, faces in the upper half), `story` (3 photos, 4:5), `wide` (16:9), `gallery` (4 photos, 4:5), `closing` (16:9). An empty string uses the bundled sample. Set a path or URL to use your own, for example `hero: "photos/hero.webp"`. Export WebP, 900-1000 px wide for the hero, 700-800 px for the rest, under 150 KB each (page ideally under 3 MB). Delete the `window.__EMB` script at the bottom once all slots point to your files.
Prompt: "Replace the sample photos with the files in ./photos (hero.webp, story1-3.webp, wide.webp, g1-4.webp, closing.webp). Crop to the ratios in CONFIG and compress to WebP under 150 KB."
Without any photo the hero would be empty: if you have no photos yet, ask Claude for a monogram hero (big initials on the palette colour) rather than stock faces.

### Sample photo sources and licence
All ten samples are from Pexels. The Pexels licence allows free commercial use without attribution; do not use them to imply the people endorse a product. Replace them with your own couple before real use. Samples were cropped and recompressed to WebP.
| Slot | Source |
| --- | --- |
| hero | https://www.pexels.com/photo/portrait-of-a-wedding-couple-18398510/ (POI) |
| story 1 | https://www.pexels.com/photo/couple-embracing-each-other-8039883/ (Melike B) |
| story 2 | https://www.pexels.com/photo/crop-happy-newlywed-couple-embracing-on-wedding-day-4431913/ (Jonathan Borba) |
| story 3 | https://www.pexels.com/photo/couple-facing-each-other-3361200/ |
| wide | https://www.pexels.com/photo/couple-standing-on-grass-field-while-looking-at-each-other-1603884/ |
| gallery 1 | https://www.pexels.com/photo/joyful-wedding-couple-embracing-in-scenic-landscape-34122800/ |
| gallery 2 | https://www.pexels.com/photo/a-bride-and-groom-walking-through-the-desert-15861988/ |
| gallery 3 | https://www.pexels.com/photo/hands-of-bride-and-groom-18980081/ |
| gallery 4 | https://www.pexels.com/photo/close-up-of-bride-and-groom-holding-hands-15784736/ |
| closing | https://www.pexels.com/photo/woman-and-man-face-to-face-1247756/ (Edward Eyer) |
Licence text: https://www.pexels.com/license/

## 4. Palettes
Four palettes (zaytun olive and ivory with warm gold, zumrad, osmon dusty blue, bordo) are CSS variables at the top of the stylesheet, including the five dress-code swatches `--sw1..--sw5`. `?theme=osmon` sets one by link; `CONFIG.theme` sets the default. To add one: "Add a palette 'anor': copy the bordo block, change every variable including --sw1..5, add a swatch button and the name to the THEMES list."

## 5. RSVP that works without a backend
On submit the page builds a prefilled message ("Assalomu alaykum! Men Aziz. Zarina va Jasur toʻyiga kelaman. Mehmonlar soni: 2.") and opens your Telegram (`t.me/<user>?text=`) or WhatsApp (`wa.me/<number>?text=`) chat, then shows a confirmation screen with the message and buttons to open it again. The guest still has to press Send in the chat, which the screen says. Set in CONFIG: `rsvp: { via: "telegram", telegram: "your_username", whatsapp: "998901234567" }`. You see answers in your own chat.
For a database or sheet instead: "In the submit handler of #rsvpForm also POST name, count, yes/no and wish as JSON to <my Apps Script / webhook URL>, keep the Telegram step as a fallback."

## 6. Map, taxi and calendar
Set `venue.lat` and `venue.lng`. The embedded Google map and the buttons Google Maps, Yandex Maps, 2GIS and Taksi (Yandex Go deep link) are built from them. `venue.googleUrl` overrides the Google button. The "Kalendarga qoʻshish" button downloads an .ics file (date, duration `durationHours`, venue, a 1-day reminder). Share uses the phone share sheet and falls back to copying the link.

## 7. Link preview (Telegram, WhatsApp)
Crawlers do not run JavaScript, so edit the static tags in `<head>`: `og:title`, `og:description`, `og:image` (1200x630 JPG generated from the hero, absolute URL), `og:url`, the twitter tags and `apple-touch-icon`. Host the image next to your page and use its absolute URL. After publishing, paste the link into Telegram to check the preview (Telegram caches; add `?v=2` to refresh).

## 8. Music
Browsers block autoplay, so music starts on the opening tap; the round button (top right) mutes it. Bundled: an original 30-second synthesized loop. Your own: set `CONFIG.musicUrl` to a hosted mp3 (under 500 KB, mono, 48 kbps). Use only music you have rights to.

## 9. How the motion works (for restyling)
- Doors: two 3D-rotating halves with an arch border, one GSAP timeline on tap. Names are written with a clip-path wipe with generous overhang so script swashes (the Z of Zarina) are never cut.
- Hero: photo parallax (translate/scale) while the text drifts up; petals are CSS-animated spans in front of the photo and pause when off screen.
- Story: one pinned screen, three photos crossfade with a slow push-in while the captions change.
- Countdown ring fills as it scrolls into view; the program line draws with scroll; gallery photos settle with a scroll-linked zoom.
- Only transform and opacity are animated (plus one stroke-dashoffset for the ring). Images are lazy-loaded except the hero.
- Fallbacks: without GSAP (blocked CDN) or with prefers-reduced-motion the page is a plain scroll with everything visible and no petals.

## Pre-launch checklist
- Replace names, date, venue, coordinates, program, story, notes, wishes, contact phone, photos and RSVP username. Edit the og: tags and title.
- Replace the sample wishes with real ones or remove the section.
- Test on a real phone (iOS Safari and Android Chrome): opening, music, RSVP link, share, calendar file.
- Host the file on any static hosting (Netlify, Cloudflare Pages, GitHub Pages) and send each guest a link with `?g=Name&n=2`.
