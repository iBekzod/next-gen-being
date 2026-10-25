# Builds demos/velluto-latte-pour-scroll.html: embeds img/*.webp (AI-rendered, cut out) into template.html as one data-URI map.
import base64, json, pathlib
here = pathlib.Path(__file__).parent
m = {p.stem: 'data:image/webp;base64,' + base64.b64encode(p.read_bytes()).decode() for p in sorted((here / 'img').glob('*.webp'))}
seq = ['data:image/webp;base64,' + base64.b64encode(p.read_bytes()).decode() for p in sorted((here / 'seq').glob('*.webp'))]
html = (here / 'template.html').read_text(encoding='utf-8').replace('/*IMG_MAP*/{}', json.dumps(m, separators=(',', ':'))).replace('/*SEQ_MAP*/[]', json.dumps(seq, separators=(',', ':')))
out = here.parents[1] / 'demos' / 'velluto-latte-pour-scroll.html'
out.write_text(html, encoding='utf-8')
print(out, len(html) // 1024, 'KB', len(m), 'images')
