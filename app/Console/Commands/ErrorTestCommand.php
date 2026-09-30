<?php

namespace App\Console\Commands;

use App\Services\Telemetry\ErrorReporter;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Xato hisoboti zanjirini uchidan-uchiga tekshiradi.
 *
 * Sozlamani ko'z bilan tekshirib bo'lmaydi: token to'g'ri, lekin bot
 * guruhga qo'shilmagan bo'lishi mumkin; chat id to'g'ri, lekin mavzu
 * o'chirilgan bo'lishi mumkin. Yagona ishonchli tekshiruv — haqiqiy
 * xabar yuborib ko'rish.
 */
class ErrorTestCommand extends Command
{
    protected $signature = 'error:test {--message= : Yuboriladigan xato matni}';

    protected $description = 'Telegram botiga sinov xato hisobotini yuboradi';

    public function handle(ErrorReporter $reporter): int
    {
        $config = (array) config('telemetry');

        $this->line('Loyiha:  '.($config['project'] ?? '—'));
        $this->line('Muhit:   '.($config['env'] ?? '—').'  (ruxsat: '.implode(', ', $config['environments'] ?? []).')');
        $this->line('Chat:    '.($config['telegram']['chat_id'] ?? '—')
            .(empty($config['telegram']['topic_id']) ? '' : '  mavzu: '.$config['telegram']['topic_id']));
        $this->line('Token:   '.(empty($config['telegram']['token']) ? 'YO\'Q' : 'bor'));
        $this->newLine();

        if (! $reporter->enabled()) {
            $this->error('Hisobotchi o\'chiq: token/chat yo\'q yoki bu muhit ruxsat etilmagan.');
            $this->line('Tekshiring: TELEGRAM_ERROR_BOT_TOKEN, TELEGRAM_ERROR_CHAT_ID, ERROR_REPORT_ENV.');

            return self::FAILURE;
        }

        $reporter->crumb('console', 'php artisan error:test');
        $reporter->crumb('note', 'bu sinov xabari, haqiqiy nosozlik emas');

        $message = (string) ($this->option('message')
            ?: 'Sinov xatosi — error:test ('.now()->format('H:i:s').')');

        $sent = $reporter->report(new RuntimeException($message), ['kind' => 'console']);

        if ($sent) {
            $this->info('Yuborildi — Telegramni tekshiring.');

            return self::SUCCESS;
        }

        $this->warn('Yuborilmadi. Sabablari: bir xil xato yaqinda yuborilgan (bo\'g\'ish), '
            .'soatlik chegara, yoki Telegram rad etdi — log\'ni ko\'ring.');

        return self::FAILURE;
    }
}
