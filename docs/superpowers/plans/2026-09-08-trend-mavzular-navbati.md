# Trend mavzular navbati (C2) — Amalga oshirish rejasi

> **Agentik ishchilar uchun:** MAJBURIY SUB-SKILL: bu rejani vazifama-vazifa bajarish uchun superpowers:subagent-driven-development (tavsiya etiladi) yoki superpowers:executing-plans skill'idan foydalaning. Qadamlar checkbox (`- [ ]`) sintaksisida kuzatiladi.

**Maqsad:** Post mavzularini qo'lda yozilgan ro'yxatdan emas, **haqiqatda kuzatilgan trenddan** olish — va generatsiya qilingan postlar o'sha manbalarga havola qilsin.

**Arxitektura:** Loyihada to'liq kontent-yig'ish quyi tizimi allaqachon bor va **uxlab yotibdi** — `ContentSource`, `CollectedContent`, `ScrapeAllSourcesCommand`, `ScrapeSingleSourceJob`, `ContentDeduplicationService`. Uni yangidan yozmaymiz: jadvalga ulaymiz va ustiga bitta yupqa qatlam qo'shamiz. Klasterlash ham yozilmaydi — dedup xizmati `is_duplicate` va `duplicate_of` ustunlarini to'ldiradi, ya'ni bir mavzu haqidagi maqolalar guruhi tayyor tuzilma. Yangi `TopicQueueService` shu guruhlarni ballaydi va eng yuqorisini qaytaradi.

**Nega aynan shunday ballanadi:** `collected_content` da hech qanday jalb qilish signali yo'q — na ovoz, na yulduz, na izoh soni. Shuning uchun "HN ovozlari bo'yicha saralash" **imkonsiz**. O'rniga trend signali sifatida **mustaqil manbalar tasdig'i** ishlatiladi: bir mavzu bir necha alohida manbada bir vaqtda chiqsa — bu ta'rifi bo'yicha trend. Bu yagona saytning ovoz sanog'idan ko'ra ishonchliroq va sxema o'zgarishini talab qilmaydi.

**Texnologiyalar:** Laravel 12, PHP 8.4, PostgreSQL, PHPUnit 11, Eloquent, mavjud queue (`ScrapeSingleSourceJob`).

**Spec:** `docs/superpowers/specs/2026-09-02-marketplace-audience-engine-design.md` — §6 (C2).

**Ishchi katalog:** `d:\projects\MyProjects\nextgenbeing\next-gen-being`.

**Test buyrug'i:** `php artisan test --filter=<TestClassOrMethod>`. To'liq to'plam: `php artisan test`.

## Boshlashdan oldingi shart

```bash
docker compose up -d ngb-database
php artisan test
```

Hozirgi holat: **103 ta test yashil**. Shu sondan pastga tushmasin.

**MUHIM — parallel test yuritmang.** Barcha testlar bitta `nextgenbeing_test` bazasini bo'lishadi. Ikkita `php artisan test` bir vaqtda ishlasa, ular bir-birining ma'lumotini o'chiradi va o'nlab test sababsiz yiqiladi. Fonda test qoldirmang.

## Global cheklovlar

- **Migratsiya YO'Q.** `collected_content` ga signal ustuni qo'shilmaydi (spec §6 buni ataylab rad etgan).
- `.env` o'zgartirilmaydi, `BLOG_AUTO_PUBLISH` ga tegilmaydi.
- Checkout quvuriga tegilmaydi.
- **`PublishGate` ga tegilmaydi.** Darvozalar bu rejaning ko'lamidan tashqarida.
- Scraping **muloyim** bo'lsin: mavjud `rate_limit_per_sec` va `ScrapeSingleSourceJob` ishlatiladi, yangi HTTP mijoz yozilmaydi.
- Kommit xabarlari ingliz tilida, Conventional Commits, oxirida:
  `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`

---

## Fayl tuzilmasi

**Yaratiladi:**
- `app/Services/Content/TopicQueueService.php` — klasterlarni ballaydi va nomzod mavzular qaytaradi.
- `app/Console/Commands/RankTopicsCommand.php` — `content:rank-topics`.
- `tests/Feature/Content/TopicQueueServiceTest.php`
- `tests/Feature/Content/RankTopicsCommandTest.php`
- `tests/Feature/Content/ScrapeScheduleTest.php`

**O'zgartiriladi:**
- `routes/console.php` — scraping va ranking jadvalga qo'shiladi.
- `app/Console/Commands/GenerateAiPost.php` — `selectTrendingTopic()` navbatdan o'qiydi.
- `app/Console/Commands/ContentHealthCheck.php` — scraping eskirganini aniqlaydi.
- `routes/api.php` + `app/Http/Controllers/Api/BotPostController.php` — botga mavzu beradigan endpoint.

---

## Vazifa 1: Manbalarni yoqish va scraping'ni jadvalga qo'yish

Quyi tizim yozilgan, lekin `routes/console.php` da **birorta ham scrape yozuvi yo'q**. Ya'ni u hech qachon ishlamagan.

**Fayllar:**
- O'zgartirish: `routes/console.php`
- Test: `tests/Feature/Content/ScrapeScheduleTest.php`

**Interfeyslar:**
- Beradi: har 6 soatda to'ldiriladigan `collected_content` jadvali — Vazifa 2 shundan o'qiydi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Content/ScrapeScheduleTest.php`:

```php
<?php

namespace Tests\Feature\Content;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapeScheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @return string[] */
    private function scheduledCommands(): array
    {
        return array_map(
            fn ($e) => (string) $e->command,
            app(Schedule::class)->events()
        );
    }

    public function test_scraping_jadvalga_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:scrape-all')
        );

        $this->assertNotEmpty($found, 'content:scrape-all jadvalda yo\'q');
    }

    public function test_mavzu_ranking_jadvalga_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:rank-topics')
        );

        $this->assertNotEmpty($found, 'content:rank-topics jadvalda yo\'q');
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=ScrapeScheduleTest`
Kutilgan natija: ikkala test ham FAIL — jadvalda bu buyruqlar yo'q.

> Ikkinchi test Vazifa 3 gacha yiqilib turadi — bu kutilgan. Bu vazifada faqat birinchisini yashil qiling.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`routes/console.php` ga, `content:drip` yozuvi yonига:

```php
// ========================================
// TREND MAVZULAR (C2)
// ========================================
// Kontent manbalarini yig'ish. Bu quyi tizim 2026-01 dan beri kodda bor edi,
// lekin hech qachon jadvalga qo'yilmagan — ya'ni bir marta ham ishlamagan.
// --async: har bir manba alohida queue job'ga tushadi, shuning uchun bitta
// sekin sayt qolganlarini to'xtatib qo'ymaydi.
Schedule::command('content:scrape-all', ['--async', '--limit=40'])
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('content:scrape-all failed');
    });
```

- [ ] **Qadam 4: Manbalar bazada borligini tasdiqlang**

Bu buyruq faol manbalarsiz `1` qaytaradi. Mahalliy bazada tekshiring:

```bash
php artisan content:init-sources
php artisan tinker --execute="echo App\Models\ContentSource::active()->count();"
```

Kutilgan natija: 10 ta manba (Hacker News, GitHub Trending, ArXiv, Product Hunt, Dev.to, TechCrunch, The Verge, Wired, CSS-Tricks, Smashing).

Agar 0 chiqsa, `SourceWhitelistService::initializeDefaultSources()` ni o'qing va hisobotda nima topganingizni yozing — implementatsiyani o'zgartirmang.

- [ ] **Qadam 5: Birinchi testni ishga tushiring**

Buyruq: `php artisan test --filter=test_scraping_jadvalga_qoyilgan`
Kutilgan natija: PASS.

- [ ] **Qadam 6: Commit qiling**

```bash
git add routes/console.php tests/Feature/Content/ScrapeScheduleTest.php
git commit -m "feat(content): schedule the dormant source scraper

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 2: `TopicQueueService` — klasterlarni ballash

**Fayllar:**
- Yaratish: `app/Services/Content/TopicQueueService.php`
- Test: `tests/Feature/Content/TopicQueueServiceTest.php`

**Interfeyslar:**
- Beradi: `TopicQueueService::topCandidates(int $limit = 10, int $windowDays = 7): Collection`.
  Har bir element: `['title' => string, 'category' => string, 'score' => float, 'cluster_size' => int, 'sources' => array<int, array{title:string,url:string,published_at:?string}>]`.
  Vazifa 3, 4, 5 va 7 shu shakldan foydalanadi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Content/TopicQueueServiceTest.php`:

```php
<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentSource;
use App\Services\Content\TopicQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    private function source(string $name, int $trust = 90): ContentSource
    {
        return ContentSource::create([
            'name' => $name,
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.example',
            'category' => 'news',
            'trust_level' => $trust,
            'scraping_enabled' => true,
        ]);
    }

    private function item(ContentSource $s, string $title, ?int $primaryId = null, int $ageHours = 2): CollectedContent
    {
        return CollectedContent::create([
            'content_source_id' => $s->id,
            'external_url' => 'https://x.example/' . uniqid('', true),
            'title' => $title,
            'excerpt' => 'Excerpt for ' . $title,
            'full_content' => str_repeat('Body text about the subject. ', 20),
            'content_type' => 'article',
            'published_at' => now()->subHours($ageHours),
            'is_duplicate' => $primaryId !== null,
            'duplicate_of' => $primaryId,
        ]);
    }

    public function test_kop_manbali_klaster_bitta_manbalidan_yuqori_turadi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $c = $this->source('Gamma');

        // Uch mustaqil manba bir mavzu haqida — bu trend.
        $primary = $this->item($a, 'Postgres 18 changes connection pooling');
        $this->item($b, 'Postgres 18 connection pooling rework', $primary->id);
        $this->item($c, 'What Postgres 18 does to pooling', $primary->id);

        // Bitta manba, yolg'iz.
        $this->item($a, 'A niche library nobody else covered');

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertNotEmpty($out);
        $this->assertSame('Postgres 18 changes connection pooling', $out->first()['title']);
        $this->assertSame(3, $out->first()['cluster_size']);
    }

    public function test_bitta_manba_ozini_trendga_aylantira_olmaydi(): void
    {
        $a = $this->source('Alpha');

        // Ayni manba bir mavzuni besh marta yozsa ham — bu trend emas.
        $primary = $this->item($a, 'Alpha keeps writing about the same thing');
        for ($i = 0; $i < 4; $i++) {
            $this->item($a, 'Same thing again ' . $i, $primary->id);
        }

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertTrue(
            $out->isEmpty(),
            'yagona manba klasteri nomzod bo\'lib qoldi'
        );
    }

    public function test_yangiroq_klaster_yuqoriroq_ballanadi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $old = $this->item($a, 'Older subject', null, 24 * 6);
        $this->item($b, 'Older subject echo', $old->id, 24 * 6);

        $new = $this->item($a, 'Fresher subject', null, 2);
        $this->item($b, 'Fresher subject echo', $new->id, 2);

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertSame('Fresher subject', $out->first()['title']);
    }

    public function test_oyna_tashqarisidagi_kontent_hisobga_olinmaydi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $old = $this->item($a, 'Ancient news', null, 24 * 30);
        $this->item($b, 'Ancient news echo', $old->id, 24 * 30);

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertTrue($out->isEmpty(), 'oyna tashqarisidagi klaster qaytdi');
    }

    public function test_manbalar_havolalari_qaytariladi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $primary = $this->item($a, 'Subject with sources');
        $this->item($b, 'Subject with sources echo', $primary->id);

        $first = app(TopicQueueService::class)->topCandidates(5, 7)->first();

        $this->assertCount(2, $first['sources']);
        foreach ($first['sources'] as $src) {
            $this->assertArrayHasKey('url', $src);
            $this->assertArrayHasKey('title', $src);
            $this->assertStringStartsWith('http', $src['url']);
        }
    }

    public function test_bosh_oyna_bosh_massiv_qaytaradi(): void
    {
        $this->assertTrue(app(TopicQueueService::class)->topCandidates(5, 7)->isEmpty());
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=TopicQueueServiceTest`
Kutilgan natija: FAIL — `Class "App\Services\Content\TopicQueueService" not found`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Services/Content/TopicQueueService.php`:

```php
<?php

namespace App\Services\Content;

use App\Models\CollectedContent;
use Illuminate\Support\Collection;

/**
 * Yig'ilgan kontentdan trend mavzular navbatini yasaydi.
 *
 * `collected_content` da hech qanday jalb qilish signali yo'q — na ovoz,
 * na yulduz, na izoh. Shuning uchun trend signali sifatida MUSTAQIL
 * MANBALAR TASDIG'I ishlatiladi: bir mavzu bir necha alohida manbada bir
 * vaqtda chiqsa, bu ta'rifi bo'yicha trend (spec §6).
 *
 * Klasterlash bu yerda yozilmaydi: ContentDeduplicationService allaqachon
 * `is_duplicate` va `duplicate_of` ustunlarini to'ldiradi, ya'ni bir mavzu
 * haqidagi maqolalar guruhi tayyor.
 */
class TopicQueueService
{
    /** Trend deb hisoblash uchun kerak bo'lgan eng kam MUSTAQIL manba soni. */
    public const MIN_INDEPENDENT_SOURCES = 2;

    public const DEFAULT_WINDOW_DAYS = 7;

    /**
     * @return Collection<int, array{title:string,category:string,score:float,cluster_size:int,sources:array}>
     */
    public function topCandidates(int $limit = 10, int $windowDays = self::DEFAULT_WINDOW_DAYS): Collection
    {
        $since = now()->subDays($windowDays);

        $rows = CollectedContent::with('source')
            ->where('published_at', '>=', $since)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // Klaster kaliti: dublikat bo'lsa — asl yozuv, aks holda o'zi.
        $clusters = $rows->groupBy(fn (CollectedContent $c) => $c->duplicate_of ?: $c->id);

        return $clusters
            ->map(fn (Collection $items) => $this->scoreCluster($items, $windowDays))
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /** @param Collection<int, CollectedContent> $items */
    private function scoreCluster(Collection $items, int $windowDays): ?array
    {
        $sourceIds = $items->pluck('content_source_id')->unique();

        // Yagona manba o'zini trendga aylantira olmaydi.
        if ($sourceIds->count() < self::MIN_INDEPENDENT_SOURCES) {
            return null;
        }

        $primary = $items->firstWhere('duplicate_of', null) ?? $items->first();

        $meanTrust = (float) $items
            ->map(fn (CollectedContent $c) => (int) ($c->source->trust_level ?? 50))
            ->avg();

        $newest = $items->max('published_at');
        $ageDays = $newest ? max(0.0, (float) $newest->diffInDays(now(), true)) : (float) $windowDays;
        $recency = max(0.1, 1.0 - ($ageDays / max(1, $windowDays)));

        $score = $sourceIds->count() * $meanTrust * $recency;

        return [
            'title' => (string) $primary->title,
            'category' => (string) ($primary->source->category ?? 'news'),
            'score' => round($score, 2),
            'cluster_size' => $items->count(),
            'sources' => $items->map(fn (CollectedContent $c) => [
                'title' => (string) $c->title,
                'url' => (string) $c->external_url,
                'published_at' => optional($c->published_at)->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
```

> **Diqqat:** `cluster_size` — klasterdagi **yozuvlar** soni, `MIN_INDEPENDENT_SOURCES` esa **manbalar** soniga qo'llanadi. Ikkalasi har xil narsa; testlar ikkalasini ham tekshiradi.

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=TopicQueueServiceTest`
Kutilgan natija: PASS — 6 ta test.

Agar `source` munosabati mavjud bo'lmasa, `CollectedContent` modelini o'qing va to'g'ri munosabat nomini ishlating; nima o'zgartirganingizni hisobotda yozing.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Services/Content/TopicQueueService.php tests/Feature/Content/TopicQueueServiceTest.php
git commit -m "feat(content): score trending topics by independent-source corroboration

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 3: `content:rank-topics` buyrug'i

**Fayllar:**
- Yaratish: `app/Console/Commands/RankTopicsCommand.php`
- O'zgartirish: `routes/console.php`
- Test: `tests/Feature/Content/RankTopicsCommandTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankTopicsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomzodlar_bolmasa_toza_chiqadi(): void
    {
        $this->artisan('content:rank-topics')
            ->expectsOutputToContain('nomzod')
            ->assertSuccessful();
    }

    public function test_nomzodlarni_chiqaradi(): void
    {
        $a = ContentSource::create(['name'=>'Alpha','url'=>'https://a.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);
        $b = ContentSource::create(['name'=>'Beta','url'=>'https://b.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);

        $p = CollectedContent::create([
            'content_source_id'=>$a->id,'external_url'=>'https://a.example/1','title'=>'Kubernetes changes the default scheduler',
            'excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour(),
        ]);
        CollectedContent::create([
            'content_source_id'=>$b->id,'external_url'=>'https://b.example/1','title'=>'The new Kubernetes scheduler',
            'excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour(),
            'is_duplicate'=>true,'duplicate_of'=>$p->id,
        ]);

        $this->artisan('content:rank-topics --limit=3')
            ->expectsOutputToContain('Kubernetes changes the default scheduler')
            ->assertSuccessful();
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=RankTopicsCommandTest`
Kutilgan natija: FAIL — `The command "content:rank-topics" does not exist.`

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Console/Commands/RankTopicsCommand.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\Content\TopicQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RankTopicsCommand extends Command
{
    protected $signature = 'content:rank-topics {--limit=10} {--window=7}';
    protected $description = 'Yig\'ilgan kontentdan trend mavzular navbatini chiqaradi';

    public function __construct(private readonly TopicQueueService $queue)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $candidates = $this->queue->topCandidates(
            (int) $this->option('limit'),
            (int) $this->option('window')
        );

        if ($candidates->isEmpty()) {
            $this->info('Hozircha nomzod mavzu yo\'q — yig\'ilgan kontent yetarli emas.');

            return self::SUCCESS;
        }

        $this->table(
            ['#', 'Ball', 'Manba', 'Mavzu'],
            $candidates->values()->map(fn ($c, $i) => [
                $i + 1,
                $c['score'],
                $c['cluster_size'],
                \Illuminate\Support\Str::limit($c['title'], 70),
            ])->all()
        );

        Log::info('content:rank-topics produced candidates', ['count' => $candidates->count()]);

        return self::SUCCESS;
    }
}
```

`routes/console.php` da scraping yozuvidan keyin:

```php
// Mavzularni ballash — generatsiya slotidan (09:00) oldin ishlaydi.
Schedule::command('content:rank-topics')
    ->dailyAt('08:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=RankTopicsCommandTest`, keyin `php artisan test --filter=ScrapeScheduleTest` (endi ikkala test ham yashil bo'lishi kerak).

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/RankTopicsCommand.php routes/console.php tests/Feature/Content/RankTopicsCommandTest.php
git commit -m "feat(content): add content:rank-topics and schedule it before generation

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 4: Generatsiyani navbatga ulash

**Fayllar:**
- O'zgartirish: `app/Console/Commands/GenerateAiPost.php` — `selectTrendingTopic()` (~330-qator)
- Test: `tests/Feature/Content/TopicQueueServiceTest.php`

**Interfeyslar:**
- Ishlatadi: `TopicQueueService::topCandidates()`.
- Beradi: `selectTrendingTopic()` qaytaradigan massivda `sources` kaliti — Vazifa 5 shundan foydalanadi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`TopicQueueServiceTest.php` ga qo'shing:

```php
    public function test_generator_navbatdan_mavzu_oladi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $p = $this->item($a, 'Redis 8 changes eviction defaults');
        $this->item($b, 'Redis 8 eviction rework', $p->id);

        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $topic = $m->invoke($command);

        $this->assertIsArray($topic);
        $this->assertSame('Redis 8 changes eviction defaults', $topic['title']);
        $this->assertArrayHasKey('sources', $topic);
        $this->assertNotEmpty($topic['sources']);
    }

    public function test_navbat_bosh_bolsa_null_qaytadi(): void
    {
        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $this->assertNull($m->invoke($command));
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=test_generator_navbatdan_mavzu_oladi`
Kutilgan natija: FAIL — `Method topicFromQueue does not exist`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`GenerateAiPost.php` ga yangi private metod qo'shing:

```php
    /**
     * Trend navbatidan eng yuqori nomzodni oladi, yoki navbat bo'sh bo'lsa null.
     *
     * Yaqinda ishlatilgan mavzular takrorlanmasligi uchun so'nggi postlar
     * sarlavhalari bilan solishtiriladi.
     */
    private function topicFromQueue(): ?array
    {
        $recent = \App\Models\Post::whereNotNull('published_at')
            ->latest('published_at')->take(30)->pluck('title')
            ->map(fn ($t) => mb_strtolower((string) $t))->all();

        foreach (app(\App\Services\Content\TopicQueueService::class)->topCandidates(10) as $candidate) {
            $title = mb_strtolower($candidate['title']);

            foreach ($recent as $seen) {
                if (similar_text($title, $seen) / max(1, mb_strlen($title)) > 0.7) {
                    continue 2;
                }
            }

            return [
                'title' => $candidate['title'],
                'category' => $candidate['category'],
                'from_queue' => true,
                'sources' => $candidate['sources'],
            ];
        }

        return null;
    }
```

`selectTrendingTopic()` ichida, `ContentPlan` bloki **tugagandan keyin** va eski kalit-so'z evristikasidan **oldin**:

```php
        // Trend navbati (C2). Qo'lda tuzilgan oylik reja ustuvor bo'lib qoladi —
        // odam tanlagan mavzu avtomatik signaldan muhimroq. Lekin rejadan
        // keyin, eski kalit-so'z evristikasidan oldin, kuzatilgan trend keladi.
        if ($queued = $this->topicFromQueue()) {
            $this->info("📈 Trend navbatidan: {$queued['title']}");

            return $queued;
        }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=TopicQueueServiceTest`
Kutilgan natija: PASS — 8 ta test. Keyin to'liq to'plam.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/GenerateAiPost.php tests/Feature/Content/TopicQueueServiceTest.php
git commit -m "feat(ai): take the post topic from the trending queue

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 5: Manbalarga havola (EEAT)

Spec §6: navbatdan olingan mavzu asosida yozilgan post **klasterni hosil qilgan URL'larga havola qilishi shart**. Bu butun korpusning EEAT himoyasi.

**Fayllar:**
- O'zgartirish: `app/Console/Commands/GenerateAiPost.php`
- Test: `tests/Feature/Content/TopicQueueServiceTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
    public function test_navbatdagi_manbalar_postga_havola_sifatida_yoziladi(): void
    {
        $post = \App\Models\Post::factory()->create();

        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('attachQueueSources');
        $m->setAccessible(true);

        $m->invokeArgs($command, [$post, [
            ['title' => 'Alpha wrote this', 'url' => 'https://alpha.example/a', 'published_at' => null],
            ['title' => 'Beta wrote this',  'url' => 'https://beta.example/b',  'published_at' => null],
        ]]);

        $refs = \App\Models\SourceReference::where('post_id', $post->id)->get();

        $this->assertCount(2, $refs);
        $this->assertEqualsCanonicalizing(
            ['https://alpha.example/a', 'https://beta.example/b'],
            $refs->pluck('url')->all()
        );
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Kutilgan natija: FAIL — `Method attachQueueSources does not exist`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

```php
    /**
     * Trend klasterini hosil qilgan manbalarni postga havola qilib yozadi.
     *
     * Mavjud ReferenceTrackingService ishlatiladi — yangi jadval yoki yangi
     * format yaratilmaydi.
     */
    private function attachQueueSources(\App\Models\Post $post, array $sources): void
    {
        $service = app(\App\Services\ReferenceTrackingService::class);

        foreach ($sources as $src) {
            if (empty($src['url'])) {
                continue;
            }

            try {
                $service->addReference(
                    $post,
                    (string) ($src['title'] ?? $src['url']),
                    (string) $src['url'],
                    null,
                    ! empty($src['published_at']) ? new \DateTime($src['published_at']) : null
                );
            } catch (\Throwable $e) {
                Log::warning('Queue source reference failed', [
                    'post_id' => $post->id,
                    'url' => $src['url'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
```

Post yaratilgandan keyingi joyda (post `$post` o'zgaruvchisiga tushgan joy — `generateSinglePost()` ichida, moderatsiya chaqiruvidan keyin) chaqiring:

```php
        if (! empty($topic['sources'])) {
            $this->attachQueueSources($post, $topic['sources']);
        }
```

> **Diqqat:** `$topic` o'zgaruvchisi o'sha nuqtada mavjudligini tekshiring. Agar yo'q bo'lsa, uni o'sha metodga uzating — yangi holat (property) qo'shmang.

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=TopicQueueServiceTest`, keyin to'liq to'plam.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/GenerateAiPost.php tests/Feature/Content/TopicQueueServiceTest.php
git commit -m "feat(ai): cite the sources that produced the trending topic

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 6: Health-check'ga scraping nazorati

Spec §5 beshinchi shartni sanagan edi — `content:scrape-all` 24 soatda muvaffaqiyat qozonmasa ogohlantirish. U C2 gacha qoldirilgan edi, chunki scraping jadvalda yo'q edi. Endi bor.

**Fayllar:**
- O'zgartirish: `app/Console/Commands/ContentHealthCheck.php`
- Test: `tests/Feature/Content/ContentHealthCheckTest.php`

- [ ] **Qadam 1: Yiqiladigan testni yozing**

Mavjud `ContentHealthCheckTest.php` ga qo'shing (u allaqachon `setUp()` da urug'langan postlarni tozalaydi — o'sha naqshni saqlang):

```php
    public function test_eskirgan_scraping_ogohlantiradi(): void
    {
        \App\Models\ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subDays(3),
        ]);

        $this->artisan('content:health-check --dry-run')
            ->expectsOutputToContain('stale_scraping')
            ->assertExitCode(1);
    }

    public function test_yangi_scraping_ogohlantirmaydi(): void
    {
        \App\Models\ContentSource::create([
            'name' => 'Alpha', 'url' => 'https://a.example', 'category' => 'news',
            'trust_level' => 90, 'scraping_enabled' => true,
            'last_scraped_at' => now()->subHours(2),
        ]);

        $this->artisan('content:health-check --dry-run')
            ->doesntExpectOutputToContain('stale_scraping');
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Kutilgan natija: birinchi test FAIL — `stale_scraping` chiqmaydi.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`ContentHealthCheck::handle()` ichida, mavjud tekshiruvlar yonига:

```php
        // Scraping to'xtaganini aniqlash. Faol manba bo'lsa-yu, hech biri
        // 24 soat ichida yig'ilmagan bo'lsa — quvurning kirish uchi qurigan.
        $activeSources = \App\Models\ContentSource::active()->count();

        if ($activeSources > 0) {
            $freshlyScraped = \App\Models\ContentSource::active()
                ->where('last_scraped_at', '>=', now()->subDay())
                ->count();

            if ($freshlyScraped === 0) {
                $problems['stale_scraping'] = "So'nggi 24 soatda birorta manba yig'ilmadi ({$activeSources} ta faol manba)";
            }
        }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=ContentHealthCheckTest`, keyin to'liq to'plam.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/ContentHealthCheck.php tests/Feature/Content/ContentHealthCheckTest.php
git commit -m "feat(content): alarm when source scraping goes stale

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Vazifa 7: Botga mavzu beradigan endpoint

Bot hozir mavzuni qo'lda yozilgan `blog-bot/topics.yaml` dan oladi. Server navbati esa haqiqiy signalga asoslangan. Bot ham shundan o'qisin.

**Fayllar:**
- O'zgartirish: `app/Http/Controllers/Api/BotPostController.php`, `routes/api.php`
- Test: `tests/Feature/Content/BotTopicEndpointTest.php` (yaratish)

**Interfeyslar:**
- Beradi: `GET /api/bot/next-topic` — mavjud HMAC himoyasi bilan, `{ok, topic: {title, category, sources}}` yoki `{ok:true, topic:null}`.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotTopicEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kalit nomi TEKSHIRILGAN: BotPostController::verifySignature() aynan
     * config('services.blog_bot.secret') dan o'qiydi, keyin env fallback.
     * Imzo formati ham tekshirilgan: hash_hmac('sha256', "{ts}.{raw_body}").
     * GET so'rovda tana bo'sh, ya'ni "{ts}." imzolanadi.
     */
    private function signedHeaders(string $body = ''): array
    {
        $secret = 'test-secret';
        config(['services.blog_bot.secret' => $secret]);
        $ts = now()->toIso8601String();

        return [
            'X-Bot-Timestamp' => $ts,
            'X-Bot-Signature' => hash_hmac('sha256', $ts . '.' . $body, $secret),
        ];
    }

    public function test_imzosiz_sorov_rad_etiladi(): void
    {
        $this->getJson('/api/bot/next-topic')->assertStatus(401);
    }

    public function test_navbat_bosh_bolsa_null_qaytadi(): void
    {
        $r = $this->withHeaders($this->signedHeaders(''))->getJson('/api/bot/next-topic');

        $r->assertOk()->assertJson(['ok' => true, 'topic' => null]);
    }

    public function test_navbatdagi_mavzu_qaytariladi(): void
    {
        $a = ContentSource::create(['name'=>'Alpha','url'=>'https://a.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);
        $b = ContentSource::create(['name'=>'Beta','url'=>'https://b.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);
        $p = CollectedContent::create(['content_source_id'=>$a->id,'external_url'=>'https://a.example/1','title'=>'Envoy adds a new filter chain','excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour()]);
        CollectedContent::create(['content_source_id'=>$b->id,'external_url'=>'https://b.example/1','title'=>'Envoy filter chain rework','excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour(),'is_duplicate'=>true,'duplicate_of'=>$p->id]);

        $r = $this->withHeaders($this->signedHeaders(''))->getJson('/api/bot/next-topic');

        $r->assertOk()
          ->assertJsonPath('ok', true)
          ->assertJsonPath('topic.title', 'Envoy adds a new filter chain');
        $this->assertNotEmpty($r->json('topic.sources'));
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Kutilgan natija: FAIL — marshrut yo'q (404).

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`BotPostController` ga metod qo'shing. Mavjud HMAC tekshiruvi **`verifySignature(Request $request): bool`** deb ataladi (`:150`) va `submitPost()` bilan `heartbeat()` ikkalasi ham aynan shuni chaqirib, muvaffaqiyatsizlikda **401** qaytaradi. Yangi tekshiruv yozmang, xuddi shu naqshni takrorlang:

```php
    public function nextTopic(Request $request, \App\Services\Content\TopicQueueService $queue)
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $candidate = $queue->topCandidates(1)->first();

        return response()->json([
            'ok' => true,
            'topic' => $candidate ? [
                'title' => $candidate['title'],
                'category' => $candidate['category'],
                'sources' => $candidate['sources'],
            ] : null,
        ]);
    }
```

`routes/api.php` da mavjud bot marshrutlari yonида:

```php
Route::get('/bot/next-topic', [\App\Http\Controllers\Api\BotPostController::class, 'nextTopic'])
    ->middleware('throttle:30,1')->name('api.bot.next-topic');
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=BotTopicEndpointTest`, keyin to'liq to'plam.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Http/Controllers/Api/BotPostController.php routes/api.php tests/Feature/Content/BotTopicEndpointTest.php
git commit -m "feat(api): serve the trending topic queue to the blog-bot

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Yakuniy tekshiruv (qo'lda, deploy'dan keyin)

- [ ] Production'da manbalarni yoqing va birinchi yig'ishni ishga tushiring:

```bash
ssh root@161.35.73.129 "cd /var/www/nextgenbeing && php artisan content:init-sources && php artisan content:scrape-all --limit=20"
```

- [ ] Yig'ilgan kontentni sanang, keyin dublikatlarni toping va navbatni ko'ring:

```bash
ssh root@161.35.73.129 "cd /var/www/nextgenbeing && php artisan content:find-duplicates && php artisan content:rank-topics"
```

- [ ] **Birinchi natijalarga tanqidiy qarang.** Agar navbat bo'sh chiqsa, bu buzuqlik emas: dedup xizmati klasterlarni hali topmagan yoki manbalar bir mavzuda kesishmagan bo'lishi mumkin. `MIN_INDEPENDENT_SOURCES` ni pasaytirishdan **oldin** yig'ilgan kontentga qarang.

## Bu rejada YO'Q narsalar

- `blog-bot/topics.py` ni endpoint'ga ulash — **boshqa repozitoriy**, alohida kichik ish. Bu reja faqat server tomonini qamraydi. Bot hozircha `topics.yaml` dan o'qiyverадi.
- `collected_content` ga jalb qilish signali ustuni — spec §6 buni ataylab rad etgan.
- Scraper'larning o'zini yaxshilash (CSS selektorlar, yangi manbalar) — mavjud holicha ishlatiladi.
- `PublishGate` ga hech qanday o'zgarish.
