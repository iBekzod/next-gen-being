<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Affiliate programs
    |--------------------------------------------------------------------------
    |
    | Only real, paying affiliate programs belong here. ChatGPT / Claude /
    | Midjourney / Perplexity were removed because they have NO public affiliate
    | program — linking to them earned $0.
    |
    | Fill each *_URL with YOUR referral link from that program's dashboard after
    | you sign up. A tool whose URL is empty is skipped entirely, so the
    | link-inserter never adds a non-earning link.
    |
    */
    'links' => [
        'elevenlabs' => [
            'patterns' => ['elevenlabs', 'eleven labs'],
            'url' => env('AFFILIATE_ELEVENLABS_URL'),
            'anchor' => 'ElevenLabs',
        ],
        'jasper' => [
            'patterns' => ['jasper ai', 'jasper'],
            'url' => env('AFFILIATE_JASPER_URL'),
            'anchor' => 'Jasper',
        ],
        'zapier' => [
            'patterns' => ['zapier automation', 'zapier'],
            'url' => env('AFFILIATE_ZAPIER_URL'),
            'anchor' => 'Zapier',
        ],
        'make' => [
            'patterns' => ['make.com', 'make automation', 'integromat'],
            'url' => env('AFFILIATE_MAKE_URL'),
            'anchor' => 'Make',
        ],
        'airtable' => [
            'patterns' => ['airtable'],
            'url' => env('AFFILIATE_AIRTABLE_URL'),
            'anchor' => 'Airtable',
        ],
        'notion' => [
            'patterns' => ['notion'],
            'url' => env('AFFILIATE_NOTION_URL'),
            'anchor' => 'Notion',
        ],
        'hostinger' => [
            'patterns' => ['hostinger'],
            'url' => env('AFFILIATE_HOSTINGER_URL'),
            'anchor' => 'Hostinger',
        ],
    ],

];
