<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Importa os artigos gerados pelo Claude no repositório gshtecnologias/ubee-social.
 *
 * Instalação:
 *   1. Copie para app/Console/Commands/ImportUbeeBlogPosts.php
 *   2. Ajuste o model (App\Models\Post) e os nomes das colunas em upsertPost()
 *   3. Agende em routes/console.php:
 *        Schedule::command('ubee:import-blog')->dailyAt('09:00')->timezone('America/Sao_Paulo');
 *
 * Uso manual:  php artisan ubee:import-blog            (importa os 10 mais recentes)
 *              php artisan ubee:import-blog --all      (importa todos)
 */
class ImportUbeeBlogPosts extends Command
{
    protected $signature = 'ubee:import-blog {--all : Importa todos os artigos do índice}';

    protected $description = 'Importa artigos do blog gerados no repositório ubee-social';

    private const RAW = 'https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/';

    public function handle(): int
    {
        $index = Http::timeout(20)->get(self::RAW . 'blog/index.json');

        if (! $index->ok()) {
            $this->error('Não consegui ler blog/index.json (HTTP ' . $index->status() . ').');
            return self::FAILURE;
        }

        $entries = collect($index->json());
        if (! $this->option('all')) {
            $entries = $entries->take(10);
        }

        $imported = 0;
        foreach ($entries as $entry) {
            $md = Http::timeout(20)->get(self::RAW . $entry['path']);
            if (! $md->ok()) {
                $this->warn("Pulando {$entry['path']} (HTTP {$md->status()})");
                continue;
            }

            [$meta, $body] = $this->parseFrontMatter($md->body());
            $publishAt = Carbon::parse($meta['date'] ?? $entry['date'] ?? now());

            // Não publica artigo com data futura.
            if ($publishAt->isFuture()) {
                continue;
            }

            $this->upsertPost($meta, $body, $publishAt);
            $imported++;
        }

        $this->info("{$imported} artigo(s) importado(s) ou atualizado(s).");
        return self::SUCCESS;
    }

    /** Ajuste aqui para o seu model e suas colunas. */
    private function upsertPost(array $meta, string $markdown, Carbon $publishAt): void
    {
        Post::updateOrCreate(
            ['slug' => $meta['slug']],
            [
                'title'        => $meta['title'] ?? '',
                'excerpt'      => $meta['description'] ?? '',
                'body'         => Str::markdown($markdown, ['html_input' => 'strip']),
                'category'     => $meta['category'] ?? null,
                'tags'         => $meta['tags'] ?? [],
                'cover_url'    => $meta['cover'] ?? null,
                'published_at' => $publishAt,
            ]
        );
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
