# python build.py  -> ../../demos/nikoh-taklifnoma-scroll.html
# Inputs next to this file: bg_zumrad.webp, bg_osmon.webp, bg_bordo.webp (900px wide, q58), music.mp3 (32 kHz mono, 48k).
# Backgrounds were generated earlier with Higgsfield (no new credits). Re-encode from your own art with PIL: im.resize((900,h)).save(..., quality=58, method=6)
import base64, pathlib
d = pathlib.Path(__file__).parent
def b64(n, mime): return f"data:{mime};base64," + base64.b64encode((d / n).read_bytes()).decode()
h = (d / "template.html").read_text(encoding="utf8")
for k, f, m in [("__BG_ZUMRAD__", "bg_zumrad.webp", "image/webp"), ("__BG_OSMON__", "bg_osmon.webp", "image/webp"),
                ("__BG_BORDO__", "bg_bordo.webp", "image/webp"), ("__AUDIO__", "music.mp3", "audio/mpeg")]:
    h = h.replace(k, b64(f, m))
out = d.parent.parent / "demos" / "nikoh-taklifnoma-scroll.html"
out.write_text(h, encoding="utf8"); print(out, len(h) // 1024, "KB")
