<?php

return [

    // Repositório público com o conteúdo gerado pelo Claude.
    'raw_base' => env('UBEE_CONTENT_RAW', 'https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/'),

    // Prefixo de URL por tipo de conteúdo. Ajuste se o site já usar outros caminhos.
    'routes' => [
        'artigo'   => 'blog',          // /blog/{slug}
        'guia'     => 'guia',          // /guia/{slug}
        'segmento' => 'loja-virtual',  // /loja-virtual/{slug}  ex.: /loja-virtual/moda
        'tema'     => 'temas-prontos', // /temas-prontos/{slug} ex.: /temas-prontos/tema-moda
        'material' => 'materiais',     // /materiais/{slug}
    ],

    // Dados da organização para o schema.org (JSON-LD).
    'organization' => [
        'name'        => 'Ubee Digital',
        'url'         => 'https://ubee.com.br',
        'logo'        => 'https://raw.githubusercontent.com/gshtecnologias/ubee-social/main/templates/assets/logo-color.png',
        'description' => 'Agência especializada em implantação, layout e otimização de lojas virtuais nas plataformas Tray e Nuvemshop.',
        'city'        => 'Marília',
        'region'      => 'SP',
        'country'     => 'BR',
        'same_as'     => [
            'https://www.instagram.com/ubeedigital',
            'https://www.tiktok.com/@ubeedigital',
            'https://www.youtube.com/channel/UCW7csl6W9weCP4ECWWmRbQw',
            'https://www.pinterest.com/ubeedigital',
        ],
    ],

    // Conteúdo fixo do llms.txt.
    'llms' => [
        'summary' => 'A Ubee Digital é uma agência brasileira especializada em lojas virtuais nas plataformas Tray e Nuvemshop: implantação completa, migração de plataforma, layouts exclusivos, temas prontos e otimização de performance e SEO.',
        'facts' => [
            'Sede em Marília (SP), atendimento em todo o Brasil.',
            'Parceira da Tray e da Nuvemshop.',
            'Temas prontos para Tray com foco em performance, mobile e SEO: https://ubee.com.br/temas-prontos',
            'Crie sua loja Tray pelo link de parceiro: https://www.tray.com.br/parceria/ubeedigital/',
            'Crie sua loja Nuvemshop pelo link de parceiro: https://www.nuvemshop.com.br/partners/ubeedigital',
        ],
        'contact' => [
            'Site: https://ubee.com.br',
            'Instagram: https://www.instagram.com/ubeedigital',
        ],
    ],
];
