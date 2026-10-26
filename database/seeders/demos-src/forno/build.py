# Builds demos/forno-pizza-scroll.html: embeds seq/*.webp (frames of an AI-rendered Veo clip) into template.html as one data-URI array.
import base64, json, pathlib
here = pathlib.Path(__file__).parent
seq = ['data:image/webp;base64,' + base64.b64encode(p.read_bytes()).decode() for p in sorted((here / 'seq').glob('*.webp'))]
html = (here / 'template.html').read_text(encoding='utf-8').replace('/*SEQ_MAP*/[]', json.dumps(seq, separators=(',', ':')))
out = here.parents[1] / 'demos' / 'forno-pizza-scroll.html'
out.write_text(html, encoding='utf-8')
print(out, len(html) // 1024, 'KB', len(seq), 'frames')
