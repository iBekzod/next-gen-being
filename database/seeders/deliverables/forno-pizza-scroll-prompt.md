# Forno Pizza Scroll (restaurant) - Build Prompts

Prompts to rebuild or restyle this page with Claude Code, Gemini (Nano Banana for stills, Veo for motion) and ffmpeg. One HTML file: a pinned section draws one AI-rendered clip, cut into WebP frames, on a canvas as you scroll. Fonts are Fraunces and Inter.

## 1. Pick the background colour first
Use one hex for the page, the stills and the prompts (here: #0E0B09 in the prompts; the video came back as about #181411, so the page uses that). A mismatch shows as a visible box around the clip.

## 2. END still: the finished product (Gemini, image)
"Photorealistic product shot, a finished Neapolitan margherita pizza with blistered leopard-spotted puffy crust, glossy melted white mozzarella, bright red tomato sauce, fresh basil leaves, centered on a round dark walnut wooden serving board, 3/4 top view from 40 degrees elevation, the pizza about 55 percent of the frame width, warm studio key light from above-right, sharp focus, solid flat background of exactly #0E0B09, no gradient, no texture, no other objects, no text, no hands. 16:9."

## 3. START still: the same scene taken apart (Gemini, image, attach the END still)
"Edit this exact image and output a new image with the identical 16:9 frame: keep the walnut board in exactly the same position, size, angle and lighting and the identical solid background. Change only the food: remove all toppings so the board holds just the plain flour-dusted raw dough disc (same size and position as the pizza now). Above the dough, floating in mid-air with sharp frozen motion, add a splash of bright red tomato sauce, whole ripe red tomatoes, torn chunks of white mozzarella and fresh basil leaves, all inside the frame with a clear margin, centered above the dough. Photorealistic, no text."
Keep board position and size identical in both stills. If they differ, the video model cross-dissolves between them (ghost double exposure) instead of animating.

## 4. The motion (Veo in Gemini, Videos, attach START then END)
"Use the first attached image as the exact FIRST frame and the second attached image as the exact LAST frame. The board and camera stay identical in both. Physically real continuous motion: the sauce splash, tomatoes, mozzarella chunks and basil leaves fall onto the raw dough, the sauce spreads in a spiral, the cheese lands and melts, basil settles, and the pizza finishes baking into the blistered Margherita, reaching the last frame by real motion only. Absolutely NO cross-dissolve, NO fade, NO morph, NO ghost double exposure, NO cut. Steady locked camera, constant studio lighting, solid dark background throughout, photorealistic, no text, no hands."

## 5. Frames
`ffmpeg -i clip.mp4 -t 9.7 -vf "fps=10,crop=1040:720:120:0,scale=960:-2" -c:v libwebp -quality 70 -compression_level 6 seq/%03d.webp` gives 97 frames, about 3.7 MB. Crop away empty side space so the product fills the frame.

## 6. Layout and scroll
"Dark page (#181411), cream text, ember-red accent #e4572e, Fraunces headlines (weight 700, soft), Inter body. A 420vh section with a sticky 100vh stage. One full-size canvas; frame index = round(progress * (N-1)); progress comes from GSAP ScrollTrigger with Lenis smoothing, a plain getBoundingClientRect fallback when the CDNs are blocked, and a small lerp so frames glide. Three glass text cards on a separate layer fade in at 0, 0.34 and 0.74 progress (left, right, left) and the picture shifts sideways to leave room. A 000/100 counter and a thin progress bar sit bottom right. Fade the top edge of the frame into the background colour. Reduced motion: show the last frame."

## 7. Below the animation
"A 'How it is made' strip of three cards drawn from frames 0, 48 and 96 of the same sequence; a two-column sample menu of six pizzas with prices; a visit block (booking button, address, hours) and a footer saying brand, menu and prices are placeholders."

## Customisation prompts
- "Swap the pizza for a burger: same method, new START/END stills and new colours."
- "Put my real menu, address and hours in; remove every placeholder."
- "Translate all copy and keep the layout."

## Pre-launch checklist
- Replace the placeholder brand, menu, prices, address and email; connect your own booking tool.
- Test on a real phone; the page weighs about 4.7 MB because the frames are embedded. For production, host the frames as separate files.
