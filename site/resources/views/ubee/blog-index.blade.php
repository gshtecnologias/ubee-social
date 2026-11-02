@extends('layouts.app')

@php($site = rtrim(config('app.url'), '/'))

@push('head')
    @include('ubee.partials.meta', [
        'metaTitle' => $title,
        'metaDescription' => $description,
        'metaUrl' => $site . '/blog',
    ])
    @include('ubee.partials.schema')
@endpush

@section('content')
<section class="ubee-blog">
    <h1>{{ $title }}</h1>
    <p class="ubee-lead">{{ $description }}</p>
    <div class="ubee-grid">
        @foreach($items as $i)
            <a class="ubee-card" href="{{ $i['url'] }}">
                @if($i['cover'])<img src="{{ $i['cover'] }}" alt="" loading="lazy" width="500" height="750">@endif
                <span class="ubee-kicker">{{ $i['category'] }}</span>
                <h2>{{ $i['title'] }}</h2>
                <p>{{ $i['description'] }}</p>
            </a>
        @endforeach
    </div>
</section>
@endsection
