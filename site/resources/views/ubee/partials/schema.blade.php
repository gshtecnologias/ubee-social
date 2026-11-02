{{-- JSON-LD: Organization + página atual (Article ou WebPage) + FAQPage + BreadcrumbList --}}
@php
    $org = config('ubee.organization');
    $site = rtrim(config('app.url'), '/');

    $graph = [[
        '@type' => 'Organization',
        '@id' => $site . '/#organization',
        'name' => $org['name'],
        'url' => $org['url'],
        'logo' => $org['logo'],
        'description' => $org['description'],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $org['city'],
            'addressRegion' => $org['region'],
            'addressCountry' => $org['country'],
        ],
        'sameAs' => $org['same_as'],
    ]];

    if (isset($item)) {
        $url = $site . $item['url'];
        $graph[] = array_filter([
            '@type' => $item['kind'] === 'blog' ? 'Article' : 'WebPage',
            '@id' => $url . '#page',
            'headline' => $item['kind'] === 'blog' ? $item['title'] : null,
            'name' => $item['title'],
            'description' => $item['description'],
            'url' => $url,
            'image' => $item['cover'] ?: null,
            'datePublished' => $item['date'],
            'dateModified' => $item['updated'],
            'inLanguage' => 'pt-BR',
            'author' => ['@id' => $site . '/#organization'],
            'publisher' => ['@id' => $site . '/#organization'],
            'keywords' => $item['tags'] ? implode(', ', $item['tags']) : null,
        ]);

        if (! empty($item['faq'])) {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => $url . '#faq',
                'mainEntity' => array_map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ], $item['faq']),
            ];
        }

        $section = $item['kind'] === 'blog' ? 'Blog' : ucfirst($item['type']);
        $sectionUrl = $site . '/' . trim(config('ubee.routes.' . ($item['kind'] === 'blog' ? 'artigo' : $item['type'])), '/');
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => $site . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $section, 'item' => $sectionUrl],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $item['title'], 'item' => $url],
            ],
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
