<?php

// Rotas do conteúdo sincronizado. Inclua no fim de routes/web.php:
//     require __DIR__.'/ubee.php';

use App\Http\Controllers\UbeeContentController;
use Illuminate\Support\Facades\Route;

$r = config('ubee.routes');

Route::get('/' . $r['artigo'], [UbeeContentController::class, 'blogIndex'])->name('ubee.blog');
Route::get('/' . $r['artigo'] . '/{slug}', [UbeeContentController::class, 'blogShow'])->name('ubee.blog.show');

foreach (['guia', 'segmento', 'tema', 'material'] as $type) {
    Route::get('/' . $r[$type] . '/{slug}', [UbeeContentController::class, 'pageShow'])
        ->defaults('type', $type)
        ->where('slug', '[a-z0-9-]+')
        ->name("ubee.{$type}");
}
