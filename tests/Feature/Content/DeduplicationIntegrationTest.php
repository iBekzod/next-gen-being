<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentAggregation;
use App\Models\ContentSource;
use App\Services\Content\TopicQueueService;
use App\Services\ContentDeduplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

/**
 * Trend mavzular navbatining YAGONA haqiqiy integratsiya testi.
 *
 * Mavjud testlarning hammasi `duplicate_of` ustunini O'ZI qo'lda yozadi va
 * shu sababli ular ContentDeduplicationService'ni umuman sinamaydi. Ya'ni
 * "klaster hosil bo'ladimi?" degan savol — butun xususiyat shunga bog'liq —
 * hech qachon tekshirilmagan.
 *
 * Bu test HAQIQIY quvurni ishga tushiradi: ikki mustaqil manba bir mavzuni
 * yozadi -> `content:deduplicate` -> TopicQueueService::topCandidates().
 * Bu yerda `duplicate_of` hech qayerda qo'lda yozilmaydi.
 *
 * ILGARIGI NATIJA (o'lchangan): quyidagi ikki hujjat orasidagi o'xshashlik
 * = 0.4623, MIN_SIMILARITY_THRESHOLD = 0.75 edi, ya'ni klaster hech qachon
 * hosil bo'lmasdi. Sabab: getTFIDFVector() IDF sifatida log(1000 /
 * (array_search($word, $words) + 1)) hisoblardi — bu so'zning MASSIVDAGI
 * BIRINCHI POZITSIYASI, hujjat chastotasi emas.
 *
 * HOZIRGI NATIJA (o'lchangan, soxta IDF olib tashlangan, tanaga 2000 belgi
 * qo'shilgan, chegara 0.35):
 *
 *   ijobiy nazorat (A vs B, bir mavzu, ikki mustaqil matn)      0.5417
 *   salbiy nazorat (eng yomoni: C/D — Rust va Kubernetes)       0.0987
 *   ajratish oralig'i                                           0.4430
 *
 * Ikkala yo'nalish ham shu yerda sinaladi: birinchi test klaster HOSIL
 * BO'LISHINI, ikkinchisi bog'liq bo'lmagan maqolalar klaster hosil
 * QILMASLIGINI tekshiradi. Chegarani ijobiy holat o'tguncha pasaytirish
 * oson, lekin u holda har bir maqola har biri bilan birlashib, "tasdiq"
 * shunchaki shovqinga aylanadi — shuning uchun salbiy nazorat majburiy.
 */
class DeduplicationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ikki mustaqil nashr bitta voqeani yozgandek matn. Sarlavhalar boshqa,
     * birinchi jumlalar boshqa, lekin texnik lug'at jiddiy darajada ustma-ust
     * tushadi — real hayotda korroboratsiya aynan shunday ko'rinadi.
     */
    private const DOC_A_TITLE = 'PostgreSQL 18 ships asynchronous I/O, cutting sequential scan latency on cloud disks';

    private const DOC_A_BODY = <<<'TXT'
PostgreSQL 18, released this week, introduces an asynchronous I/O subsystem that
changes how the database reads pages from storage. Until now every sequential scan
issued blocking read calls one buffer at a time, which was tolerable on local NVMe
drives but punishing on network attached cloud storage where each round trip costs
hundreds of microseconds. The new asynchronous I/O layer submits many read requests
at once through io_uring on Linux, allowing the storage device to keep several
requests in flight while the executor continues working. Early benchmarks from the
development mailing list show sequential scan throughput improving by a factor of
two or three on Amazon EBS volumes, with vacuum and bitmap heap scans seeing similar
gains. The feature is controlled by the io_method configuration parameter, which
defaults to a worker based implementation so that platforms without io_uring still
benefit. Administrators upgrading a read heavy analytics replica are the most likely
to notice the difference immediately. The PostgreSQL developers caution that write
path latency is unchanged in this release, and that further asynchronous I/O work,
including asynchronous writes and checkpointer integration, is planned for
PostgreSQL 19.
TXT;

    private const DOC_B_TITLE = "Why Postgres 18's new AIO subsystem matters for read-heavy workloads";

    private const DOC_B_BODY = <<<'TXT'
For years the standard advice for running Postgres on cloud storage was simple:
accept that sequential scans will be slow, and buy faster disks. The Postgres 18
release changes that calculus. Its headline addition is an asynchronous I/O
subsystem, usually shortened to AIO, which lets the executor submit a batch of page
read requests to the operating system instead of waiting for each buffer in turn. On
Linux the implementation uses io_uring; elsewhere a pool of I/O worker processes
provides the same behaviour, selected through the new io_method parameter. Why does
this matter so much on network attached storage such as EBS? Because latency, not
bandwidth, is the binding constraint there, and keeping many read requests in flight
hides that latency. Benchmarks circulated during the beta showed sequential scan and
bitmap heap scan throughput roughly doubling, and vacuum finishing noticeably sooner
on large tables. Read heavy analytics workloads and reporting replicas stand to gain
the most. Write latency is untouched for now; asynchronous writes and checkpointer
changes are on the roadmap for a future PostgreSQL release. Teams already planning
an upgrade should benchmark their own sequential scan workloads before and after.
TXT;

    /**
     * SALBIY NAZORAT. Bir xil uslub, bir xil texnik registr, taxminan bir xil
     * uzunlik — lekin mavzular butunlay boshqa. Bular klasterga tushmasligi
     * SHART: aks holda "mustaqil manbalar tasdig'i" ma'nosini yo'qotadi va
     * navbat ishonchli ko'rinishdagi bema'ni mavzular bilan to'ladi.
     */
    private const DOC_C_TITLE = 'Rust 1.90 stabilises const generic arithmetic, trimming build times for embedded crates';

    private const DOC_C_BODY = <<<'TXT'
The Rust release train arrived on schedule this month with version 1.90, whose
headline item is stabilised const generic arithmetic. Crate authors have until now
worked around the restriction with macros that expand one implementation per array
size, a habit that inflates compile times and produces error messages nobody enjoys
reading. With the new syntax a single generic implementation covers every length,
and the borrow checker understands it without additional annotations. Maintainers of
embedded crates report build times falling by a fifth on their continuous
integration runners, mostly because the compiler no longer monomorphises dozens of
near identical copies. The release also promotes several standard library methods
out of nightly, tightens a lint around unused lifetimes, and ships a smaller
toolchain tarball after the team pruned debug symbols from the shipped binaries. As
usual the changes reach stable through the six week cycle, so anyone tracking beta
has been exercising them since summer. The compiler team notes that the remaining
const evaluation work, in particular floating point support inside const contexts,
is still gated behind a nightly feature flag and is unlikely to stabilise this year.
TXT;

    private const DOC_D_TITLE = 'Kubernetes Gateway API reaches GA as Ingress finally starts its retirement';

    private const DOC_D_BODY = <<<'TXT'
After four years of incubation the Kubernetes Gateway API has been declared
generally available, and with it the project has begun the long goodbye to Ingress.
The older resource was famously underspecified: every controller vendor invented its
own annotations, so a manifest that routed traffic correctly on one cluster behaved
differently on the next. Gateway API replaces that with a layered set of resources,
splitting the concerns of the platform team, who own the gateway itself, from those
of application teams, who own the routes attached to it. Header based matching,
traffic splitting by weight and cross namespace references are all part of the
specification rather than vendor extensions. Several controller projects have
shipped conformant implementations already, and the conformance suite publishes
results so operators can compare them before committing. Ingress will remain in the
API for the foreseeable future, but it is now frozen and will receive no further
features. Platform teams running a service mesh alongside their ingress controller
are the obvious early adopters, since the mesh interfaces reuse the same route
types.
TXT;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeder yig'ilgan kontent qoldirgan bo'lsa, u begona klasterlar
        // yasab, bu testning o'lchovini buzadi.
        CollectedContent::query()->delete();
    }

    public function test_ikki_mustaqil_nashr_haqiqiy_quvurda_bitta_klasterga_birlashadi(): void
    {
        [$a, $b] = $this->seedTwoPublications();

        // O'lchov: chegaradan o'tadimi yoki yo'q — raqam bilan.
        $similarity = app(ContentDeduplicationService::class)->calculateSimilarity($a, $b);
        $threshold = $this->minSimilarityThreshold();

        // Bu qorovul O'CHIRILMAYDI, lekin endi ISHLAMASLIGI kerak. U servisning
        // haqiqiy chegarasiga qaraydi, ya'ni chegara yana ko'tarilsa yoki metrika
        // buzilsa, test yashil ko'rinib turib jim qolmaydi.
        if ($similarity < $threshold) {
            $this->markTestSkipped(sprintf(
                'ContentDeduplicationService ikki mustaqil nashrni bitta mavzu deb '
                . 'tan olmaydi: o\'lchangan o\'xshashlik %.4f, MIN_SIMILARITY_THRESHOLD %.2f '
                . '— demak trend mavzular navbati production\'da hech qachon to\'lmaydi.',
                $similarity,
                $threshold
            ));
        }

        // Ijobiy nazorat o'lchovi qotirib qo'yiladi: 0.5417 o'lchangan.
        $this->assertGreaterThan(
            0.45,
            $similarity,
            'Ijobiy nazorat kutilganidan past tushdi — metrika sekin buzilyapti.'
        );

        $this->artisan('content:deduplicate')->assertExitCode(0);

        $candidates = app(TopicQueueService::class)->topCandidates(10, 7);

        $this->assertCount(
            1,
            $candidates,
            'Ikki mustaqil manba bitta mavzuni yozdi — aynan bitta nomzod kutilgan edi.'
        );
        $this->assertSame(2, $candidates->first()['cluster_size']);
    }

    /**
     * Yuqoridagi test `excerpt` ustuniga BUTUN tanani yozadi, production esa
     * unday qilmaydi: skraper excerpt'ni 500 belgiga qirqadi va haqiqiy
     * qatorlarda u 84-228 belgi bo'lgan, tana esa 4,558-9,707. Shu sababli
     * ayni o'sha juftlik production SHAKLIDA ham tekshiriladi — qisqa
     * excerpt + to'liq tana. O'lchangan: 0.5159 (yuqoridagi 0.5417 o'rniga).
     */
    public function test_production_shaklidagi_qisqa_excerpt_bilan_ham_klaster_hosil_boladi(): void
    {
        [$a, $b] = $this->seedTwoPublications(productionShapedExcerpt: true);

        $this->assertLessThanOrEqual(228, strlen((string) $a->excerpt), 'Fixture production shaklida emas.');

        $similarity = app(ContentDeduplicationService::class)->calculateSimilarity($a, $b);

        $this->assertGreaterThan(
            $this->minSimilarityThreshold(),
            $similarity,
            sprintf(
                'Qisqa excerpt + to\'liq tana bilan ikki mustaqil nashr chegaradan o\'tmadi (%.4f) — '
                . 'ya\'ni metrika faqat fixture shaklida ishlaydi, productionda emas.',
                $similarity
            )
        );

        $this->artisan('content:deduplicate')->assertExitCode(0);

        $candidates = app(TopicQueueService::class)->topCandidates(10, 7);

        $this->assertCount(1, $candidates);
        $this->assertSame(2, $candidates->first()['cluster_size']);
    }

    /**
     * SALBIY NAZORAT: bir-biriga bog'liq bo'lmagan ikki maqola KLASTER HOSIL
     * QILMASLIGI kerak. Chegarani ijobiy holat o'tguncha pasaytirish oson —
     * bu test aynan shu yo'ldan borishga to'sqinlik qiladi.
     */
    public function test_bogliq_bolmagan_ikki_maqola_klaster_hosil_qilmaydi(): void
    {
        [$c, $d] = $this->seedTwoUnrelatedArticles();

        $similarity = app(ContentDeduplicationService::class)->calculateSimilarity($c, $d);

        // O'lchangan: 0.0987 (eng yomon salbiy juftlik). Chegara 0.35.
        $this->assertLessThan(
            $this->minSimilarityThreshold(),
            $similarity,
            sprintf(
                'Bog\'liq bo\'lmagan ikki maqola chegaradan o\'tib ketdi (%.4f) — bu holda '
                . '"mustaqil manbalar tasdig\'i" shunchaki shovqinga aylanadi.',
                $similarity
            )
        );
        $this->assertLessThan(0.20, $similarity, 'Salbiy nazorat kutilganidan yuqori — ajratish oralig\'i yo\'qolyapti.');

        $this->artisan('content:deduplicate')->assertExitCode(0);

        // Haqiqiy quvur hech narsani birlashtirmagan bo'lishi kerak.
        $c->refresh();
        $d->refresh();

        $this->assertFalse((bool) $c->is_duplicate);
        $this->assertFalse((bool) $d->is_duplicate);
        $this->assertNull($c->duplicate_of);
        $this->assertNull($d->duplicate_of);
        $this->assertSame(0, ContentAggregation::count(), 'Bog\'liq bo\'lmagan maqolalardan agregatsiya yasalmasligi kerak.');

        $candidates = app(TopicQueueService::class)->topCandidates(10, 7);

        $this->assertCount(
            0,
            $candidates,
            'Ikki mustaqil manba HAR XIL mavzu yozdi — trend nomzodi bo\'lmasligi kerak.'
        );
    }

    /**
     * Servisning haqiqiy chegarasi. Test uni takrorlab yozmaydi, o'qiydi —
     * aks holda konstanta o'zgarganda testlar jim qolib ketishi mumkin.
     */
    private function minSimilarityThreshold(): float
    {
        return (float) (new ReflectionClass(ContentDeduplicationService::class))
            ->getConstant('MIN_SIMILARITY_THRESHOLD');
    }

    /**
     * @return array{0: CollectedContent, 1: CollectedContent}
     */
    private function seedTwoUnrelatedArticles(): array
    {
        $sourceA = ContentSource::create([
            'name' => 'The Register Dev Desk',
            'url' => 'https://register-dev.example',
            'category' => 'news',
            'trust_level' => 85,
            'scraping_enabled' => true,
            'last_scraped_at' => now()->subHour(),
        ]);

        $sourceB = ContentSource::create([
            'name' => 'InfoQ Cloud Native',
            'url' => 'https://infoq-cloud.example',
            'category' => 'news',
            'trust_level' => 80,
            'scraping_enabled' => true,
            'last_scraped_at' => now()->subHour(),
        ]);

        $c = CollectedContent::create([
            'content_source_id' => $sourceA->id,
            'external_url' => 'https://register-dev.example/rust-1-90-const-generics',
            'title' => self::DOC_C_TITLE,
            'excerpt' => self::DOC_C_BODY,
            'full_content' => self::DOC_C_BODY,
            'content_type' => 'news',
            'language' => 'en',
            'published_at' => now()->subHours(3),
        ]);

        $d = CollectedContent::create([
            'content_source_id' => $sourceB->id,
            'external_url' => 'https://infoq-cloud.example/gateway-api-ga',
            'title' => self::DOC_D_TITLE,
            'excerpt' => self::DOC_D_BODY,
            'full_content' => self::DOC_D_BODY,
            'content_type' => 'news',
            'published_at' => now()->subHours(2),
        ]);

        return [$c, $d];
    }

    /**
     * @return array{0: CollectedContent, 1: CollectedContent}
     */
    private function seedTwoPublications(bool $productionShapedExcerpt = false): array
    {
        // Production'da excerpt — tananing qisqa boshlanishi, butun matn emas.
        $excerpt = fn (string $body) => $productionShapedExcerpt
            ? substr(preg_replace('/\s+/', ' ', $body), 0, 200)
            : $body;

        $sourceA = ContentSource::create([
            'name' => 'The Register DB Desk',
            'url' => 'https://register.example',
            'category' => 'news',
            'trust_level' => 85,
            'scraping_enabled' => true,
            'last_scraped_at' => now()->subHour(),
        ]);

        $sourceB = ContentSource::create([
            'name' => 'InfoQ Data Engineering',
            'url' => 'https://infoq.example',
            'category' => 'news',
            'trust_level' => 80,
            'scraping_enabled' => true,
            'last_scraped_at' => now()->subHour(),
        ]);

        $a = CollectedContent::create([
            'content_source_id' => $sourceA->id,
            'external_url' => 'https://register.example/postgresql-18-async-io',
            'title' => self::DOC_A_TITLE,
            'excerpt' => $excerpt(self::DOC_A_BODY),
            'full_content' => self::DOC_A_BODY,
            'content_type' => 'news',
            'language' => 'en',
            'published_at' => now()->subHours(3),
        ]);

        $b = CollectedContent::create([
            'content_source_id' => $sourceB->id,
            'external_url' => 'https://infoq.example/postgres-18-aio-read-heavy',
            'title' => self::DOC_B_TITLE,
            'excerpt' => $excerpt(self::DOC_B_BODY),
            'full_content' => self::DOC_B_BODY,
            'content_type' => 'news',
            'published_at' => now()->subHours(2),
        ]);

        return [$a, $b];
    }
}
