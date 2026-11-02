# Integração do site ubee.com.br com o conteúdo automático

Este pacote liga o site Laravel da Ubee Digital ao repositório `gshtecnologias/ubee-social`, onde o Claude publica todo dia artigos do blog, guias, páginas por segmento, páginas de temas e materiais gratuitos. Depois de instalado, o site sincroniza sozinho a cada hora; ninguém precisa abrir o VS Code para publicar.

## Como usar (Guilherme)

1. Abra o projeto do site no VS Code.
2. Peça ao Claude Code: **"Siga o arquivo INSTRUCOES-CLAUDE-CODE.md do repositório gshtecnologias/ubee-social (pasta site/) e instale a integração neste projeto."**
   - Se ele não tiver acesso ao GitHub, baixe a pasta `site/` do repositório e coloque dentro do projeto como `_ubee-integracao/`, e peça para ele seguir este arquivo.
3. Faça o deploy como de costume e confirme que o cron do servidor roda o agendador do Laravel.

---

## Instruções para o Claude Code

Você está instalando a integração de conteúdo no projeto Laravel aberto. Siga os passos na ordem, adaptando ao que já existe no projeto. Não apague nem sobrescreva páginas existentes sem perguntar ao Guilherme.

### 1. Entenda o projeto antes de mexer

- Versão do Laravel (`composer.json`), onde ficam o layout principal (`resources/views/layouts/...`) e as seções/stacks que ele usa (`@yield`, `@stack('head')`).
- Se já existe blog (model, tabela, rotas `/blog`), sitemap, `robots.txt` e alguma página em `/temas-prontos`.
- Como é o deploy (git, FTP, Forge etc.) e se `storage/` e `public/` persistem entre deploys.

Resuma o que encontrou para o Guilherme antes do passo 3.

### 2. Arquivos do pacote

| Arquivo | Destino no projeto | Função |
| --- | --- | --- |
| `config/ubee.php` | `config/ubee.php` | URL do repositório, prefixos de rota, dados da empresa e textos do llms.txt |
| `app/Console/Commands/UbeeContentSync.php` | mesmo caminho | Comando `php artisan ubee:sync-content`: baixa blog e páginas, converte Markdown em HTML, extrai o FAQ, gera `public/llms.txt` e `public/sitemap-ubee.xml` |
| `app/Http/Controllers/UbeeContentController.php` | mesmo caminho | Exibe blog e páginas a partir de `storage/app/ubee` |
| `routes/ubee.php` | `routes/ubee.php` | Rotas `/blog`, `/blog/{slug}`, `/guia/{slug}`, `/loja-virtual/{slug}`, `/temas-prontos/{slug}`, `/materiais/{slug}` |
| `resources/views/ubee/show.blade.php` | mesmo caminho | Página de artigo ou de conteúdo, com breadcrumb, CTA e "Continue lendo" |
| `resources/views/ubee/blog-index.blade.php` | mesmo caminho | Listagem do blog |
| `resources/views/ubee/partials/meta.blade.php` | mesmo caminho | Title, description, canonical e Open Graph |
| `resources/views/ubee/partials/schema.blade.php` | mesmo caminho | JSON-LD: Organization, Article/WebPage, FAQPage e BreadcrumbList |

### 3. Instale

1. Copie os arquivos para os destinos acima.
2. Ajuste as views ao layout real: troque `@extends('layouts.app')`, `@section('content')` e `@push('head')` pelos nomes usados no projeto. Se o layout não tiver `@stack('head')`, adicione dentro do `<head>`.
3. Estilize `.ubee-content`, `.ubee-body` (tipografia de artigo: h2, h3, listas, tabelas, links), `.ubee-grid` e `.ubee-card` com o CSS do site, mobile first. Cores da marca: azul `#0d6efd`, fonte Montserrat.
4. Inclua as rotas no fim de `routes/web.php`: `require __DIR__.'/ubee.php';`. Se algum prefixo conflitar com rotas existentes, mude em `config/ubee.php` (não nas rotas).
5. Se o site **já tem blog com banco de dados**, pergunte ao Guilherme se prefere: (a) migrar o blog para este sistema de arquivos, mantendo os posts antigos nas rotas atuais com redirect 301, ou (b) adaptar o comando para gravar no model existente (troque a gravação em JSON por `Model::updateOrCreate(['slug' => ...], [...])`, guardando também `faq` e `html`).
6. Agende o comando a cada hora:
   - Laravel 11+: em `routes/console.php`, `Schedule::command('ubee:sync-content')->hourly();`
   - Laravel 10 ou anterior: em `app/Console/Kernel.php`, `$schedule->command('ubee:sync-content')->hourly();`
7. Remova o comando antigo `ImportUbeeBlogPosts` se tiver sido instalado; este o substitui.

### 4. SEO do site inteiro

- Inclua `@include('ubee.partials.schema')` (sem `$item`) no `<head>` da home para publicar o schema de Organization.
- `robots.txt`: acrescente `Sitemap: https://ubee.com.br/sitemap-ubee.xml` (mantenha o sitemap principal, se existir; se houver um sitemap index, inclua o novo nele).
- Garanta que o deploy não apague `public/llms.txt` nem `public/sitemap-ubee.xml` (ou rode `php artisan ubee:sync-content` no fim do deploy).
- Na página `/temas-prontos` existente, adicione links para as páginas `/temas-prontos/{slug}` que forem surgindo (pode listar a partir de `storage/app/ubee/pages/_index.json`, filtrando `type = tema`).
- Confira se as páginas importantes têm um único `<h1>`, title e description únicos e imagens com `width`, `height` e `loading="lazy"`.

### 5. Teste

1. `php artisan ubee:sync-content` deve listar os itens sincronizados.
2. Abra `/blog`, um artigo (ex.: `/blog/frete-gratis-por-regiao-tray`), `/llms.txt` e `/sitemap-ubee.xml`.
3. Valide o JSON-LD de um artigo no [Teste de pesquisa aprimorada do Google](https://search.google.com/test/rich-results): devem aparecer Article, FAQ e Breadcrumb.
4. Rode `php artisan route:list | grep ubee` e confira que nada conflita.

### 6. Produção

- O servidor precisa do cron do Laravel: `* * * * * cd /caminho/do/site && php artisan schedule:run >> /dev/null 2>&1`.
- Depois do deploy, rode `php artisan ubee:sync-content` uma vez.
- No Google Search Console, envie `https://ubee.com.br/sitemap-ubee.xml`.

## Formato do conteúdo (referência)

Artigos: `blog/AAAA-MM-DD-slug.md` listados em `blog/index.json`. Páginas: `pages/<tipo>-<slug>.md` listadas em `pages/index.json`. Front matter:

```yaml
---
title: "Loja virtual para moda: como montar e vender mais"
slug: moda
type: segmento            # artigo | guia | segmento | tema | material
date: 2026-11-04T09:00:00-03:00
updated: 2026-11-04T09:00:00-03:00
description: "Até 155 caracteres."
category: Segmentos
tags: [moda, loja virtual, tray, nuvemshop]
cover: https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/...
cta_text: "Quero minha loja de moda"
cta_url: https://ubee.com.br/temas-prontos
---
```

O FAQ do schema vem da seção `## Perguntas frequentes`, com cada pergunta em **negrito** numa linha e a resposta no parágrafo seguinte.
