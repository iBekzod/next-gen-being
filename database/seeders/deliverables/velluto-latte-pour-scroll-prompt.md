# Velluto Latte Pour (cafe) - Build Prompts

Prompts to rebuild or restyle this page with Claude Code, Gemini (Nano Banana for stills, Veo for motion) and ffmpeg. One HTML file: a cream editorial hero, then a pinned terracotta section that draws ONE AI-rendered clip, cut into 100 WebP frames, on a canvas as you scroll. Fonts are Fraunces (soft, heavy) and Inter.

## 1. Pick the background colour first
One hex for the stills and the prompts (here #B0694A, terracotta). The video comes back slightly darker with a vignette, so the page samples six edge colours per frame and paints the canvas margin with them.

## 2. START still (Gemini, image)
"Photorealistic 3D product render, a plain empty matte-white speckled ceramic latte cup with handle on a saucer, centered, slightly high 3/4 front view, the cup about 40 percent of the frame width. Directly above the empty cup, floating in mid-air, one glossy dark chocolate-brown coffee sphere with a mirror-like surface, a few coffee beans and two small cream marshmallow spheres. Soft warm studio key light from upper left, gentle soft shadow, solid flat smooth terracotta background of exactly #B0694A, no gradient, no texture, no horizon line, no text, no hands, no other objects. 16:9, generous empty margin."

## 3. END still: edit the START still (same chat)
"Edit this exact image and output a new image with the identical 16:9 frame: keep the cup, saucer, camera angle, lighting and the solid terracotta #B0694A background exactly the same size and position. Change only the contents: the cup is now filled with a creamy latte with a detailed white rosetta latte art, and a thin crown-shaped milk splash rises just above the rim; the glossy sphere is gone. Floating around the cup with sharp frozen motion, all inside the frame with a clear margin: a cream vanilla flower with two vanilla pods on the left, a glossy caramel swirl ribbon at lower left, a dark chocolate chunk on the right, a few coffee beans and two cream marshmallow spheres. Photorealistic, no text."
Editing the first image (instead of generating a new one) keeps cup, camera and colour identical, which is what stops the video model from cross-dissolving.

## 4. The motion (Veo in Gemini, Videos, frames to video: attach START then END)
"Use the first attached image as the exact FIRST frame and the second attached image as the exact LAST frame. The cup, saucer, camera and terracotta background stay identical in both. Physically real continuous motion: the glossy coffee sphere drops straight down into the empty cup, a creamy latte wells up and fills the cup, a crown-shaped milk splash rises and settles, the rosetta latte art forms on the surface, while the vanilla flower, caramel swirl, chocolate chunk, coffee beans and marshmallows drift in slowly from outside the frame and float into their final positions around the cup, reaching the last frame by real motion only. Smooth, unhurried, even motion. Absolutely NO cross-dissolve, NO fade, NO morph, NO ghost double exposure, NO cut. Steady locked camera, no zoom, constant soft studio lighting, solid terracotta background throughout, photorealistic, no text, no hands."

## 5. Frames
`ffmpeg -i clip.mp4 -t 10 -vf "fps=10,scale=1200:-2" -c:v libwebp -quality 72 -compression_level 6 seq/%03d.webp` gives 100 frames, about 1.4 MB.

## 6. Layout and scroll
"Cream (#f3ece1) editorial hero with soft arc shapes, small nav, Search pill, black Sign Up pill, huge uppercase Fraunces headline, glossy sphere cutout floating, two product cards bleeding off the edge. Then a 560vh section with a sticky 100vh stage and one full-size canvas. Frame index = round(progress * (N-1)); progress from GSAP ScrollTrigger with Lenis smoothing, plain getBoundingClientRect fallback if the CDNs are blocked. Per frame, paint the canvas with the clip's own six edge colours, draw the frame with feathered edges, scaled to fit (wider crop on phones). Two glass cards fade in on a separate layer (Milk at 0, Signature No. 2 at 0.33). At 0.8 the headline 'Make your latte.' and VANILLA / CARAMEL / MOCHA buttons fade in, positioned on top of the ingredients from frame-relative coordinates; clicking one updates a note. Reduced motion: show the last frame."

## 7. Make it yours
"Chips for flavour, milk, temperature and size update a summary card (name, note, sample price). A three-item menu on sand tiles and a footer with placeholder address."

## Customisation prompts
- "Swap the coffee sphere for a matcha sphere and the milk for oat milk; regenerate both stills in green tones."
- "Add a fourth flavour, cardamom, to the end still and the buttons."
- "Put my real menu, address and hours in; remove every placeholder."
- "Translate all copy and keep the layout."

## Pre-launch checklist
- Replace the placeholder brand, prices, address and email; connect your own checkout.
- Test on a real phone; the page weighs about 1.9 MB because the frames are embedded. For production, host the frames as separate files.
