# Ubee Social

Conteúdo diário da Ubee Digital gerado pelo Claude: artes para Instagram, Facebook, Pinterest e LinkedIn (agendadas no Metricool) e artigos para o blog do ubee.com.br.

O repositório precisa ser **público**: o Metricool baixa as imagens pelo link `raw.githubusercontent.com`.

## Estrutura

```
templates/            renderizador e identidade visual
  render.py           gera os PNGs a partir de um spec JSON
  exemplo-spec.json   exemplo completo de spec
  assets/             logos (cor e branco) e fonte Montserrat
posts/AAAA-MM-DD/     artes do dia + spec.json + legendas.json
blog/                 artigos em Markdown com front matter (um por dia útil)
blog/index.json       lista dos artigos, do mais novo para o mais antigo
```

## Gerar artes

```bash
pip install playwright pillow
python3 templates/render.py posts/2026-11-03/spec.json posts/2026-11-03
```

Formatos: carrossel e post único 1080×1350, story 1080×1920, pin 1000×1500. Se um texto não couber, a fonte é reduzida e o relatório avisa para encurtar.

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

## Importador para o Laravel

`laravel/ImportUbeeBlogPosts.php` é um comando Artisan (`php artisan ubee:import-blog`) que lê o índice, converte o Markdown em HTML e faz upsert pelo slug. Artigos com data futura são ignorados até a hora chegar. Ajuste o model e as colunas no método `upsertPost()`.
