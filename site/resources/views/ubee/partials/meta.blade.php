{{-- Meta tags de SEO. Inclua dentro do <head> do layout. --}}
@php($site = rtrim(config('app.url'), '/'))
<title>{{ $metaTitle }} | Ubee Digital</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $metaUrl }}">
<meta property="og:type" content="{{ $metaType ?? 'website' }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
@if(!empty($metaImage))<meta property="og:image" content="{{ $metaImage }}">@endif
<meta property="og:locale" content="pt_BR">
<meta name="twitter:card" content="summary_large_image">
