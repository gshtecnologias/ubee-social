# Ubee Social

Conteúdo diário da Ubee Digital gerado pelo Claude: artes para Instagram, Facebook, Pinterest e LinkedIn, vídeos verticais para TikTok e YouTube Shorts (tudo agendado no Metricool) e artigos para o blog do ubee.com.br.

O repositório precisa ser **público**: o Metricool baixa as imagens pelo link `raw.githubusercontent.com`.

## Estrutura

```
templates/            renderizador e identidade visual
  render.py           gera os PNGs a partir de um spec JSON
  exemplo-spec.json   exemplo completo de spec
  assets/             logos (cor e branco) e fonte Montserrat
posts/AAAA-MM-DD/                 post educativo das 10h (artes, video.mp4, spec.json, legendas.json)
posts/AAAA-MM-DD-vendas-tray/     venda de planos Tray (15h)
posts/AAAA-MM-DD-vendas-nuvemshop/ venda de planos Nuvemshop (15h)
posts/AAAA-MM-DD-tendencias/      tendências (20h)
blog/                 artigos em Markdown com front matter (um por dia útil)
blog/index.json       lista dos artigos, do mais novo para o mais antigo
pages/                guias, páginas por segmento, temas e materiais (pages/index.json)
site/                 pacote de integração com o site Laravel (veja site/INSTRUCOES-CLAUDE-CODE.md)
reports/              relatórios semanais de desempenho
social/               método de social media (PLAYBOOK.md), voz da marca, hashtags e cópia das skills de Instagram
```

## Gerar artes

```bash
pip install playwright pillow
python3 templates/render.py posts/2026-11-03/spec.json posts/2026-11-03
```

Formatos: carrossel e post único 1080×1350, story 1080×1920, pin 1000×1500 e `video.mp4` vertical 1080×1920 (gerado automaticamente a partir do carrossel ou do post único; use `"video": false` no spec para desligar). Se um texto não couber, a fonte é reduzida e o relatório avisa para encurtar.

Link público de uma arte:

```
https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/posts/2026-11-03/carousel-01.png
```

## Formato dos artigos do blog

```markdown
---
title: "Como organizar categorias na Nuvemshop para vender mais"
slug: como-organizar-categorias-nuvemshop
date: 2026-11-03T09:00:00-03:00
description: "Meta description com até 155 caracteres."
category: Nuvemshop
tags: [nuvemshop, categorias, seo]
cover: https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/posts/2026-11-03/pin.png
---

Corpo do artigo em Markdown (H2/H3, listas, FAQ no final).
```

O site em Laravel importa os artigos lendo `blog/index.json` e cada `.md` listado.

## Integração com o site

Use o pacote em `site/` (comando `php artisan ubee:sync-content`, rotas, views, schema JSON-LD, `llms.txt` e sitemap). O passo a passo para o Claude Code do VS Code está em `site/INSTRUCOES-CLAUDE-CODE.md`. O antigo `laravel/ImportUbeeBlogPosts.php` foi substituído por esse pacote.
