#!/usr/bin/env python3
"""Ubee Digital - renderizador de artes para redes sociais.

Uso:  python3 render.py spec.json [pasta_saida]

Gera PNGs a partir de um JSON com as chaves opcionais:
  carousel : lista de slides  (feed 1080x1350)
  feed     : post único        (1080x1350)
  story    : story             (1080x1920)
  pin      : pin Pinterest     (1000x1500)
Veja exemplo-spec.json para o formato completo.
"""
import json, sys, html, pathlib
from playwright.sync_api import sync_playwright

BASE = pathlib.Path(__file__).resolve().parent
ASSETS = (BASE / "assets").as_uri()

BLUE = "#0d6efd"
BLUE_DARK = "#0a4fc4"
NAVY = "#071a3d"
INK = "#0b1220"
TEXT = "#3b4658"
MUTED = "#8a94a6"
LIGHT = "#f2f6ff"

FONT_CSS = "\n".join(
    f"@font-face{{font-family:'Montserrat';font-weight:{w};src:url('{ASSETS}/montserrat-latin-{w}-normal.woff2') format('woff2');}}"
    for w in (300, 400, 500, 600, 700, 800, 900)
)

BASE_CSS = f"""
{FONT_CSS}
*{{margin:0;padding:0;box-sizing:border-box}}
html,body{{width:var(--w);height:var(--h);overflow:hidden}}
body{{font-family:'Montserrat',sans-serif;-webkit-font-smoothing:antialiased;position:relative}}
.blue{{background:radial-gradient(120% 90% at 85% 10%, #3d8bff 0%, {BLUE} 38%, {BLUE_DARK} 100%);color:#fff}}
.white{{background:#fff;color:{INK}}}
.light{{background:{LIGHT};color:{INK}}}
.chip{{display:inline-flex;align-items:center;gap:14px;font-weight:800;font-size:26px;letter-spacing:.14em;text-transform:uppercase;padding:16px 28px;border-radius:999px}}
.blue .chip{{background:#fff;color:{BLUE}}}
.white .chip,.light .chip{{background:{BLUE};color:#fff}}
.chip i{{width:16px;height:18px;display:inline-block;background:currentColor;clip-path:polygon(50% 0,100% 25%,100% 75%,50% 100%,0 75%,0 25%)}}
.deco{{position:absolute;pointer-events:none}}
.fit{{overflow:hidden}}
b,strong{{font-weight:800}}
.blue em{{font-style:normal;color:#cfe0ff}}
.white em,.light em{{font-style:normal;color:{BLUE}}}
"""

HEX_SVG = ("<svg class='deco' style='{style}' viewBox='0 0 100 100'>"
           "<polygon points='50,4 92,28 92,72 50,96 8,72 8,28' fill='{fill}' stroke='{stroke}' "
           "stroke-width='{sw}' stroke-linejoin='round'/></svg>")


def hexa(style, fill="none", stroke="rgba(255,255,255,.14)", sw=3):
    return HEX_SVG.format(style=style, fill=fill, stroke=stroke, sw=sw)


def esc(s):
    """Escapa HTML mas permite **negrito** e *destaque* simples."""
    s = html.escape(s or "")
    import re
    s = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", s)
    s = re.sub(r"\*(.+?)\*", r"<em>\1</em>", s)
    return s.replace("\n", "<br>")


FIT_JS = """
document.fonts.ready.then(()=>{
  const warn=[];
  document.querySelectorAll('.fit').forEach(el=>{
    let size=parseFloat(getComputedStyle(el).fontSize);
    const min=parseFloat(el.dataset.min||22);
    const inn=document.createElement('div');
    while(el.firstChild) inn.appendChild(el.firstChild);
    el.appendChild(inn);
    const over=()=> inn.offsetHeight > el.clientHeight+1;
    while(over() && size>min){size-=2;el.style.fontSize=size+'px';}
    if(over()) warn.push(el.dataset.name||'texto');
  });
  window.__fitWarnings=warn; window.__ready=true;
});
"""


def page(w, h, body_class, inner):
    return (f"<!doctype html><html><head><meta charset='utf-8'><style>:root{{--w:{w}px;--h:{h}px}}{BASE_CSS}</style>"
            f"</head><body class='{body_class}'>{inner}<script>{FIT_JS}</script></body></html>")


# ---------------- carrossel ----------------

def footer_progress(idx, total, dark):
    segs = "".join(
        f"<span style='flex:1;height:8px;border-radius:8px;background:{(BLUE if not dark else '#fff') if i <= idx else ('#dfe7f5' if not dark else 'rgba(255,255,255,.28)')}'></span>"
        for i in range(total))
    return f"<div style='position:absolute;left:96px;right:96px;bottom:64px;display:flex;gap:10px'>{segs}</div>"


def slide_cover(s, spec, idx, total):
    inner = f"""
    {hexa('width:720px;height:720px;right:-220px;top:-160px')}
    {hexa('width:420px;height:420px;right:120px;top:300px', stroke='rgba(255,255,255,.08)')}
    <div style='position:absolute;left:96px;right:96px;top:110px'>
      <span class='chip'><i></i>{esc(spec.get('kicker',''))}</span>
    </div>
    <div class='fit' data-name='titulo da capa' data-min='56'
         style='position:absolute;left:96px;right:110px;top:300px;height:560px;font-size:100px;font-weight:800;line-height:1.04;letter-spacing:-.02em;display:flex;align-items:flex-end'>
      <div>{esc(s.get('title'))}</div></div>
    <div class='fit' data-name='subtitulo da capa' data-min='26'
         style='position:absolute;left:96px;right:160px;top:900px;height:150px;font-size:38px;font-weight:500;line-height:1.35;color:rgba(255,255,255,.88)'>{esc(s.get('subtitle',''))}</div>
    <img src='{ASSETS}/logo-white.png' style='position:absolute;left:96px;bottom:120px;width:300px'>
    <div style='position:absolute;right:96px;bottom:126px;font-weight:700;font-size:30px;display:flex;align-items:center;gap:14px'>Arraste
      <svg width='46' height='24' viewBox='0 0 46 24'><path d='M2 12h40M32 3l10 9-10 9' stroke='#fff' stroke-width='4' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg></div>
    """
    return page(1080, 1350, "blue", inner)


def top_bar(spec, idx, total, dark=False):
    icon = "icon-white.png" if dark else "icon-color.png"
    col = "#fff" if dark else BLUE
    mut = "rgba(255,255,255,.7)" if dark else MUTED
    return f"""
    <div style='position:absolute;left:96px;right:96px;top:84px;display:flex;align-items:center;justify-content:space-between'>
      <div style='display:flex;align-items:center;gap:18px'>
        <img src='{ASSETS}/{icon}' style='height:54px'>
        <span style='font-weight:800;font-size:24px;letter-spacing:.14em;text-transform:uppercase;color:{col}'>{esc(spec.get('kicker',''))}</span>
      </div>
      <span style='font-weight:600;font-size:26px;color:{mut}'>{idx+1}/{total}</span>
    </div>"""


def slide_step(s, spec, idx, total):
    n = s.get("n", idx)
    inner = f"""
    {hexa('width:520px;height:520px;right:-200px;bottom:-140px', stroke='rgba(13,110,253,.07)', sw=4)}
    {top_bar(spec, idx, total)}
    <div style='position:absolute;left:96px;top:250px;width:150px;height:150px'>
      <svg viewBox='0 0 100 100' width='150' height='150'><polygon points='50,4 92,28 92,72 50,96 8,72 8,28' fill='{BLUE}' stroke='{BLUE}' stroke-width='8' stroke-linejoin='round'/></svg>
      <div style='position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:62px'>{n}</div>
    </div>
    <div style='position:absolute;left:96px;right:96px;top:450px;bottom:150px;display:flex;flex-direction:column;gap:44px'>
      <div class='fit' data-name='titulo do slide {idx+1}' data-min='40'
           style='flex:none;max-height:330px;font-size:68px;font-weight:800;line-height:1.12;letter-spacing:-.015em;color:{INK}'>{esc(s.get('title'))}</div>
      <div class='fit' data-name='texto do slide {idx+1}' data-min='26'
           style='flex:1;min-height:0;font-size:40px;font-weight:500;line-height:1.5;color:{TEXT}'>{esc(s.get('body',''))}</div>
    </div>
    {footer_progress(idx, total, False)}
    """
    return page(1080, 1350, "white", inner)


def slide_list(s, spec, idx, total):
    items = "".join(
        f"<li style='display:flex;gap:26px;align-items:flex-start;margin-bottom:34px'>"
        f"<svg viewBox='0 0 100 100' width='34' height='34' style='flex:none;margin-top:10px'><polygon points='50,4 92,28 92,72 50,96 8,72 8,28' fill='{BLUE}' stroke='{BLUE}' stroke-width='10' stroke-linejoin='round'/></svg>"
        f"<span>{esc(it)}</span></li>" for it in s.get("items", []))
    inner = f"""
    {top_bar(spec, idx, total)}
    <div class='fit' data-name='titulo do slide {idx+1}' data-min='40'
         style='position:absolute;left:96px;right:96px;top:240px;height:230px;font-size:66px;font-weight:800;line-height:1.1;letter-spacing:-.015em;color:{INK};display:flex;align-items:flex-end'><div>{esc(s.get('title'))}</div></div>
    <div style='position:absolute;left:96px;width:120px;top:500px;height:8px;border-radius:8px;background:{BLUE}'></div>
    <ul class='fit' data-name='lista do slide {idx+1}' data-min='26'
        style='list-style:none;position:absolute;left:96px;right:96px;top:570px;height:620px;font-size:40px;font-weight:600;line-height:1.38;color:{INK}'>{items}</ul>
    {footer_progress(idx, total, False)}
    """
    return page(1080, 1350, "light", inner)


def slide_cta(s, spec, idx, total):
    btn = s.get("button", "Fale com a Ubee")
    inner = f"""
    {hexa('width:820px;height:820px;right:-380px;bottom:-300px')}
    {hexa('width:300px;height:300px;right:80px;top:120px', fill='rgba(255,255,255,.1)', stroke='none')}
    <img src='{ASSETS}/logo-white.png' style='position:absolute;left:96px;top:120px;width:380px'>
    <div class='fit' data-name='titulo do CTA' data-min='44'
         style='position:absolute;left:96px;right:96px;top:380px;height:380px;font-size:80px;font-weight:800;line-height:1.08;letter-spacing:-.02em;display:flex;align-items:flex-end'><div>{esc(s.get('title'))}</div></div>
    <div class='fit' data-name='texto do CTA' data-min='26'
         style='position:absolute;left:96px;right:120px;top:800px;height:170px;font-size:38px;font-weight:500;line-height:1.4;color:rgba(255,255,255,.9)'>{esc(s.get('body',''))}</div>
    <div style='position:absolute;left:96px;top:1020px;background:#fff;color:{BLUE};font-weight:800;font-size:36px;padding:28px 52px;border-radius:999px;box-shadow:0 18px 40px rgba(7,26,61,.25)'>{esc(btn)}</div>
    <div style='position:absolute;left:96px;right:96px;bottom:150px;display:flex;justify-content:space-between;font-weight:600;font-size:28px;color:rgba(255,255,255,.85)'>
      <span>{esc(spec.get('handle','@ubeedigital'))}</span><span>{esc(spec.get('site','ubee.com.br'))}</span></div>
    {footer_progress(idx, total, True)}
    """
    return page(1080, 1350, "blue", inner)


SLIDES = {"cover": slide_cover, "step": slide_step, "list": slide_list, "cta": slide_cta}


# ---------------- post único ----------------

def feed_single(f, spec):
    kind = f.get("type", "tip")
    if kind == "stat":
        inner = f"""
        {hexa('width:640px;height:640px;right:-200px;top:-120px', stroke='rgba(13,110,253,.08)', sw=4)}
        <div style='position:absolute;left:96px;top:110px'><span class='chip'><i></i>{esc(spec.get('kicker',''))}</span></div>
        <div class='fit' data-name='numero' data-min='120'
             style='position:absolute;left:90px;right:96px;top:290px;height:330px;font-size:300px;font-weight:900;line-height:1;letter-spacing:-.04em;color:{BLUE}'>{esc(f.get('number'))}</div>
        <div class='fit' data-name='titulo' data-min='38'
             style='position:absolute;left:96px;right:96px;top:650px;height:300px;font-size:64px;font-weight:800;line-height:1.12;color:{INK}'>{esc(f.get('title'))}</div>
        <div class='fit' data-name='fonte' data-min='20'
             style='position:absolute;left:96px;right:96px;top:980px;height:120px;font-size:30px;font-weight:500;line-height:1.4;color:{MUTED}'>{esc(f.get('note',''))}</div>
        <img src='{ASSETS}/logo-color.png' style='position:absolute;left:96px;bottom:96px;width:280px'>
        """
        return page(1080, 1350, "white", inner)
    blue = kind == "highlight"
    logo = "logo-white.png" if blue else "logo-color.png"
    title_col = "#fff" if blue else INK
    body_col = "rgba(255,255,255,.9)" if blue else TEXT
    deco = hexa('width:760px;height:760px;right:-260px;top:-200px') if blue else hexa('width:640px;height:640px;right:-220px;bottom:-180px', stroke='rgba(13,110,253,.08)', sw=4)
    inner = f"""
    {deco}
    <div style='position:absolute;left:96px;top:110px'><span class='chip'><i></i>{esc(spec.get('kicker',''))}</span></div>
    <div class='fit' data-name='titulo' data-min='48'
         style='position:absolute;left:96px;right:110px;top:260px;height:470px;font-size:92px;font-weight:800;line-height:1.06;letter-spacing:-.02em;color:{title_col};display:flex;align-items:flex-end'><div>{esc(f.get('title'))}</div></div>
    <div style='position:absolute;left:96px;width:120px;top:770px;height:8px;border-radius:8px;background:{"#fff" if blue else BLUE}'></div>
    <div class='fit' data-name='texto' data-min='26'
         style='position:absolute;left:96px;right:120px;top:830px;height:290px;font-size:40px;font-weight:500;line-height:1.45;color:{body_col}'>{esc(f.get('body',''))}</div>
    <img src='{ASSETS}/{logo}' style='position:absolute;left:96px;bottom:96px;width:280px'>
    <span style='position:absolute;right:96px;bottom:104px;font-weight:600;font-size:28px;color:{"rgba(255,255,255,.8)" if blue else MUTED}'>{esc(spec.get('handle','@ubeedigital'))}</span>
    """
    return page(1080, 1350, "blue" if blue else "white", inner)


# ---------------- story ----------------
# Zonas seguras do Instagram: evitar 0-250px (perfil) e 1580-1920px (respostas/figurinhas).

def story(st, spec):
    cta = st.get("cta", "")
    cta_html = (f"<div style='flex:none;margin-top:24px;align-self:flex-start;display:flex;align-items:center;gap:18px;font-weight:800;font-size:34px;background:#fff;color:{BLUE};padding:26px 40px;border-radius:999px;box-shadow:0 18px 40px rgba(7,26,61,.25)'>"
                f"{esc(cta)}<svg width='36' height='36' viewBox='0 0 24 24'><path d='M12 4v14M5 11l7 7 7-7' stroke='{BLUE}' stroke-width='2.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg></div>") if cta else ""
    inner = f"""
    {hexa('width:900px;height:900px;right:-360px;top:120px')}
    {hexa('width:620px;height:620px;left:-240px;bottom:-120px', stroke='rgba(255,255,255,.09)')}
    {hexa('width:150px;height:150px;right:120px;bottom:330px', fill='rgba(255,255,255,.12)', stroke='none')}
    <div style='position:absolute;left:96px;right:110px;top:280px;bottom:360px;display:flex;flex-direction:column;justify-content:center;gap:40px'>
      <img src='{ASSETS}/logo-white.png' style='width:260px;flex:none'>
      <div style='flex:none'><span class='chip'><i></i>{esc(spec.get('kicker',''))}</span></div>
      <div class='fit' data-name='titulo do story' data-min='56'
           style='flex:none;max-height:640px;font-size:108px;font-weight:800;line-height:1.05;letter-spacing:-.02em'>{esc(st.get('title'))}</div>
      <div class='fit' data-name='texto do story' data-min='28'
           style='flex:none;max-height:260px;font-size:46px;font-weight:500;line-height:1.4;color:rgba(255,255,255,.9);padding-right:20px'>{esc(st.get('body',''))}</div>
      {cta_html}
    </div>
    """
    return page(1080, 1920, "blue", inner)


# ---------------- pin ----------------

def pin(p, spec):
    inner = f"""
    {hexa('width:760px;height:760px;right:-280px;top:-220px')}
    <div style='position:absolute;left:84px;top:110px'><span class='chip'><i></i>{esc(spec.get('kicker',''))}</span></div>
    <div class='fit' data-name='titulo do pin' data-min='50'
         style='position:absolute;left:84px;right:100px;top:270px;height:620px;font-size:96px;font-weight:800;line-height:1.05;letter-spacing:-.02em;display:flex;align-items:flex-end'><div>{esc(p.get('title'))}</div></div>
    <div class='fit' data-name='subtitulo do pin' data-min='26'
         style='position:absolute;left:84px;right:120px;top:930px;height:200px;font-size:40px;font-weight:500;line-height:1.4;color:rgba(255,255,255,.9)'>{esc(p.get('subtitle',''))}</div>
    <div style='position:absolute;left:84px;top:1170px;background:#fff;color:{BLUE};font-weight:800;font-size:32px;padding:24px 44px;border-radius:999px'>{esc(p.get('button','Leia no blog'))}</div>
    <img src='{ASSETS}/logo-white.png' style='position:absolute;left:84px;bottom:90px;width:280px'>
    <span style='position:absolute;right:84px;bottom:98px;font-weight:600;font-size:28px;color:rgba(255,255,255,.85)'>{esc(spec.get('site','ubee.com.br'))}</span>
    """
    return page(1000, 1500, "blue", inner)


# ---------------- execução ----------------

def render(spec, outdir):
    outdir = pathlib.Path(outdir).resolve()
    outdir.mkdir(parents=True, exist_ok=True)
    jobs = []
    slides = spec.get("carousel") or []
    for i, s in enumerate(slides):
        jobs.append((f"carousel-{i+1:02d}.png", 1080, 1350, SLIDES[s.get("type", "step")](s, spec, i, len(slides))))
    if spec.get("feed"):
        jobs.append(("feed.png", 1080, 1350, feed_single(spec["feed"], spec)))
    if spec.get("story"):
        jobs.append(("story.png", 1080, 1920, story(spec["story"], spec)))
    if spec.get("pin"):
        jobs.append(("pin.png", 1000, 1500, pin(spec["pin"], spec)))

    report = {"files": [], "warnings": []}
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path=_chrome(), args=["--allow-file-access-from-files"])
        for name, w, h, doc in jobs:
            pg = browser.new_page(viewport={"width": w, "height": h}, device_scale_factor=1)
            tmp = outdir / (name + ".html")
            tmp.write_text(doc, encoding="utf-8")
            pg.goto(tmp.as_uri())
            pg.wait_for_function("window.__ready === true", timeout=15000)
            warns = pg.evaluate("window.__fitWarnings")
            pg.screenshot(path=str(outdir / name), type="png")
            tmp.unlink()
            pg.close()
            report["files"].append(str(outdir / name))
            report["warnings"] += [f"{name}: {x} não coube, encurte o texto" for x in warns]
        browser.close()
    return report


def _chrome():
    import shutil
    for c in ("google-chrome", "chromium", "chromium-browser"):
        path = shutil.which(c)
        if path:
            return path
    return None


if __name__ == "__main__":
    spec = json.loads(pathlib.Path(sys.argv[1]).read_text(encoding="utf-8"))
    out = sys.argv[2] if len(sys.argv) > 2 else str(BASE / "out" / spec.get("id", "post"))
    print(json.dumps(render(spec, out), ensure_ascii=False, indent=2))
