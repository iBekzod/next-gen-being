<style>
  #ngb-market{
    --paper: oklch(0.975 0.006 264); --surface:#fff; --surface-2: oklch(0.965 0.008 264);
    --ink: oklch(0.18 0.035 264); --ink-soft: oklch(0.42 0.028 264); --ink-faint: oklch(0.58 0.02 264);
    --line: oklch(0.90 0.012 264); --line-strong: oklch(0.83 0.016 264);
    --signal: oklch(0.58 0.17 255); --signal-ink: oklch(0.46 0.16 255); --signal-wash: oklch(0.95 0.03 255);
    --good: oklch(0.62 0.15 155); --amber: oklch(0.75 0.14 75);
    --font-display: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    color: var(--ink); background: var(--paper);
  }
  .dark #ngb-market{
    --paper: oklch(0.155 0.028 264); --surface: oklch(0.195 0.03 264); --surface-2: oklch(0.225 0.032 264);
    --ink: oklch(0.96 0.006 264); --ink-soft: oklch(0.78 0.02 264); --ink-faint: oklch(0.6 0.022 264);
    --line: oklch(0.30 0.03 264); --line-strong: oklch(0.38 0.035 264);
    --signal: oklch(0.72 0.16 255); --signal-ink: oklch(0.80 0.14 255); --signal-wash: oklch(0.28 0.06 255);
  }
  #ngb-market .m-display{ font-family: var(--font-display); font-weight: 800; letter-spacing: -0.03em; }
  #ngb-market h1, #ngb-market h2, #ngb-market h3{ font-family: var(--font-display); }
  #ngb-market .m-card{ background: var(--surface); border: 1px solid var(--line); border-radius: 14px;
    overflow: hidden; transition: transform .2s, box-shadow .2s, border-color .2s; }
  #ngb-market .m-card:hover{ transform: translateY(-3px); border-color: var(--signal);
    box-shadow: 0 24px 60px oklch(0.2 0.03 264 / .16); }
  #ngb-market .m-live{ display:inline-flex; align-items:center; gap:6px; font-size:.68rem; font-weight:700;
    color:#fff; letter-spacing:.04em; }
  #ngb-market .m-live .blink{ width:7px; height:7px; border-radius:50%; background:#fff; animation:mblink 1.6s ease-in-out infinite; }
  @keyframes mblink{ 0%,100%{opacity:1} 50%{opacity:.25} }
  /* card live-demo preview on hover */
  #ngb-market .m-thumb{ height:140px; position:relative; overflow:hidden; display:grid; place-items:center;
    background:linear-gradient(135deg, oklch(0.35 0.13 264), var(--signal)); }
  #ngb-market .m-thumb .m-demo{ position:absolute; inset:0; width:250%; height:250%; border:0;
    transform:scale(.4); transform-origin:top left; opacity:0; transition:opacity .35s; pointer-events:none; background:#0b1020; }
  #ngb-market .m-card:hover .m-thumb .m-demo{ opacity:1; }
  #ngb-market .m-thumb .m-title{ position:relative; z-index:1; color:#fff; font-size:1.35rem; transition:opacity .3s; }
  #ngb-market .m-card:hover .m-thumb .m-title{ opacity:0; }
  #ngb-market .m-thumb .m-live{ z-index:2; }
  #ngb-market .m-thumb .m-hint{ position:absolute; bottom:8px; right:10px; z-index:2; font-size:.64rem; font-weight:700;
    color:#fff; background:oklch(0.15 0.03 264 / .55); padding:3px 8px; border-radius:999px; opacity:0; transition:opacity .3s; }
  #ngb-market .m-card:hover .m-thumb .m-hint{ opacity:1; }
  @media (prefers-reduced-motion: reduce){
    #ngb-market .m-thumb .m-demo, #ngb-market .m-thumb .m-title, #ngb-market .m-thumb .m-hint{ transition:none; }
  }
</style>
