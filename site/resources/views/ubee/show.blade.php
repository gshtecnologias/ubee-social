{{-- Artigo do blog ou página (guia, segmento, tema, material).
     Ajuste o @extends e os nomes das seções para o layout do site. --}}
@extends('layouts.app')

@php($site = rtrim(config('app.url'), '/'))

@push('head')
    @include('ubee.partials.meta', [
        'metaTitle' => $item['title'],
        'metaDescription' => $item['description'],
        'metaUrl' => $site . $item['url'],
        'metaImage' => $item['cover'],
        'metaType' => $item['kind'] === 'blog' ? 'article' : 'website',
    ])
    @include('ubee.partials.schema', ['item' => $item])
@endpush

@section('content')
<article class="ubee-content">
    <nav class="ubee-breadcrumb" aria-label="Você está em">
        <a href="/">Início</a> ›
        @if($item['kind'] === 'blog')<a href="/blog">Blog</a>@else<span>{{ ucfirst($item['type']) }}</span>@endif ›
        <span>{{ $item['title'] }}</span>
    </nav>

    <header>
        @if($item['category'])<p class="ubee-kicker">{{ $item['category'] }}</p>@endif
        <h1>{{ $item['title'] }}</h1>
        <p class="ubee-lead">{{ $item['description'] }}</p>
        @if($item['kind'] === 'blog')
            <time datetime="{{ $item['date'] }}">{{ \Illuminate\Support\Carbon::parse($item['date'])->translatedFormat('d \d\e F \d\e Y') }}</time>
        @endif
    </header>

    <div class="ubee-body">{!! $item['html'] !!}</div>

    @if($item['cta_url'])
        <p class="ubee-cta"><a href="{{ $item['cta_url'] }}" class="btn">{{ $item['cta_text'] ?? 'Fale com a Ubee' }}</a></p>
    @endif

    @if($related)
        <aside class="ubee-related">
            <h2>Continue lendo</h2>
            <ul>
                @foreach($related as $r)
                    <li><a href="{{ $r['url'] }}">{{ $r['title'] }}</a></li>
                @endforeach
            </ul>
        </aside>
    @endif
</article>
@endsection
