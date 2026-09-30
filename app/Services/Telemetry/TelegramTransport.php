<?php

namespace App\Services\Telemetry;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tayyor xabarni Telegramga uzatadi — boshqa hech narsa qilmaydi.
 *
 * Bot tokeni faqat shu yerda ishlatiladi va faqat serverda turadi: token
 * ilovaga solinsa, APK'ni ochgan har kim bot nomidan yozishi va hisobotlarni
 * o'qishi mumkin, bekor qilishning yagona yo'li esa botni qayta yaratish.
 */
final class TelegramTransport
{
    public function __construct(
        private readonly ?string $token,
        private readonly ?string $chatId,
        private readonly ?string $topicId = null,
        private readonly int $timeout = 10,
    ) {}

    public function configured(): bool
    {
        return ! empty($this->token) && ! empty($this->chatId);
    }

    /**
     * Xabarni yuboradi. Hech qachon istisno tashlamaydi — hisobot
     * yuborilmagani ilovaning ishiga ta'sir qilmasligi kerak.
     */
    public function send(string $html): bool
    {
        if (! $this->configured()) {
            return false;
        }

        try {
            $payload = [
                'chat_id' => $this->chatId,
                'text' => $html,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            // Forum-guruhda har loyihaning o'z mavzusi bor; oddiy chatda
            // bu maydon bo'lmaydi.
            if (! empty($this->topicId)) {
                $payload['message_thread_id'] = (int) $this->topicId;
            }

            $response = Http::timeout($this->timeout)
                ->asJson()
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            if ($response->failed()) {
                Log::debug('[telemetry] telegram sendMessage failed', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 300),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::debug('[telemetry] telegram transport error: '.$e->getMessage());

            return false;
        }
    }
}
