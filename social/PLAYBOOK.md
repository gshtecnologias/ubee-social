# Playbook de social media da Ubee Digital (Instagram e Facebook)

Este é o método que as tarefas agendadas seguem para criar e publicar no Instagram e no Facebook. Ele aplica as 9 skills de Instagram (gshtecnologias/instagram-skills) à rotina da Ubee e troca a publicação via Publora pelo **Metricool**, que já está conectado. O calendário editorial continua mandando no *quê* e *quando*; este playbook manda no *como escrever, auditar e publicar*.

Se as skills `ig-*` estiverem disponíveis na sessão, carregue as que o passo pede (ferramenta Skill). Se não estiverem, siga este arquivo e as referências em `social/skills-ref/` (cópia das skills e das referências originais). A voz da marca está em `social/voice-profile.md` e os bancos de hashtags em `social/hashtags.md`.

## O que move o alcance em 2026

O Instagram ranqueia por **envios** (mandar no direct, repostar no story) e **salvamentos**, depois comentários; curtida pesa pouco. Antes de cada post, a pergunta é: alguém vai salvar isso para usar depois ou mandar para um amigo lojista? Se a resposta for só "vai curtir", reescreva.

- Reels alcançam quem não segue; carrosséis rendem salvamentos; stories mantêm quem já segue.
- Os primeiros ~125 caracteres da legenda aparecem antes do "mais". O gancho tem que funcionar sozinho ali.
- 3 a 5 hashtags dimensionadas valem mais que 30 genéricas.
- Isca de engajamento ("comenta SIM", "marca 3 amigos", "curte se concorda") é rebaixada.

## Fluxo de cada post (ordem obrigatória)

| # | Etapa | Skill | Saída |
|---|---|---|---|
| 1 | Escolher superfície e fórmula pela linha editorial (tabela abaixo) | ig-content-planner | formato + fórmula + objetivo (salvar, enviar, comentar, seguir) |
| 2 | Escrever o carrossel tela por tela, ou o post único | ig-carousel-planner / ig-caption-writer | texto das telas no spec.json + legenda |
| 2b | Quando o post nasce do artigo do blog: reconstruir para o Instagram, não colar | ig-repurposer | uma ideia, gancho novo |
| 3 | Montar 3 a 5 hashtags do banco, por tema | ig-hashtag-strategist | bloco no fim da legenda |
| 4 | Passar o humanizador nas telas e nas legendas | ig-humanizer | texto limpo |
| 5 | Auditoria antes de agendar (portão) | ig-humanizer --mode audit | `auditoria.md` na pasta do post |
| 6 | Adaptar para o Facebook | (este playbook) | legenda facebook |
| 7 | Agendar no Metricool | (substitui o Publora) | posts agendados |

Post com bloqueio na auditoria **não é agendado**: corrija e audite de novo. Se não der para corrigir, registre "falhou: auditoria" no histórico.

## Linha editorial → fórmula

Fórmulas (detalhes em `skills-ref/hook-formulas.md`): IG1 número primeiro, IG2 verdade contrária, IG3 situação que o lojista reconhece, IG4 história real, IG5 lista, IG6 antes e depois, IG7 mito vs verdade, IG8 método com nome, IG9 Reel que quebra o padrão, IG10 Reel "como eu fiz".

| Linha (calendário) | Formato no Instagram | Fórmula preferida | Objetivo |
|---|---|---|---|
| 10h seg/ter: dica prática Tray / Nuvemshop | carrossel 6 a 8 telas | IG5 lista ou IG8 método | salvar |
| 10h qua: informativo de e-commerce | carrossel | IG7 mito vs verdade ou IG5 | enviar |
| 10h qui: novidades Tray e Nuvemshop | carrossel | IG5 "N novidades" (só com fonte oficial) | salvar |
| 10h sex: comercial Ubee (implantação, migração, layout) | carrossel | IG8 (o método Ubee de implantar) ou IG6 só com case autorizado | seguir |
| 10h sáb: temas prontos | carrossel | IG5 "N detalhes do tema X" | salvar, seguir |
| 10h dom: conteúdo leve | post único ou carrossel | IG3 situação do lojista ou IG7 | comentar, enviar |
| 15h seg a sex: venda de planos | post único ou carrossel curto | IG2 ou IG3, com benefício tirado da página de parceria do dia | enviar |
| 20h ter/qui/sáb: tendências | **Reel** (video.mp4) | IG9 com o número e a fonte na primeira tela | enviar |

Regras de variação na semana: nenhuma fórmula mais de 2 vezes; pelo menos 3 posts feitos para salvar e 2 para enviar. Venda de planos é linha fixa pedida pelo Guilherme (5 por semana): varie o ângulo e mantenha um único chamado por post.

## Carrossel (ig-carousel-planner)

- **Tela 1 é promessa com ciclo aberto**, nunca só título. "5 ajustes no checkout da Nuvemshop (o 4º quase ninguém faz)" em vez de "Dicas de checkout". Tipo `cover` no spec.
- Melhor ponto nas telas 2 ou 3. Um ponto por tela, lido em 2 segundos (tipos `step` e `list`).
- Última tela (`cta`): resumo que vale salvar + **um** pedido (salvar, seguir ou link na bio nas vendas). Nunca três pedidos.
- 6 a 8 telas. Menos de 4 pontos reais vira post único. Nunca encher para chegar a 10.
- No máximo um travessão (—) por tela.

## Legenda do Instagram (ig-caption-writer)

- **Gancho nos primeiros 125 caracteres** que se sustenta sem a segunda linha. Conte os caracteres.
- Uma ideia por legenda. Linhas curtas com linha em branco entre as partes.
- Carrossel: 40 a 120 palavras (as telas carregam o conteúdo). Post único: 80 a 200. Reel: 1 a 3 linhas + motivo para salvar ou enviar.
- Um número específico com referência ("16% da audiência em dez/2025, segundo a Conversion"), nunca um número inventado. Sem o dado, corte a frase.
- **0 a 3 emojis** colocados com intenção. Em listas use 1., 2., 3. em vez de 1️⃣ 2️⃣ 3️⃣.
- Fechamento: pedido específico com motivo ("salve para revisar antes da Black Friday", "mande para quem vai abrir loja este ano") ou pergunta real ("sua loja tem busca por voz?"). Nunca "o que acharam?" ou "comenta aí".
- Venda de planos: "link na bio" uma vez, no fim. Instagram não tem link clicável na legenda.
- Hashtags: 3 a 5 do banco, no fim da legenda, numa linha só.

## Humanizador (ig-humanizer)

Corte por densidade, parágrafo a parágrafo (3 marcas no mesmo parágrafo = reescrever o parágrafo):

- Vocabulário que denuncia IA em português: "alavancar", "potencializar", "desbloquear", "elevar", "jornada", "cenário", "robusto", "crucial", "fundamental", "essencial" em excesso, "no mundo digital de hoje", "no cenário atual", "mergulhar", "virar o jogo", "próximo nível", "divisor de águas".
- Revelações forçadas: "O resultado?", "A verdade é que...", "Vamos falar sobre...", "Sabe o que acontece?", "Pare de X, comece Y", "Não é só X, é Y".
- Frases em escadinha: "Simples. Rápido. Eficiente.", "Sem X. Sem Y. Só Z.", palavra solta numa linha para dramatizar.
- Sinceridade anunciada: "vou ser sincero", "papo reto", "real oficial". Diga o fato direto.
- Listas de três adjetivos vazios ("mais rápido, mais bonito, mais lucrativo").
- Depois de limpar, releia: se a correção criou escadinha ou tirou toda a personalidade, volte atrás. Legenda limpa recebe dois ou três toques, não uma reforma.

## Auditoria (portão antes do Metricool)

Grave `posts/<pasta>/auditoria.md` com uma linha por item, para Instagram e Facebook, e o veredito.

**Bloqueios (não agenda):**
- gancho não se sustenta nos 125 primeiros caracteres;
- mais de 5 hashtags no Instagram, ou hashtags no meio do texto;
- isca de engajamento ou fechamento morto ("o que acham?");
- abertura com revelação forçada ou sinceridade anunciada;
- parágrafo com 3 ou mais marcas de IA;
- mais de 1 travessão a cada 100 palavras;
- número, data ou case sem fonte ou sem autorização;
- preço de plano de terceiro, promessa de resultado, crítica a concorrente;
- tela 1 do carrossel sem promessa; carrossel com mais de 10 ou menos de 2 imagens; mídia faltando.

**Avisos (corrige se der, não bloqueia):** mais de 3 emojis, nenhum número com referência, nenhum nome próprio (Tray, Nuvemshop, Conversion...), dois pedidos no fim, hashtags todas amplas.

## Facebook

O Facebook recebe as mesmas imagens, com legenda própria:

- Mesmo gancho na primeira linha (o Facebook também corta o texto no celular).
- Tom mais conversado, 60 a 150 palavras, em parágrafos curtos.
- **Link escrito e específico**: o artigo do dia (https://ubee.com.br/blog/<slug>), a página de segmento ou tema, ou o link de parceria nas vendas. Nunca só a página inicial quando existe página do assunto. Nunca "link na bio".
- 0 a 2 hashtags (no Facebook pesam pouco). 0 a 2 emojis.
- Fechar com uma pergunta real ajuda nos comentários, que é o que mais distribui no Facebook.
- Tendências (ter/qui/sáb 20h) vão como Reel do Facebook com o mesmo video.mp4.

## Reels

- Ter/qui/sáb às 20h, a tendência sai como Reel no Instagram e no Facebook usando o video.mp4 que o renderizador já gera. Tela de capa do spec = primeiro quadro do vídeo: precisa ter o número e a promessa (IG9).
- Legenda curta (1 a 3 linhas + fonte + hashtags). O gancho está no vídeo.
- Os carrosséis continuam como carrossel. Não publicar o mesmo conteúdo como carrossel e Reel no mesmo dia.

## Stories

- Story do educativo às 18h no Instagram e no Facebook (story.png), como já está no fluxo diário.
- Stories não trazem seguidores novos; servem para quem já segue. Não tem texto na API.

## Publicação no Metricool (no lugar do Publora)

brandId 7820009, timezone America/Sao_Paulo. Mídias por link raw do GitHub, conferidas com curl (200) antes.

| Post | providers | Dados da rede | media |
|---|---|---|---|
| Carrossel / post único Instagram | instagram | `instagramData: {"type":"POST","isAiGenerated":false}` + `mediaAltText` por imagem | PNGs em ordem |
| Reel Instagram | instagram | `instagramData: {"type":"REEL","showReelOnFeed":true,"isAiGenerated":false}` | video.mp4 |
| Story Instagram | instagram | `instagramData: {"type":"STORY"}`, sem texto | story.png |
| Post Facebook | facebook | `facebookData: {"type":"POST"}` | PNGs |
| Reel Facebook | facebook | `facebookData: {"type":"REEL","title":"<título curto>"}` | video.mp4 |
| Story Facebook | facebook | `facebookData: {"type":"STORY"}`, sem texto | story.png |

Sempre `autoPublish: true`, `draft: false`, um agendamento por rede (cada rede com sua legenda). `isAiGenerated` fica false porque as artes saem do template da Ubee; se algum dia usar imagem gerada por IA, marque true (regra do calendário).

## Medição (relatório de segunda)

O número principal da semana é **salvamentos + compartilhamentos por alcance**, não curtidas. Compare por fórmula (IG1 a IG10) e por formato (carrossel, post único, Reel). Para isso, cada linha do histórico de publicações leva a fórmula usada.

## Perfil (ig-profile-optimizer)

Revisar a cada 3 meses ou quando o posicionamento mudar: campo Nome com palavra-chave buscável (ex.: "Ubee Digital | Lojas Tray"), bio de 150 caracteres com benefício + tema + prova, um link, destaques na ordem Comece aqui / Temas / Planos / Clientes / Contato, 9 primeiras posts consistentes e até 3 fixados (um de cada: melhor desempenho, oferta, quem somos). Mudanças no perfil são feitas pelo Guilherme no app.
