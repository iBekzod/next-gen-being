<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return [

    /*
    |--------------------------------------------------------------------------
    | Xato hisoboti
    |--------------------------------------------------------------------------
    |
    | Ushlanmagan istisnolar Telegram botiga yuboriladi (forum-guruhdagi
    | Nextgenbeing mavzusi). Token faqat serverdagi .env da turadi.
    |
    */

    'enabled' => env('ERROR_REPORT_ENABLED', true),

    'project' => env('ERROR_REPORT_PROJECT', 'Nextgenbeing'),

    'env' => env('ERROR_REPORT_ENV', env('APP_ENV', 'production')),

    /*
    | Faqat shu muhitlardan yuboriladi. `local` ataylab yo'q: ishlab chiqish
    | paytidagi har bir xato telefonga kelib turishi kerak emas.
    */
    'environments' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ERROR_REPORT_ENVIRONMENTS', 'production,prod,staging'))
    ))),

    /*
    | Reliz — xato qaysi kod holatida chiqqanini bildiradi. Deploy skripti
    | `.env` ga yozadi; bo'lmasa `VERSION` faylidan o'qiladi.
    */
    'release' => env('ERROR_REPORT_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Nima yuborilmaydi
    |--------------------------------------------------------------------------
    |
    | 401/403/404/422 — kundalik hodisa, xato emas. Ular yuborilsa chatni
    | bosib ketadi va haqiqiy nosozliklar ko'rinmay qoladi.
    |
    */

    'ignore_status' => array_values(array_filter(array_map(
        'intval',
        explode(',', (string) env('ERROR_REPORT_IGNORE', '401,403,404,405,419,422,429'))
    ))),

    'ignore_exceptions' => [
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        AccessDeniedHttpException::class,
        NotFoundHttpException::class,
        MethodNotAllowedHttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ThrottleRequestsException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bo'g'ish
    |--------------------------------------------------------------------------
    |
    | Bir xil xato tsiklda takrorlansa daqiqada yuzlab xabar kelardi.
    | Ikki qavat: barmoq izi bo'yicha oyna, va umumiy soatlik chegara.
    |
    */

    'dedupe_ttl' => (int) env('ERROR_REPORT_DEDUPE_TTL', 600),

    'hourly_cap' => (int) env('ERROR_REPORT_HOURLY_CAP', 40),

    /*
    | Bo'g'ish hisoblagichlari qayerda turadi. Bo'sh — ilovaning asosiy
    | keshi (prod'da redis).
    */
    'cache_store' => env('ERROR_REPORT_CACHE_STORE'),

    /*
    | Jarvis lentasi — har hodisa JSON qatori bo'lib shu faylga yoziladi,
    | egasining kompyuteridagi kuzatuvchi uni ssh orqali o'qiydi (bot
    | guruhdagi xabarlarni qayta o'qiy olmaydi). Bo'sh qiymat — o'chiq.
    */
    'feed_path' => env('ERROR_REPORT_FEED_PATH', storage_path('logs/error-feed.jsonl')),

    /*
    |--------------------------------------------------------------------------
    | Telegram
    |--------------------------------------------------------------------------
    |
    | `topic_id` — forum-guruhdagi mavzu (har loyihaga bittadan). Bo'sh
    | bo'lsa xabar chatning umumiy oqimiga tushadi.
    |
    */

    'telegram' => [
        'token' => env('TELEGRAM_ERROR_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_ERROR_CHAT_ID'),
        'topic_id' => env('TELEGRAM_ERROR_TOPIC_ID'),
        'timeout' => (int) env('TELEGRAM_ERROR_TIMEOUT', 10),
    ],

];
