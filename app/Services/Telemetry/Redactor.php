<?php

namespace App\Services\Telemetry;

/**
 * Hisobotdan maxfiy ma'lumotni olib tashlaydi.
 *
 * Bu ixtiyoriy emas: hisobotlar oddiy Telegram chatida turadi. Agar ularda
 * JWT yoki telefon raqami ketsa, nosozlik tuzatish vositasi ma'lumot
 * sizdirish yo'liga aylanadi.
 */
final class Redactor
{
    /** Qiymati hech qachon ko'rinmasligi kerak bo'lgan kalitlar. */
    private const SECRET_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'apikey',
        'secret',
        'client_secret',
        'authorization',
        'jwt',
        'otp',
        'code',
        'pin',
    ];

    public static function text(string $value): string
    {
        $keys = implode('|', array_map('preg_quote', self::SECRET_KEYS));

        return preg_replace(
            [
                // JWT: uchta nuqta bilan ajratilgan base64 bo'lak.
                '/eyJ[A-Za-z0-9_-]{6,}\.[A-Za-z0-9_-]{6,}\.[A-Za-z0-9_-]+/',
                // `Bearer <token>`
                '/(?<=Bearer )\S+/i',
                // JSON kalit-qiymat: "password": "..."
                '/"('.$keys.')"\s*:\s*"[^"]*"/i',
                // So'rov qatori: ?token=... yoki &secret=...
                '/\b('.$keys.')=[^&\s"]+/i',
                // O'zbek telefon raqamlari.
                '/\+998\d{9}/',
                // Kartaga o'xshash 16 raqam.
                '/\b\d{16}\b/',
            ],
            [
                '<jwt>',
                '<token>',
                '"$1": "<redacted>"',
                '$1=<redacted>',
                '<phone>',
                '<card>',
            ],
            $value
        ) ?? $value;
    }

    /**
     * Massivni rekursiv tozalaydi — maxfiy kalitlar butunlay almashtiriladi.
     *
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public static function array(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SECRET_KEYS, true)) {
                $clean[$key] = '<redacted>';

                continue;
            }

            $clean[$key] = match (true) {
                is_array($value) => self::array($value),
                is_string($value) => self::text($value),
                default => $value,
            };
        }

        return $clean;
    }

    /** Uzun matnni kesadi — Telegram xabari 4096 belgidan oshmasligi kerak. */
    public static function cap(string $value, int $limit): string
    {
        return mb_strlen($value) > $limit
            ? mb_substr($value, 0, $limit).'…'
            : $value;
    }
}
