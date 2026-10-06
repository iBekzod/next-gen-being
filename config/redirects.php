<?php

/**
 * Permanent (301) redirects for retired URLs, registered in routes/web.php
 * before posts.show. Keys and values are post slugs (no /posts/ prefix).
 */
return [
    'posts' => [
        // 2026-10-07: the four Uzbek posts were unpublished (site is English-only).
        'ai-bilan-haftasiga-10-soat-tejash-2026-yil-uchun-6-ta-vosita-va-aniq-ish-tartibi' => 'claude-code-headless-automate-dev-chores-with-claude-p',
        'ozbekistondan-turib-ai-vositalar-orqali-hamkorlik-daromadi-2026-yilda-nima-haqiqatan-ishlaydi' => 'text-to-speech-api-for-developers-piper-vs-elevenlabs',
        'elevenlabs-bilan-ozbekcha-ovoz-va-dublyaj-2026-yil-uchun-toliq-qollanma' => 'text-to-speech-api-for-developers-piper-vs-elevenlabs',
        'freelancerlar-uchun-ai-vositalar-2026-ovoz-video-matn-va-tolov' => 'text-to-speech-api-for-developers-piper-vs-elevenlabs',
    ],
];
