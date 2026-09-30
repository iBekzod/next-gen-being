<?php

namespace App\Providers;

use App\Services\Telemetry\ErrorReporter;
use App\Services\Telemetry\FeedWriter;
use App\Services\Telemetry\TelegramTransport;
use App\Services\Telemetry\Throttle;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Xato hisobotchisini yig'adi va uni ilova hodisalariga ulaydi.
 *
 * Ulanish shu yerda jamlangan: `ErrorReporter` ning o'zi Laravel
 * hodisalari haqida bilmaydi, ya'ni uni testda yolg'iz ishlatish mumkin.
 */
class TelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ErrorReporter::class, function ($app): ErrorReporter {
            $config = (array) $app['config']->get('telemetry', []);

            $store = $config['cache_store'] ?? null;
            /** @var CacheRepository $cache */
            $cache = $store ? Cache::store($store) : Cache::store();

            return new ErrorReporter(
                config: $config,
                throttle: new Throttle(
                    cache: $cache,
                    dedupeTtl: (int) ($config['dedupe_ttl'] ?? 600),
                    hourlyCap: (int) ($config['hourly_cap'] ?? 40),
                ),
                transport: new TelegramTransport(
                    token: $config['telegram']['token'] ?? null,
                    chatId: $config['telegram']['chat_id'] ?? null,
                    topicId: $config['telegram']['topic_id'] ?? null,
                    timeout: (int) ($config['telegram']['timeout'] ?? 10),
                ),
                feed: ! empty($config['feed_path'])
                    ? new FeedWriter((string) $config['feed_path'], $cache)
                    : null,
            );
        });
    }

    public function boot(): void
    {
        // Hisobotchi o'chiq bo'lsa (masalan local) hech qanday tinglovchi
        // osilmasin: har so'rovdagi ortiqcha ish shundoq ham keraksiz.
        if (! $this->app->make(ErrorReporter::class)->enabled()) {
            return;
        }

        $this->listenForBreadcrumbs();
        $this->listenForFailedJobs();
    }

    /**
     * Nafas izlari — xatodan OLDIN nima bo'lgani.
     *
     * Ko'pincha xatoning o'zidan foydaliroq: qaysi so'rov sekin ketdi,
     * qaysi tashqi API 500 qaytardi, qaysi job boshlangan edi.
     */
    private function listenForBreadcrumbs(): void
    {
        $reporter = $this->app->make(ErrorReporter::class);

        Event::listen(QueryExecuted::class, function (QueryExecuted $event) use ($reporter): void {
            $reporter->crumb('query', mb_substr($event->sql, 0, 160).' ('.round($event->time).'ms)');
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event) use ($reporter): void {
            try {
                $reporter->crumb('http', $event->request->method().' '
                    .parse_url($event->request->url(), PHP_URL_HOST)
                    .' → '.$event->response->status());
            } catch (Throwable) {
                // Tashqi so'rov haqidagi izning yozilmagani muhim emas.
            }
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event) use ($reporter): void {
            $reporter->crumb('job', $event->job->resolveName().' boshlandi');
        });
    }

    /**
     * Yiqilgan queue job'lari.
     *
     * Ular so'rov oqimidan tashqarida yiqiladi — istisno ushlagichi ularni
     * ko'rmaydi, shuning uchun alohida ulanadi.
     */
    private function listenForFailedJobs(): void
    {
        Event::listen(JobFailed::class, function (JobFailed $event): void {
            $this->app->make(ErrorReporter::class)->report($event->exception, [
                'kind' => 'job',
                'request' => $event->job->resolveName(),
            ]);
        });
    }
}
