# Builds demos/velluto-latte-pour-scroll.html: embeds seq/*.webp (frames of ONE Veo clip, start still -> end still) and img/*.webp cutouts into template.html.
# BG_MAP = per-frame colours of the 6 edge points (TL,TR,ML,MR,BL,BR) so the canvas margin matches the clip's own vignette.
import base64, json, pathlib
from PIL import Image
here = pathlib.Path(__file__).parent
USED = ['ball', 'press', 'pitcher', 'bean', 'marsh', 'cube', 'ring', 'saucer']
m = {n: 'data:image/webp;base64,' + base64.b64encode((here / 'img' / (n + '.webp')).read_bytes()).decode() for n in USED}
files = sorted((here / 'seq').glob('*.webp'))
seq = ['data:image/webp;base64,' + base64.b64encode(p.read_bytes()).decode() for p in files]
def corners(p):
    im = Image.open(p).convert('RGB'); w, h = im.size; o = []
    for x, y in ((6, 6), (w - 7, 6), (6, h // 2), (w - 7, h // 2), (6, h - 7), (w - 7, h - 7)): o += list(im.getpixel((x, y)))
    return o
bg = [corners(p) for p in files]
html = (here / 'template.html').read_text(encoding='utf-8').replace('/*IMG_MAP*/{}', json.dumps(m, separators=(',', ':'))).replace('/*SEQ_MAP*/[]', json.dumps(seq, separators=(',', ':'))).replace('/*BG_MAP*/[]', json.dumps(bg, separators=(',', ':')))
out = here.parents[1] / 'demos' / 'velluto-latte-pour-scroll.html'
out.write_text(html, encoding='utf-8')
print(out, len(html) // 1024, 'KB', len(seq), 'frames')
