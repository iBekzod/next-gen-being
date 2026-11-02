# python build.py  -> ../../demos/nikoh-taklifnoma-scroll.html  (+ ../../posters/nikoh-taklifnoma-scroll-og.jpg when --og is run via shots.py)
# Inputs next to this file: photos/*.webp (Pexels, see deliverables doc), music.mp3 (32 kHz mono, 48k).
# To use your own photos: re-crop with PIL (hero/story/gallery 4:5, wide/closing 16:9, WebP q~78, <=150 KB) and overwrite photos/*.webp,
# or leave this build alone and set CONFIG.photos paths in the HTML.
import base64, pathlib
d = pathlib.Path(__file__).parent
def b64(p, mime): return f"data:{mime};base64," + base64.b64encode(p.read_bytes()).decode()
h = (d / "template.html").read_text(encoding="utf8")
for k in ["hero", "s1", "s2", "s3", "wide", "g1", "g2", "g3", "g4", "close"]:
    h = h.replace(f"__PH_{k.upper()}__", b64(d / "photos" / f"{k}.webp", "image/webp"))
h = h.replace("__AUDIO__", b64(d / "music.mp3", "audio/mpeg"))
out = d.parent.parent / "demos" / "nikoh-taklifnoma-scroll.html"
out.write_text(h, encoding="utf8"); print(out, len(h) // 1024, "KB")
