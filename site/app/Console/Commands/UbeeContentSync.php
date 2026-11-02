<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Sincroniza o conteúdo gerado pelo Claude no repositório público
 * gshtecnologias/ubee-social com o site ubee.com.br.
 *
 *  - blog/index.json  → artigos do blog       (/blog/{slug})
 *  - pages/index.json → guias, segmentos, temas e materiais (rotas em config/ubee.php)
 *
 * Para cada item grava storage/app/ubee/{blog|pages}/{slug}.json com o HTML pronto,
 * o FAQ extraído (para schema FAQPage) e os metadados. Também gera:
 *  - public/llms.txt          (resumo do site para buscadores com IA)
 *  - public/sitemap-ubee.xml  (sitemap só do conteúdo sincronizado)
 *
 * Uso: php artisan ubee:sync-content
 * Agendamento sugerido: a cada hora (artigos com data futura só aparecem quando a hora chega).
 */
class UbeeContentSync extends Command
{
    protected $signature = 'ubee:sync-content {--fresh : Apaga o cache local antes de sincronizar}';

    protected $description = 'Sincroniza blog e páginas do repositório ubee-social';

    public function handle(): int
    {
        $raw = rtrim(config('ubee.raw_base'), '/') . '/';
        $root = storage_path('app/ubee');

        if ($this->option('fresh')) {
            File::deleteDirectory($root);
        }

        $total = 0;
        foreach (['blog' => 'blog/index.json', 'pages' => 'pages/index.json'] as $kind => $indexPath) {
            $res = Http::timeout(20)->get($raw . $indexPath);
            if ($res->status() === 404) {
                $this->line("Sem {$indexPath} ainda, pulando.");
                File::ensureDirectoryExists("{$root}/{$kind}");
                File::put("{$root}/{$kind}/_index.json", '[]');
                continue;
            }
            if (! $res->ok()) {
                $this->error("Falha ao ler {$indexPath} (HTTP {$res->status()}).");
                return self::FAILURE;
            }

            File::ensureDirectoryExists("{$root}/{$kind}");
            $index = [];

            foreach ($res->json() as $entry) {
                $md = Http::timeout(20)->get($raw . $entry['path']);
                if (! $md->ok()) {
                    $this->warn("Pulando {$entry['path']} (HTTP {$md->status()})");
                    continue;
                }

                [$meta, $body] = $this->parseFrontMatter($md->body());
                $meta['slug'] = $meta['slug'] ?? $entry['slug'];
                $meta['type'] = $meta['type'] ?? ($kind === 'blog' ? 'artigo' : 'segmento');
                $date = Carbon::parse($meta['date'] ?? $entry['date'] ?? now());

                $item = [
                    'kind'        => $kind,
                    'type'        => $meta['type'],
                    'slug'        => $meta['slug'],
                    'title'       => $meta['title'] ?? '',
                    'description' => $meta['description'] ?? '',
                    'category'    => $meta['category'] ?? null,
                    'tags'        => $meta['tags'] ?? [],
                    'cover'       => $meta['cover'] ?? null,
                    'cta_text'    => $meta['cta_text'] ?? null,
                    'cta_url'     => $meta['cta_url'] ?? null,
                    'date'        => $date->toIso8601String(),
                    'updated'     => isset($meta['updated']) ? Carbon::parse($meta['updated'])->toIso8601String() : $date->toIso8601String(),
                    'url'         => $this->urlFor($kind, $meta['type'], $meta['slug']),
                    'html'        => Str::markdown($body, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
                    'faq'         => $this->extractFaq($body),
                ];

                File::put("{$root}/{$kind}/{$item['slug']}.json", json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $index[] = collect($item)->except(['html'])->all();
                $total++;
            }

            usort($index, fn ($a, $b) => strcmp($b['date'], $a['date']));
            File::put("{$root}/{$kind}/_index.json", json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $this->writeLlmsTxt($root);
        $this->writeSitemap($root);

        $this->info("{$total} item(ns) sincronizado(s). llms.txt e sitemap-ubee.xml atualizados.");
        return self::SUCCESS;
    }

    private function urlFor(string $kind, string $type, string $slug): string
    {
        if ($kind === 'blog') {
            return '/' . trim(config('ubee.routes.artigo', 'blog'), '/') . '/' . $slug;
        }
        $prefix = config("ubee.routes.{$type}", 'conteudo');
        return '/' . trim($prefix, '/') . '/' . $slug;
    }

    /** Lê o bloco "## Perguntas frequentes" no formato **Pergunta?** + parágrafo de resposta. */
    private function extractFaq(string $markdown): array
    {
        if (! preg_match('/^##\s+Perguntas frequentes\s*$(.*?)(?=^##\s|\z)/msi', $markdown, $m)) {
            return [];
        }
        preg_match_all('/\*\*(.+?)\*\*\s*\R(.+?)(?=\R\s*\R|\z)/s', trim($m[1]), $qa, PREG_SET_ORDER);

        return array_map(fn ($x) => [
            'q' => trim($x[1]),
            'a' => trim(preg_replace('/\s+/', ' ', strip_tags(Str::markdown($x[2])))),
        ], $qa);
    }

    private function writeLlmsTxt(string $root): void
    {
        $site = rtrim(config('app.url'), '/');
        $blog = json_decode(File::get("{$root}/blog/_index.json"), true) ?: [];
        $pages = json_decode(File::get("{$root}/pages/_index.json"), true) ?: [];
        $now = now();

        $live = fn ($items) => array_values(array_filter($items, fn ($i) => Carbon::parse($i['date'])->lte($now)));
        $line = fn ($i) => "- [{$i['title']}]({$site}{$i['url']}): {$i['description']}";

        $out = [];
        $out[] = '# Ubee Digital';
        $out[] = '';
        $out[] = '> ' . config('ubee.llms.summary');
        $out[] = '';
        foreach (config('ubee.llms.facts', []) as $fact) {
            $out[] = "- {$fact}";
        }

        $groups = [
            'Guias completos' => 'guia',
            'Lojas por segmento' => 'segmento',
            'Temas prontos' => 'tema',
            'Materiais gratuitos' => 'material',
        ];
        foreach ($groups as $title => $type) {
            $items = array_filter($live($pages), fn ($i) => $i['type'] === $type);
            if ($items) {
                $out[] = '';
                $out[] = "## {$title}";
                foreach ($items as $i) {
                    $out[] = $line($i);
                }
            }
        }

        $recent = array_slice($live($blog), 0, 30);
        if ($recent) {
            $out[] = '';
            $out[] = '## Artigos recentes';
            foreach ($recent as $i) {
                $out[] = $line($i);
            }
        }

        $out[] = '';
        $out[] = '## Contato';
        foreach (config('ubee.llms.contact', []) as $c) {
            $out[] = "- {$c}";
        }

        File::put(public_path('llms.txt'), implode("\n", $out) . "\n");
    }

    private function writeSitemap(string $root): void
    {
        $site = rtrim(config('app.url'), '/');
        $items = array_merge(
            json_decode(File::get("{$root}/blog/_index.json"), true) ?: [],
            json_decode(File::get("{$root}/pages/_index.json"), true) ?: []
        );
        $now = now();

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($items as $i) {
            if (Carbon::parse($i['date'])->gt($now)) {
                continue;
            }
            $xml[] = '  <url><loc>' . e($site . $i['url']) . '</loc><lastmod>' . Carbon::parse($i['updated'])->toDateString() . '</lastmod></url>';
        }
        $xml[] = '</urlset>';

        File::put(public_path('sitemap-ubee.xml'), implode("\n", $xml) . "\n");
    }

    /** Front matter simples: chave: valor, strings com aspas e listas [a, b]. */
    private function parseFrontMatter(string $raw): array
    {
        $raw = ltrim($raw, "\xEF\xBB\xBF");
        if (! preg_match('/^---\R(.*?)\R---\R(.*)$/s', $raw, $m)) {
            return [[], $raw];
        }

        $meta = [];
        foreach (preg_split('/\R/', $m[1]) as $line) {
            if (! preg_match('/^([A-Za-z_]+):\s*(.*)$/', $line, $kv)) {
                continue;
            }
            $value = trim($kv[2]);
            if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                $value = array_values(array_filter(array_map(
                    fn ($v) => trim($v, " \"'"),
                    explode(',', substr($value, 1, -1))
                )));
            } else {
                $value = trim($value, "\"'");
            }
            $meta[$kv[1]] = $value;
        }

        return [$meta, ltrim($m[2])];
    }
}
