# Velluto Latte Pour (cafe) - Build Prompts

Prompts to rebuild or restyle this exact page with Claude Code and an image model. One HTML file, no build step, no WebGL: every visual is an AI-rendered photoreal image (cut out to transparent WebP, floated with scroll parallax) plus one AI-rendered video turned into a scroll-scrubbed frame sequence. Fonts are Fraunces (soft, heavy) and Inter.

## 1. Render the objects (image model, e.g. Gemini / Nano Banana)
"Hyper-realistic glossy 3D product-render asset sheet, 4K, 16:9, on a PURE WHITE seamless background, no floor, no ground shadows. Eight separate isolated objects in a loose 4x2 grid with generous empty white space so nothing touches: a roasted coffee bean, a glossy cream marshmallow sphere, a white vanilla orchid with two vanilla pods, a thick glossy golden caramel ribbon swirl, a broken shard of dark milk chocolate bar, a glossy caramel cube with a drip, a milk drop with a tiny splash crown, a glossy dark chocolate bead. Soft warm studio light from upper left, crisp specular highlights, warm cream / caramel / terracotta / chocolate palette. No text, no logos."
Repeat for: speckled stoneware latte cup with rosetta latte art (no saucer), glass french press, copper milk pitcher, chocolate sphere with a milk drip, mug of milk with a chocolate bead, porcelain ring, sugar cube, caramel droplets. Cut each object out (rembg or any background remover), trim, resize to its display size x2 and save as WebP with alpha (quality ~82).

## 2. Render the pour clip (video model, e.g. Veo in Gemini)
"Photorealistic commercial product cinematography, 16:9, locked-off static camera, macro lens, shallow depth of field, soft bright high-key studio light, warm cream palette. A single glossy dark espresso-brown chocolate sphere falls in slow motion into a wide pool of creamy white milk; a symmetrical milk crown splash rises with thin tendrils and droplets, ripples spread, then a thick smooth ribbon of milk pours down into the pool. Nothing else in frame, no hands, no text."
Then: `ffmpeg -i clip.mp4 -t 9.6 -vf "fps=7.5,scale=960:540,curves=all='0/0 0.45/0.55 1/1'" -c:v libwebp -quality 58 seq/%03d.webp` (72 frames, ~480 KB).

## 3. Layout
"Clean editorial layout on cream (#f3ece1) with soft curved arc shapes. Small nav (Home About Features Contact), a Search pill and a black Sign Up pill. Headlines in Fraunces weight 900, SOFT 100, uppercase. Section labels like 'SECTION 2 -'. Rounded-square product cards, side cards with 'Shop Now' / 'Choose Now' pill buttons, pagination dots."

## 4. Hero
"Left: 'Section 1', a huge two-line headline, one short paragraph, a dark Shop Now pill and a circular arrow. Centre: the glossy coffee sphere cutout floating with a slow bob. Right: two product cards (french press, copper pitcher) bleeding off the edge. Around: caramel cube, porcelain ring, marshmallow, coffee bean as separate cutouts, each with its own parallax speed (data-par), float amplitude and tiny rotation. 'Collections' peeks at the bottom."

## 5. Collections to Milk (pinned, 440vh)
"A sticky 100vh stage with scroll progress p. 0-0.3: title 'Our Collections', three tiles (french press, mug of milk, copper pitcher) and the sphere-with-milk-drip cutout falls from the title into the centre tile. 0.3-0.46: title swaps to 'Milk', side tiles slide out, the centre tile scales up, a text card (Signature No. 2, Shop Now / Learn more) slides in on the left and a small product card on the right. 0.46-0.56: a canvas with the pour clip fades in over everything. 0.5-0.96: draw frame round(u*(n-1)) of the sequence to the canvas, so scrolling plays the drop, crown splash, ripples and the stream. Small cutouts keep floating on top."

## 6. Make your latte
"Terracotta section (radial gradient #c0785a to #9a573a) with cream corner circles, one with circular text. Chunky cream headline 'Make your latte.' and 'CHOOSE YOUR FLAVOR'. The latte cup cutout sits low-centre; vanilla flower on the left, caramel swirl in front, chocolate shard on the right, marshmallow, beans and cube floating. Thin curved lines with dot ends connect labels VANILLA / CARAMEL / MOCHA to their ingredient; clicking a label lights that ingredient up and updates a note. A white milk stream grows from the top edge down into the cup as you scroll, matching the width of the stream at the end of the pour clip."

## 7. Make it yours
"Chips for flavour, milk, temperature and size update a summary card (name, note, sample price). A three-item menu on sand tiles with cutouts and a footer with placeholder address."

## Customisation prompts
- "Swap the coffee sphere for a matcha sphere and the milk for oat milk; regenerate the renders in green tones."
- "Add a fourth flavour, cardamom, with its own cutout, label and note."
- "Put my real menu, address and hours in; remove all placeholders."
- "Translate all copy to my language and keep the layout."

## Pre-launch checklist
- Replace placeholder brand, prices, address and email; connect your own checkout.
- Re-render or replace any image you do not want to ship; keep image alt text meaningful.
- Test the scroll sequence on a real phone; frames are 960x540 WebP.
