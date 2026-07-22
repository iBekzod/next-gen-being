<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Welcome / Onboarding Sequence
    |--------------------------------------------------------------------------
    | An automated email series sent AFTER a subscriber confirms (double
    | opt-in). Step 1 fires immediately on verification; later steps are sent
    | by the scheduled `newsletter:send-onboarding` command once `delay_days`
    | have passed since the previous step for that subscriber.
    |
    | `delay_days` is measured from the PREVIOUS step, so the timeline below is
    | day 0 → day 2 → day 4 → day 6.
    */
    'onboarding' => [
        'enabled' => filter_var(env('NEWSLETTER_ONBOARDING_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'steps' => [
            1 => [
                'view' => 'emails.newsletter.onboarding.welcome',
                'subject' => 'Welcome — your free Field Guide is inside 🎁',
                'delay_days' => 0,
            ],
            2 => [
                'view' => 'emails.newsletter.onboarding.start-here',
                'subject' => 'Start here: the guides worth your time',
                'delay_days' => 2,
            ],
            3 => [
                'view' => 'emails.newsletter.onboarding.tools',
                'subject' => 'The AI tools I actually pay for',
                'delay_days' => 2,
            ],
            4 => [
                'view' => 'emails.newsletter.onboarding.offer',
                'subject' => 'A quick question (and something for you)',
                'delay_days' => 2,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Links used inside onboarding emails
    |--------------------------------------------------------------------------
    | Swap these for real affiliate / product URLs as those land (plan tasks
    | 1.2 and 2.1). A null value makes the email skip that section, so the
    | sequence degrades gracefully until the links exist.
    */
    'links' => [
        'lead_magnet' => env('NEWSLETTER_LEAD_MAGNET_URL', '/guides/ai-assisted-developer-field-guide.html'),
        'featured_tool' => env('NEWSLETTER_FEATURED_TOOL_URL'),        // real affiliate link (ElevenLabs/Jasper/…)
        'featured_tool_name' => env('NEWSLETTER_FEATURED_TOOL_NAME'),  // e.g. "ElevenLabs"
        'flagship_product' => env('NEWSLETTER_FLAGSHIP_PRODUCT_URL'),  // e.g. /resources/<slug>
    ],

    // Onboarding emails invite replies (boosts deliverability + engagement).
    'reply_to' => env('NEWSLETTER_REPLY_TO', env('MAIL_FROM_ADDRESS')),
];
