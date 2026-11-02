<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Exibe o conteúdo sincronizado por `php artisan ubee:sync-content`
 * (arquivos em storage/app/ubee). Itens com data futura retornam 404.
 */
class UbeeContentController extends Controller
{
    public function blogIndex()
    {
        $items = $this->live($this->index('blog'));

        return view('ubee.blog-index', [
            'items' => array_slice($items, 0, 60),
            'title' => 'Blog da Ubee Digital: Tray, Nuvemshop e e-commerce',
            'description' => 'Dicas práticas de Tray, Nuvemshop, SEO, frete, conversão e tendências do e-commerce brasileiro.',
        ]);
    }

    public function blogShow(string $slug)
    {
        $item = $this->item('blog', $slug);

        return view('ubee.show', [
            'item' => $item,
            'related' => $this->related($item),
        ]);
    }

    public function pageShow(string $type, string $slug)
    {
        $item = $this->item('pages', $slug);
        abort_unless($item['type'] === $type, 404);

        return view('ubee.show', [
            'item' => $item,
            'related' => $this->related($item),
        ]);
    }

    private function index(string $kind): array
    {
        $file = storage_path("app/ubee/{$kind}/_index.json");
        return File::exists($file) ? (json_decode(File::get($file), true) ?: []) : [];
    }

    private function live(array $items): array
    {
        return array_values(array_filter($items, fn ($i) => Carbon::parse($i['date'])->lte(now())));
    }

    private function item(string $kind, string $slug): array
    {
        abort_unless(preg_match('/^[a-z0-9-]+$/', $slug), 404);
        $file = storage_path("app/ubee/{$kind}/{$slug}.json");
        abort_unless(File::exists($file), 404);

        $item = json_decode(File::get($file), true);
        abort_if(Carbon::parse($item['date'])->gt(now()), 404);

        return $item;
    }

    /** Até 4 itens da mesma categoria (ou os mais recentes), para links internos. */
    private function related(array $item): array
    {
        $all = $this->live(array_merge($this->index('blog'), $this->index('pages')));
        $all = array_values(array_filter($all, fn ($i) => $i['slug'] !== $item['slug']));
        $same = array_values(array_filter($all, fn ($i) => ($i['category'] ?? null) === ($item['category'] ?? null)));

        return array_slice($same ?: $all, 0, 4);
    }
}
