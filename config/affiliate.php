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
    | Every entry is also reachable as /go/<key> (GoController): posts link to
    | /go/<key>, the redirect goes to `url` when set, otherwise to `homepage`,
    | and each click is logged in outbound_clicks. Swapping a program = change
    | the env value + `php artisan config:cache`; no post has to be edited.
    |
    */
    'links' => [
        'elevenlabs' => [
            'patterns' => ['elevenlabs', 'eleven labs'],
            'url' => env('AFFILIATE_ELEVENLABS_URL'),
            'anchor' => 'ElevenLabs',
            'homepage' => 'https://elevenlabs.io',
        ],
        'jasper' => [
            'patterns' => ['jasper ai', 'jasper'],
            'url' => env('AFFILIATE_JASPER_URL'),
            'anchor' => 'Jasper',
            'homepage' => 'https://www.jasper.ai',
        ],
        'zapier' => [
            'patterns' => ['zapier automation', 'zapier'],
            'url' => env('AFFILIATE_ZAPIER_URL'),
            'anchor' => 'Zapier',
            'homepage' => 'https://zapier.com',
        ],
        'make' => [
            'patterns' => ['make.com', 'make automation', 'integromat'],
            'url' => env('AFFILIATE_MAKE_URL'),
            'anchor' => 'Make',
            'homepage' => 'https://www.make.com',
        ],
        'airtable' => [
            'patterns' => ['airtable'],
            'url' => env('AFFILIATE_AIRTABLE_URL'),
            'anchor' => 'Airtable',
            'homepage' => 'https://www.airtable.com',
        ],
        'notion' => [
            'patterns' => ['notion'],
            'url' => env('AFFILIATE_NOTION_URL'),
            'anchor' => 'Notion',
            'homepage' => 'https://www.notion.com',
        ],
        'hostinger' => [
            'patterns' => ['hostinger'],
            'url' => env('AFFILIATE_HOSTINGER_URL'),
            'anchor' => 'Hostinger',
            'homepage' => 'https://www.hostinger.com',
        ],
        // Programs below are planned (see memory note ngb-revival-affiliates):
        // the /go/ link works today and points at the homepage until the
        // referral URL is filled in.
        'murf' => [
            'patterns' => ['murf.ai', 'murf ai'],
            'url' => env('AFFILIATE_MURF_URL'),
            'anchor' => 'Murf AI',
            'homepage' => 'https://murf.ai',
        ],
        'apify' => [
            'patterns' => ['apify'],
            'url' => env('AFFILIATE_APIFY_URL'),
            'anchor' => 'Apify',
            'homepage' => 'https://apify.com',
        ],
        'digitalocean' => [
            'patterns' => ['digitalocean', 'digital ocean'],
            'url' => env('AFFILIATE_DIGITALOCEAN_URL'),
            'anchor' => 'DigitalOcean',
            'homepage' => 'https://www.digitalocean.com',
        ],
        'invideo' => [
            'patterns' => ['invideo'],
            'url' => env('AFFILIATE_INVIDEO_URL'),
            'anchor' => 'InVideo',
            'homepage' => 'https://invideo.io',
        ],
        'semrush' => [
            'patterns' => ['semrush'],
            'url' => env('AFFILIATE_SEMRUSH_URL'),
            'anchor' => 'Semrush',
            'homepage' => 'https://www.semrush.com',
        ],
        'writesonic' => [
            'patterns' => ['writesonic'],
            'url' => env('AFFILIATE_WRITESONIC_URL'),
            'anchor' => 'Writesonic',
            'homepage' => 'https://writesonic.com',
        ],
        'fliki' => [
            'patterns' => ['fliki'],
            'url' => env('AFFILIATE_FLIKI_URL'),
            'anchor' => 'Fliki',
            'homepage' => 'https://fliki.ai',
        ],
        'synthesia' => [
            'patterns' => ['synthesia'],
            'url' => env('AFFILIATE_SYNTHESIA_URL'),
            'anchor' => 'Synthesia',
            'homepage' => 'https://www.synthesia.io',
        ],
        'payoneer' => [
            'patterns' => ['payoneer'],
            'url' => env('AFFILIATE_PAYONEER_URL'),
            'anchor' => 'Payoneer',
            'homepage' => 'https://www.payoneer.com',
        ],
        'capcut' => [
            'patterns' => [],
            'url' => env('AFFILIATE_CAPCUT_URL'),
            'anchor' => 'CapCut',
            'homepage' => 'https://www.capcut.com',
        ],
        'descript' => [
            'patterns' => [],
            'url' => env('AFFILIATE_DESCRIPT_URL'),
            'anchor' => 'Descript',
            'homepage' => 'https://www.descript.com',
        ],
        'gamma' => [
            'patterns' => [],
            'url' => env('AFFILIATE_GAMMA_URL'),
            'anchor' => 'Gamma',
            'homepage' => 'https://gamma.app',
        ],
    ],

];
