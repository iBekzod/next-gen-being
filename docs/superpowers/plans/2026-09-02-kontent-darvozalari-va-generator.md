# Kontent darvozalari va generator tuzatish — Amalga oshirish rejasi

> **Agentik ishchilar uchun:** MAJBURIY SUB-SKILL: bu rejani vazifama-vazifa bajarish uchun superpowers:subagent-driven-development (tavsiya etiladi) yoki superpowers:executing-plans skill'idan foydalaning. Qadamlar checkbox (`- [ ]`) sintaksisida kuzatiladi.

**Maqsad:** Nashr darvozalarini takrorlanish va soxta tajriba da'volarini ushlaydigan qilib tuzatish, generatordagi "imkonsiz so'z maqsadi" xatosini bartaraf etish va kontent quvuri jim qolganda ogohlantiruvchi signal qo'shish.

**Arxitektura:** Hozir nashr darvozalari `ContentDripPublish` ichida `private` holda yashiringan, shuning uchun ular na qayta ishlatiladi, na alohida test qilinadi. Ularni yagona `PublishGate` xizmatiga ajratamiz; unga ikkita yangi darvoza qo'shamiz (takrorlanish va atribut) va `MIN_WORDS`ni **dublikatsiz** matnga nisbatan hisoblaymiz. Keyin `GenerateAiPost::expandPostContent()` ni bo'lim-bo'lim kengaytirishga o'tkazamiz, shunda model mavjud matnni qaytadan chiqarmaydi. Oxirida `content:health-check` buyrug'i xuddi shu `PublishGate` predikatidan foydalanib quvurni kuzatadi.

**Texnologiyalar:** Laravel 12, PHP 8.4, PostgreSQL, PHPUnit 11, Eloquent, `Illuminate\Support\Facades\Http`.

**Spec:** `docs/superpowers/specs/2026-09-02-marketplace-audience-engine-design.md` (§1.2a, §1.2b, §5, §8)

**Ishchi katalog:** barcha yo'llar `d:\projects\MyProjects\nextgenbeing\next-gen-being` ga nisbatan.

**Test buyrug'i:** `php artisan test --filter=<TestClassOrMethod>`. To'liq to'plam: `php artisan test`.

## Boshlashdan oldingi shart

Test to'plami ishlashi kerak. U PostgreSQL'ni `localhost:9061` da (`nextgenbeing_test`) talab qiladi — Docker Desktop ishga tushirilgan bo'lsin:

```bash
docker compose up -d ngb-database
php artisan test
```

Agar to'plam yashil bo'lmasa, avval shuni tuzating. Spec buni vazifa emas, **shart** deb belgilagan.

## Global cheklovlar

- **Hech qanday migratsiya yo'q.** Bu reja sxemani o'zgartirmaydi (spec §8).
- **Production'ga hech narsa yozilmaydi.** `BLOG_AUTO_PUBLISH` ataylab `false` — uni bu rejada **o'zgartirmang** (spec §1.1). Bu egasining qarori.
- **Mavjud kadenцiya saqlanadi:** 5 kun / post, 7 kun / tutorial. Bu qiymatlar faqat `PublishGate` ichida bir marta ta'riflanadi.
- **`str_word_count` ishlatiladi** — mavjud kod bilan bir xil bo'lishi uchun, boshqa so'z sanash usuliga o'tmang.
- Kommit xabarlari ingliz tilida, Conventional Commits uslubida.

---

## Fayl tuzilmasi

**Yaratiladi:**
- `app/Services/Content/PublishGate.php` — barcha nashr darvozalari va kadenцiya konstantalari uchun yagona manba.
- `app/Console/Commands/ContentHealthCheck.php` — kunlik quvur monitoringi.
- `tests/Unit/Content/PublishGateTest.php` — darvozalarning sof mantiq testlari (DB kerak emas).
- `tests/Feature/Content/ContentHealthCheckTest.php` — monitoring testlari (DB kerak).
- `tests/Feature/Content/PostExpansionTest.php` — kengaytirish mantiqi testlari (Http::fake).

**O'zgartiriladi:**
- `app/Console/Commands/ContentDripPublish.php` — darvozalar `PublishGate` ga ko'chiriladi.
- `app/Console/Commands/GenerateAiPost.php` — `expandPostContent()` qayta yoziladi, o'lik zona yopiladi.
- `routes/console.php` — `content:health-check` jadvalga qo'shiladi.
- `.env.example` — `BLOG_AUTO_PUBLISH` hujjatlashtiriladi.

---

## Vazifa 0: Test fabrikalari (`CategoryFactory`, `PostFactory`)

Vazifa 4 va 7 `Post::factory()` ga tayanadi, lekin loyihada **faqat** `UserFactory`, `DigitalProductFactory` va `MarketplaceListingFactory` bor. `posts` jadvali `title`, `slug`, `excerpt`, `content`, `author_id`, `category_id` ustunlarini majburiy qiladi, `category_id` esa `categories` ga FK — shuning uchun ikkala fabrika ham kerak.

**Diqqat:** `moderation_status` ustuni **default `'pending'`**. Fabrika buni ataylab `'approved'` qilib qo'yadi, aks holda har bir test posti 4-darvozada yiqiladi va testlar chalg'ituvchi bo'ladi.

**Fayllar:**
- Yaratish: `database/factories/CategoryFactory.php`
- Yaratish: `database/factories/PostFactory.php`

**Interfeyslar:**
- Beradi: `Post::factory()` va `Category::factory()` — Vazifa 4 va 7 shulardan foydalanadi.

- [ ] **Qadam 1: `CategoryFactory` ni yozing**

`database/factories/CategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 999999),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
```

- [ ] **Qadam 2: `PostFactory` ni yozing**

`database/factories/PostFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 999999),
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => 'draft',
            // Ustun default qiymati 'pending'. Uni ochiq 'approved' qilamiz,
            // aks holda har bir test posti moderatsiya darvozasida yiqiladi.
            'moderation_status' => 'approved',
            'series_title' => null,
            'published_at' => null,
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
```

- [ ] **Qadam 3: Fabrikalar ishlashini tasdiqlang**

`tests/Feature/Content/FactorySmokeTest.php` yarating:

```php
<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactorySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_fabrikasi_ishlaydi(): void
    {
        $post = Post::factory()->create();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertSame('approved', $post->moderation_status);
        $this->assertNotNull($post->category_id);
        $this->assertNotNull($post->author_id);
    }
}
```

Buyruq: `php artisan test --filter=FactorySmokeTest`
Kutilgan natija: PASS.

> Agar `Post` yoki `Category` modelida `HasFactory` trait'i bo'lmasa, uni qo'shing: `use Illuminate\Database\Eloquent\Factories\HasFactory;` va klass ichida `use HasFactory;`.

- [ ] **Qadam 4: Commit qiling**

```bash
git add database/factories/CategoryFactory.php database/factories/PostFactory.php tests/Feature/Content/FactorySmokeTest.php
git commit -m "test: add Post and Category factories for content pipeline tests"
```

---

## Vazifa 1: `PublishGate` xizmatini ajratish

Mavjud to'rtta darvozani o'zgartirmasdan ko'chiramiz. Xulq-atvor **o'zgarmasligi** kerak — bu toza refaktoring.

**Fayllar:**
- Yaratish: `app/Services/Content/PublishGate.php`
- Test: `tests/Unit/Content/PublishGateTest.php`

**Interfeyslar:**
- Ishlatadi: `App\Models\Post` (faqat `content` va `moderation_status` xossalari).
- Beradi: `PublishGate::failures(Post): array<string>`, `PublishGate::passes(Post): bool`, va `POST_INTERVAL_DAYS`, `TUTORIAL_INTERVAL_DAYS`, `MIN_WORDS` konstantalari.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Unit/Content/PublishGateTest.php` yarating:

```php
<?php

namespace Tests\Unit\Content;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Tests\TestCase;

class PublishGateTest extends TestCase
{
    private function post(string $content, string $moderation = 'approved'): Post
    {
        $post = new Post();
        $post->content = $content;
        $post->moderation_status = $moderation;

        return $post;
    }

    /** Har biri ~18 so'zdan iborat, 60 belgidan uzun, TAKRORLANMAYDIGAN jumlalar. */
    private function cleanContent(int $minWords = 1600): string
    {
        $sentences = [];
        $i = 0;
        do {
            $i++;
            $sentences[] = "Bu {$i}-raqamli noyob izohli jumla bo'lib, ishlab chiqarish tizimlarida "
                . "ma'lumotlar bazasi indekslash va so'rov rejalashtirish haqida gapiradi.";
        } while (str_word_count(implode(' ', $sentences)) < $minWords);

        return implode(' ', $sentences);
    }

    public function test_toza_uzun_matn_barcha_darvozalardan_otadi(): void
    {
        $gate = new PublishGate();

        $this->assertSame([], $gate->failures($this->post($this->cleanContent())));
        $this->assertTrue($gate->passes($this->post($this->cleanContent())));
    }

    public function test_qisqa_matn_yiqiladi(): void
    {
        $gate = new PublishGate();

        $this->assertContains('too_short', $gate->failures($this->post('Juda qisqa matn.')));
    }

    public function test_kesilgan_matn_yiqiladi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent() . ' Bu jumla tugamay qoldi va nuqta yo';

        $this->assertContains('truncated', $gate->failures($this->post($content)));
    }

    public function test_yopilmagan_kod_bloki_yiqiladi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent() . " \n```php\n echo 1;\n";

        $this->assertContains('unbalanced_fences', $gate->failures($this->post($content)));
    }

    public function test_moderatsiya_kutilayotgan_post_yiqiladi(): void
    {
        $gate = new PublishGate();

        $this->assertContains(
            'moderation_pending',
            $gate->failures($this->post($this->cleanContent(), 'pending'))
        );
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: FAIL — `Class "App\Services\Content\PublishGate" not found`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Services/Content/PublishGate.php` yarating:

```php
<?php

namespace App\Services\Content;

use App\Models\Post;

/**
 * Nashr darvozalarining yagona manbasi.
 *
 * Ilgari bu mantiq ContentDripPublish ichida private edi, shuning uchun
 * monitoring uni qayta ishlata olmasdi. Endi nashr qiluvchi ham,
 * content:health-check ham xuddi shu predikatni chaqiradi — ikkalasi
 * bir-biridan uzoqlashib keta olmaydi.
 */
class PublishGate
{
    public const POST_INTERVAL_DAYS = 5;
    public const TUTORIAL_INTERVAL_DAYS = 7;
    public const MIN_WORDS = 1500;

    /**
     * Post yiqilgan darvozalar ro'yxati. Bo'sh massiv = nashrga tayyor.
     *
     * @return string[]
     */
    public function failures(Post $post): array
    {
        $content = (string) $post->content;
        $failures = [];

        if (str_word_count(strip_tags($content)) < self::MIN_WORDS) {
            $failures[] = 'too_short';
        }

        if (! preg_match('/[.!?]["\')\]]?\s*$/', trim($content))) {
            $failures[] = 'truncated';
        }

        if (substr_count($content, '```') % 2 !== 0) {
            $failures[] = 'unbalanced_fences';
        }

        if ($post->moderation_status === 'pending') {
            $failures[] = 'moderation_pending';
        }

        return $failures;
    }

    public function passes(Post $post): bool
    {
        return $this->failures($post) === [];
    }
}
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: PASS — 5 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Services/Content/PublishGate.php tests/Unit/Content/PublishGateTest.php
git commit -m "refactor(content): extract publish gates into PublishGate service"
```

---

## Vazifa 2: Takrorlanish darvozasi va dublikatsiz so'z hisobi

Spec §1.2a: `MIN_WORDS` hozir **teskari ishlaydi** — takrorlanish so'z sonini shishiradi, shuning uchun eng buzuq draft darvozadan o'tadi. Ikkita o'zgarish: takrorlanishni rad etish, va uzunlikni dublikatsiz matnga nisbatan o'lchash.

**Fayllar:**
- O'zgartirish: `app/Services/Content/PublishGate.php`
- Test: `tests/Unit/Content/PublishGateTest.php`

**Interfeyslar:**
- Beradi: `PublishGate::redundantSentenceCount(string): int`, `PublishGate::uniqueWordCount(string): int`, `MAX_REDUNDANT_SENTENCES` konstantasi, va `failures()` dagi yangi `duplicated_content` sababi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Unit/Content/PublishGateTest.php` ga qo'shing:

```php
    public function test_takrorlangan_jumlalar_rad_etiladi(): void
    {
        $gate = new PublishGate();
        $repeat = 'Bu jumla ataylab bir necha marta takrorlanadi va altmish belgidan uzunroq.';
        $content = $this->cleanContent() . ' ' . str_repeat($repeat . ' ', 5);

        $this->assertSame(4, $gate->redundantSentenceCount($content));
        $this->assertContains('duplicated_content', $gate->failures($this->post($content)));
    }

    public function test_takrorlanish_bilan_shishirilgan_uzunlik_otmaydi(): void
    {
        $gate = new PublishGate();
        // 300 so'zlik noyob matn, 6 marta takrorlangan: xom hisob 1500 dan oshadi,
        // lekin noyob hajm hamon juda kichik.
        $short = $this->cleanContent(300);
        $content = trim(str_repeat($short . ' ', 6));

        $this->assertGreaterThan(PublishGate::MIN_WORDS, str_word_count(strip_tags($content)));
        $this->assertLessThan(PublishGate::MIN_WORDS, $gate->uniqueWordCount($content));
        $this->assertContains('too_short', $gate->failures($this->post($content)));
    }

    public function test_toza_matnda_takrorlanish_yoq(): void
    {
        $gate = new PublishGate();

        $this->assertSame(0, $gate->redundantSentenceCount($this->cleanContent()));
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: FAIL — `Call to undefined method ...::redundantSentenceCount()`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`PublishGate.php` ni yangilang. Konstanta qo'shing:

```php
    /** Bundan ko'p ortiqcha nusxa = generatsiya buzilgan (spec §1.2a). */
    public const MAX_REDUNDANT_SENTENCES = 2;

    /** Faqat shu uzunlikdan katta jumlalar takrorlanish uchun hisobga olinadi. */
    private const LONG_SENTENCE_CHARS = 60;
```

Yangi metodlarni qo'shing:

```php
    /** Matnni normallashtirilgan jumlalarga bo'ladi. @return string[] */
    public function sentences(string $content): array
    {
        $text = preg_replace('/\s+/', ' ', strip_tags($content));
        $parts = preg_split('/(?<=[.!?])\s+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $parts ?: []);
    }

    /** Ortiqcha nusxalar soni: takrorlangan har bir uzun jumla uchun (n - 1). */
    public function redundantSentenceCount(string $content): int
    {
        $long = array_filter(
            $this->sentences($content),
            fn (string $s): bool => mb_strlen($s) > self::LONG_SENTENCE_CHARS
        );

        $redundant = 0;
        foreach (array_count_values($long) as $occurrences) {
            if ($occurrences > 1) {
                $redundant += $occurrences - 1;
            }
        }

        return $redundant;
    }

    /**
     * Dublikatlar olib tashlangandan keyingi so'z soni.
     *
     * Uzunlikni takrorlash orqali sun'iy oshirib bo'lmasligi uchun
     * MIN_WORDS aynan shu qiymatga nisbatan qo'llanadi.
     */
    public function uniqueWordCount(string $content): int
    {
        $unique = [];
        foreach ($this->sentences($content) as $sentence) {
            $unique[$sentence] = true;
        }

        return str_word_count(implode(' ', array_keys($unique)));
    }
```

`failures()` ichida uzunlik tekshiruvini almashtiring va yangi darvozani qo'shing:

```php
        if ($this->uniqueWordCount($content) < self::MIN_WORDS) {
            $failures[] = 'too_short';
        }
```

va `moderation_pending` tekshiruvidan keyin:

```php
        if ($this->redundantSentenceCount($content) > self::MAX_REDUNDANT_SENTENCES) {
            $failures[] = 'duplicated_content';
        }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: PASS — 8 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Services/Content/PublishGate.php tests/Unit/Content/PublishGateTest.php
git commit -m "feat(content): reject duplicated drafts and measure length on unique text"
```

---

## Vazifa 3: Atribut darvozasi (soxta tajriba da'volari)

Spec §1.2a: to'qqizta draftdan uchtasi haqiqiy imzo ostida yolg'on kasbiy tajribani da'vo qiladi. Bu EEAT muammosi va halollik muammosi.

**Fayllar:**
- O'zgartirish: `app/Services/Content/PublishGate.php`
- Test: `tests/Unit/Content/PublishGateTest.php`

**Interfeyslar:**
- Beradi: `PublishGate::fabricatedExperience(string): array<string>` va `failures()` dagi `fabricated_experience` sababi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

```php
    public function test_soxta_tajriba_davolari_rad_etiladi(): void
    {
        $gate = new PublishGate();

        $namunalar = [
            'As a senior engineer with over 10 years of experience, I learned a lot.',
            'Last quarter, our team discovered a serious bottleneck in the system.',
            'In my experience, connection pooling solves most of these problems.',
            'Our team migrated the whole platform to a new database engine.',
        ];

        foreach ($namunalar as $namuna) {
            $content = $this->cleanContent() . ' ' . $namuna;

            $this->assertNotEmpty(
                $gate->fabricatedExperience($content),
                "Ushlanmadi: {$namuna}"
            );
            $this->assertContains('fabricated_experience', $gate->failures($this->post($content)));
        }
    }

    public function test_toza_texnik_matn_atribut_darvozasidan_otadi(): void
    {
        $gate = new PublishGate();

        $this->assertSame([], $gate->fabricatedExperience($this->cleanContent()));
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: FAIL — `Call to undefined method ...::fabricatedExperience()`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`PublishGate.php` ga qo'shing:

```php
    /**
     * Birinchi shaxsdagi kasbiy tajriba da'volari.
     *
     * Generatsiya qilingan post o'zida bo'lmagan karerani da'vo qilmasligi kerak:
     * bu Google HCU nishoni va imzo ostidagi halollik masalasi (spec §1.2a).
     *
     * @return string[] topilgan iboralar
     */
    public function fabricatedExperience(string $content): array
    {
        $text = (string) preg_replace('/\s+/', ' ', strip_tags($content));

        $patterns = [
            '/\bas an? (?:senior|seasoned|experienced|lead|principal|staff)\b/i',
            '/\bwith (?:over |more than )?\d+\+? years? of experience\b/i',
            '/\bin my (?:experience|career)\b/i',
            '/\bwhen i first started\b/i',
            '/\b(?:our|my) team (?:discovered|learned|built|shipped|migrated|ran)\b/i',
            '/\blast (?:quarter|month|year),? (?:we|our|i)\b/i',
        ];

        $hits = [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                $hits[] = $matches[0];
            }
        }

        return $hits;
    }
```

`failures()` oxiriga, `return` dan oldin:

```php
        if ($this->fabricatedExperience($content) !== []) {
            $failures[] = 'fabricated_experience';
        }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PublishGateTest`
Kutilgan natija: PASS — 10 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Services/Content/PublishGate.php tests/Unit/Content/PublishGateTest.php
git commit -m "feat(content): reject drafts asserting fabricated first-person experience"
```

---

## Vazifa 4: `ContentDripPublish` ni `PublishGate` ga ulash

Endi nashr qiluvchi o'z nusxasidagi mantiqni tashlaydi va xizmatdan foydalanadi.

**Fayllar:**
- O'zgartirish: `app/Console/Commands/ContentDripPublish.php`
- Test: `tests/Feature/Content/ContentDripPublishTest.php` (yaratish)

**Interfeyslar:**
- Ishlatadi: `PublishGate::passes()`, `PublishGate::POST_INTERVAL_DAYS`, `PublishGate::TUTORIAL_INTERVAL_DAYS`.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Content/ContentDripPublishTest.php` yarating:

```php
<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use App\Models\User;
use App\Services\Content\PublishGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentDripPublishTest extends TestCase
{
    use RefreshDatabase;

    private function draft(string $content, string $moderation = 'approved'): Post
    {
        return Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => $moderation,
            'content' => $content,
            'published_at' => null,
        ]);
    }

    private function cleanContent(int $minWords = 1600): string
    {
        $sentences = [];
        $i = 0;
        do {
            $i++;
            $sentences[] = "Bu {$i}-raqamli noyob izohli jumla bo'lib, ishlab chiqarish tizimlarida "
                . "ma'lumotlar bazasi indekslash va so'rov rejalashtirish haqida gapiradi.";
        } while (str_word_count(implode(' ', $sentences)) < $minWords);

        return implode(' ', $sentences);
    }

    public function test_takrorlanuvchi_draft_nashr_qilinmaydi(): void
    {
        $repeat = 'Bu jumla ataylab bir necha marta takrorlanadi va altmish belgidan uzunroq.';
        $post = $this->draft($this->cleanContent() . ' ' . str_repeat($repeat . ' ', 5));

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_toza_draft_nashr_qilinadi(): void
    {
        $post = $this->draft($this->cleanContent());

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('published', $post->fresh()->status);
    }
}
```

`Post::factory()` Vazifa 0 da yaratilgan. `draft()` yordamchisi `moderation_status` ni ochiq belgilaydi, chunki ustun default qiymati `'pending'`.

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=ContentDripPublishTest`
Kutilgan natija: `test_takrorlanuvchi_draft_nashr_qilinmaydi` FAIL — post nashr qilinib qo'yiladi, chunki eski darvozalar takrorlanishni ko'rmaydi.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`ContentDripPublish.php` da:

1. Yuqoriga import qo'shing: `use App\Services\Content\PublishGate;`
2. `POST_INTERVAL_DAYS`, `TUTORIAL_INTERVAL_DAYS`, `MIN_WORDS` konstantalarini **o'chiring**.
3. `passesGates()` metodini **butunlay o'chiring**.
4. Konstruktor orqali xizmatni oling:

```php
    public function __construct(private readonly PublishGate $gate)
    {
        parent::__construct();
    }
```

5. `handle()` dagi konstanta havolalarini almashtiring:

```php
        if ($this->isDue(false, PublishGate::POST_INTERVAL_DAYS)) {
```

```php
        if ($this->isDue(true, PublishGate::TUTORIAL_INTERVAL_DAYS)) {
```

6. `pickPublishable()` ichidagi filtrni almashtiring:

```php
            ->first(fn (Post $p): bool => $this->gate->passes($p));
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=ContentDripPublishTest`
Kutilgan natija: PASS — 2 ta test.

Keyin to'liq to'plamni ishga tushiring: `php artisan test`
Kutilgan natija: mavjud testlar buzilmagan.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/ContentDripPublish.php tests/Feature/Content/ContentDripPublishTest.php
git commit -m "refactor(content): drip publisher now uses the shared PublishGate"
```

---

## Vazifa 5: Kengaytirishdagi o'lik zonani yopish

Spec §1.2b: kengaytirish faqat `< 1500` da ishlaydi, lekin `< 2000` da `throw` qiladi. 1500–1999 oralig'idagi har bir natija yo'q qilinadi.

**Fayllar:**
- O'zgartirish: `app/Console/Commands/GenerateAiPost.php:1185-1212`
- Test: `tests/Feature/Content/PostExpansionTest.php` (yaratish)

**Interfeyslar:**
- Ishlatadi: `PublishGate::MIN_WORDS` — generatsiya va nashr chegarasi endi bitta qiymat.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Content/PostExpansionTest.php` yarating:

```php
<?php

namespace Tests\Feature\Content;

use App\Services\Content\PublishGate;
use ReflectionClass;
use Tests\TestCase;

class PostExpansionTest extends TestCase
{
    public function test_generatsiya_chegarasi_nashr_chegarasi_bilan_bir_xil(): void
    {
        $source = file_get_contents(app_path('Console/Commands/GenerateAiPost.php'));

        // O'lik zona: kengaytirish va rad etish chegaralari farq qilmasligi kerak.
        $this->assertStringNotContainsString(
            'Minimum required: 2000 words',
            $source,
            'Generatsiya hamon 2000 so\'z talab qilyapti, PublishGate::MIN_WORDS esa 1500.'
        );

        $this->assertStringContainsString(
            'PublishGate::MIN_WORDS',
            $source,
            'GenerateAiPost PublishGate::MIN_WORDS dan foydalanishi kerak.'
        );
    }

    public function test_publish_gate_chegarasi_kutilgan_qiymatda(): void
    {
        $this->assertSame(1500, PublishGate::MIN_WORDS);
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PostExpansionTest`
Kutilgan natija: FAIL — manba hamon `Minimum required: 2000 words` ni o'z ichiga oladi.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`GenerateAiPost.php` da import qo'shing: `use App\Services\Content\PublishGate;`

`1185–1212` qatorlaridagi blokni almashtiring:

```php
        // Pass 1 natijasini nashr chegarasiga nisbatan tekshiramiz.
        // Chegara PublishGate dan olinadi: generatsiya va nashr bitta ta'rifga
        // bo'ysunadi, shuning uchun 1500-1999 "o'lik zonasi" endi mavjud emas.
        $wordCount = str_word_count(strip_tags($postData['content']));

        if ($wordCount < PublishGate::MIN_WORDS) {
            $this->info("   📝 Pass 1: {$wordCount} so'z. Pass 2: kengaytirish...");

            try {
                $postData = $this->expandPostContent($postData);
                $wordCount = str_word_count(strip_tags($postData['content']));
                $this->info("   ✅ Pass 2 tugadi: {$wordCount} so'z");
            } catch (\Exception $e) {
                Log::warning('Post expansion failed, using Pass 1 content', ['error' => $e->getMessage()]);
                $this->warn("   ⚠️  Kengaytirish muvaffaqiyatsiz, Pass 1 ishlatiladi ({$wordCount} so'z)");
            }
        } else {
            $this->info("   ✅ Pass 1 yetarli: {$wordCount} so'z");
        }

        if ($wordCount < PublishGate::MIN_WORDS) {
            Log::warning('Generated content below publish threshold', [
                'required_min' => PublishGate::MIN_WORDS,
                'actual_words' => $wordCount,
                'title' => $postData['title'],
            ]);

            throw new \Exception(
                "Content too short: {$wordCount} words. Minimum required: "
                . PublishGate::MIN_WORDS . ' words.'
            );
        }
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PostExpansionTest`
Kutilgan natija: PASS — 2 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/GenerateAiPost.php tests/Feature/Content/PostExpansionTest.php
git commit -m "fix(ai): close the 1500-1999 word dead zone in post generation"
```

---

## Vazifa 6: Bo'lim-bo'lim kengaytirish (takrorlanishning ildizi)

Spec §1.2b: hozirgi prompt butun maqolani `max_tokens=4000` ichida "4000+ **so'z**" qilib qaytarishni so'raydi — bu bajarib bo'lmaydigan talab, model esa uni takrorlanish bilan "hal qiladi". Yechim: modeldan mavjud matnni **umuman qaytarmaslikni** so'rash.

**Fayllar:**
- O'zgartirish: `app/Console/Commands/GenerateAiPost.php` — `expandPostContent()`
- Test: `tests/Feature/Content/PostExpansionTest.php`

**Interfeyslar:**
- Beradi: `GenerateAiPost::splitSections(string): array<array{heading: string, body: string}>` — sof funksiya, test qilinadi.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`PostExpansionTest.php` ga qo'shing:

```php
    private function invoke(string $method, array $args = []): mixed
    {
        $command = app(\App\Console\Commands\GenerateAiPost::class);
        $ref = new ReflectionClass($command);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($command, $args);
    }

    public function test_maqola_h2_sarlavhalari_boyicha_bolinadi(): void
    {
        $markdown = "Kirish matni.\n\n## Birinchi bo'lim\nBirinchi tana.\n\n## Ikkinchi bo'lim\nIkkinchi tana.";

        $sections = $this->invoke('splitSections', [$markdown]);

        $this->assertCount(3, $sections);
        $this->assertSame('', $sections[0]['heading']);
        $this->assertSame('## Birinchi bo\'lim', $sections[1]['heading']);
        $this->assertSame('## Ikkinchi bo\'lim', $sections[2]['heading']);
    }

    public function test_sarlavhasiz_matn_bitta_bolim_boladi(): void
    {
        $sections = $this->invoke('splitSections', ["Sarlavhasiz oddiy matn."]);

        $this->assertCount(1, $sections);
        $this->assertSame('', $sections[0]['heading']);
    }
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=PostExpansionTest`
Kutilgan natija: FAIL — `Method splitSections does not exist`.

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`GenerateAiPost.php` ga yangi metod qo'shing:

```php
    /**
     * Markdown maqolani `##` sarlavhalari bo'yicha bo'ladi.
     *
     * @return array<int, array{heading: string, body: string}>
     */
    private function splitSections(string $markdown): array
    {
        $lines = preg_split('/\R/', $markdown) ?: [];
        $sections = [['heading' => '', 'body' => '']];

        foreach ($lines as $line) {
            if (preg_match('/^##\s+\S/', $line) === 1) {
                $sections[] = ['heading' => trim($line), 'body' => ''];
                continue;
            }

            $sections[array_key_last($sections)]['body'] .= $line . "\n";
        }

        foreach ($sections as $i => $section) {
            $sections[$i]['body'] = trim($section['body']);
        }

        // Bo'sh muqaddimani tashlab yuboramiz (maqola darhol sarlavha bilan boshlansa).
        if ($sections[0]['heading'] === '' && $sections[0]['body'] === '' && count($sections) > 1) {
            array_shift($sections);
        }

        return array_values($sections);
    }
```

`expandPostContent()` ni to'liq almashtiring:

```php
    /**
     * PASS 2: maqolani BO'LIM-BO'LIM kengaytiradi.
     *
     * Eski versiya butun maqolani qaytadan yozishni so'rar edi va shu bilan
     * birga max_tokens=4000 chegarasini qo'yardi — 4000 SO'Z ~5300+ token
     * bo'lgani uchun talab bajarilmas edi va model takrorlanishga o'tardi
     * (spec §1.2b). Endi model faqat YANGI matn qaytaradi; mavjud matn
     * hech qachon qayta chiqarilmaydi, shuning uchun uni takrorlay olmaydi.
     */
    private function expandPostContent(array $postData, int $retryCount = 0, int $maxRetries = 2): array
    {
        $sections = $this->splitSections((string) $postData['content']);
        $expanded = [];

        foreach ($sections as $section) {
            $piece = trim($section['heading'] . "\n" . $section['body']);
            $expanded[] = $piece;

            // Muqaddimani kengaytirmaymiz — u qisqa bo'lishi kerak.
            if ($section['heading'] === '' || $section['body'] === '') {
                continue;
            }

            try {
                $addition = $this->callOpenAI([
                    [
                        'role' => 'system',
                        'content' => 'You add new material to an existing article section. '
                            . 'You never restate, summarise, or repeat what you are given.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Below is one section of a technical article.\n\n"
                            . "SECTION:\n---\n{$piece}\n---\n\n"
                            . "Write 200-350 words of ADDITIONAL material that continues this section: "
                            . "a concrete code example, an edge case, or a gotcha not already mentioned.\n\n"
                            . "Rules:\n"
                            . "- Do NOT repeat or rephrase any sentence above.\n"
                            . "- Do NOT write a heading.\n"
                            . "- Do NOT claim personal or team experience.\n"
                            . "- Start directly with the new material.",
                    ],
                ], 700, 0.7, false);

                $addition = trim(preg_replace('/^```[a-z]*\n?/i', '', $addition) ?? '');

                if ($addition !== '') {
                    $expanded[] = $addition;
                }
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'rate_limit') && $retryCount < $maxRetries) {
                    $wait = 20 + ($retryCount * 10);
                    $this->warn("   ⏸️  Rate limit. {$wait}s kutilmoqda...");
                    sleep($wait);

                    return $this->expandPostContent($postData, $retryCount + 1, $maxRetries);
                }

                Log::warning('Section expansion skipped', [
                    'error' => $e->getMessage(),
                    'heading' => $section['heading'],
                ]);
            }
        }

        $postData['content'] = implode("\n\n", array_filter($expanded));

        return $postData;
    }
```

> **Diqqat:** `700` token har bir bo'lim uchun ~350 so'zga yetadi — maqsad endi byudjet ichida. Eski `4000` qiymati bilan adashtirmang.

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=PostExpansionTest`
Kutilgan natija: PASS — 4 ta test.

- [ ] **Qadam 5: Commit qiling**

```bash
git add app/Console/Commands/GenerateAiPost.php tests/Feature/Content/PostExpansionTest.php
git commit -m "fix(ai): expand articles section-by-section so the model never restates existing text"
```

---

## Vazifa 7: `content:health-check` — jimlikni imkonsiz qilish

Spec §1.5: eng jiddiy nuqson — to'rt hafta jimlik va hech qanday signal yo'q.

**Fayllar:**
- Yaratish: `app/Console/Commands/ContentHealthCheck.php`
- O'zgartirish: `routes/console.php`, `.env.example`
- Test: `tests/Feature/Content/ContentHealthCheckTest.php`

**Interfeyslar:**
- Ishlatadi: `PublishGate::passes()`, `PublishGate::POST_INTERVAL_DAYS`, `PublishGate::TUTORIAL_INTERVAL_DAYS`.

- [ ] **Qadam 1: Yiqiladigan testni yozing**

`tests/Feature/Content/ContentHealthCheckTest.php` yarating:

```php
<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentHealthCheckTest extends TestCase
{
    use RefreshDatabase;

    private function cleanContent(int $minWords = 1600): string
    {
        $sentences = [];
        $i = 0;
        do {
            $i++;
            $sentences[] = "Bu {$i}-raqamli noyob izohli jumla bo'lib, ishlab chiqarish tizimlarida "
                . "ma'lumotlar bazasi indekslash va so'rov rejalashtirish haqida gapiradi.";
        } while (str_word_count(implode(' ', $sentences)) < $minWords);

        return implode(' ', $sentences);
    }

    public function test_eski_korpus_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now()->subDays(30),
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('stale_posts')
            ->assertExitCode(1);
    }

    public function test_nashrga_tayyor_draft_yoqligi_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now(),
            'content' => $this->cleanContent(),
        ]);
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'approved',
            'content' => 'Juda qisqa draft.',
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('empty_publishable_backlog')
            ->assertExitCode(1);
    }

    public function test_kutilayotgan_moderatsiya_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('unattended_moderation')
            ->assertExitCode(1);
    }
}
```

- [ ] **Qadam 2: Testni ishga tushiring va yiqilishini tasdiqlang**

Buyruq: `php artisan test --filter=ContentHealthCheckTest`
Kutilgan natija: FAIL — `The command "content:health-check" does not exist.`

- [ ] **Qadam 3: Minimal implementatsiyani yozing**

`app/Console/Commands/ContentHealthCheck.php` yarating:

```php
<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Kontent quvurining kunlik salomatlik tekshiruvi.
 *
 * Mavjud bo'lish sababi: 2026-08-04 dan beri hech narsa nashr qilinmadi
 * va hech kim xabar topmadi (spec §1.5). Bu buyruq shu jimlikni ushlaydi.
 *
 * MUHIM: bo'sh zaxira XOM draft soni bilan emas, DARVOZADAN O'TADIGAN
 * draftlar soni bilan o'lchanadi. Production'da 255 ta draft va 0 ta
 * nashrga yaroqli post bor edi — xom sanoq butun uzilish davomida
 * "sog'lom" deb ko'rsatgan bo'lardi (spec §5).
 */
class ContentHealthCheck extends Command
{
    protected $signature = 'content:health-check {--dry-run : Faqat hisobot, xat yuborilmaydi}';
    protected $description = 'Kontent quvuri sog\'ligini tekshiradi va jimlikni aniqlaydi';

    private const MIN_PUBLISHABLE_BACKLOG = 3;

    public function __construct(private readonly PublishGate $gate)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $problems = [];

        $lastPost = Post::where('status', 'published')->whereNull('series_title')->max('published_at');
        if (! $lastPost || Carbon::parse($lastPost)->lt(now()->subDays(PublishGate::POST_INTERVAL_DAYS + 2))) {
            $problems['stale_posts'] = 'Oxirgi post: ' . ($lastPost ?: 'hech qachon');
        }

        $lastTutorial = Post::where('status', 'published')->whereNotNull('series_title')->max('published_at');
        if (! $lastTutorial || Carbon::parse($lastTutorial)->lt(now()->subDays(PublishGate::TUTORIAL_INTERVAL_DAYS + 3))) {
            $problems['stale_tutorials'] = 'Oxirgi tutorial: ' . ($lastTutorial ?: 'hech qachon');
        }

        $publishable = 0;
        $pendingOnly = 0;

        Post::where('status', 'draft')->select('id', 'content', 'moderation_status')
            ->chunk(200, function ($drafts) use (&$publishable, &$pendingOnly) {
                foreach ($drafts as $draft) {
                    $failures = $this->gate->failures($draft);

                    if ($failures === []) {
                        $publishable++;
                    } elseif ($failures === ['moderation_pending']) {
                        $pendingOnly++;
                    }
                }
            });

        if ($publishable < self::MIN_PUBLISHABLE_BACKLOG) {
            $problems['empty_publishable_backlog'] = "Darvozadan o'tadigan draftlar: {$publishable}";
        }

        if ($pendingOnly > 0) {
            $problems['unattended_moderation'] = "Faqat moderatsiya kutayotgan draftlar: {$pendingOnly}";
        }

        if ($problems === []) {
            $this->info("✅ Kontent quvuri sog'lom. Nashrga tayyor draftlar: {$publishable}");

            return self::SUCCESS;
        }

        foreach ($problems as $key => $detail) {
            $this->error("{$key}: {$detail}");
        }

        Log::error('Content pipeline degraded', $problems);

        if (! $this->option('dry-run')) {
            $this->notify($problems);
        }

        return self::FAILURE;
    }

    private function notify(array $problems): void
    {
        // DIQQAT: config('backup.notifications.mail.to') ISHLATILMAYDI —
        // config/backup.php:214 da u qattiq yozilgan 'your@example.com'
        // placeholder qiymati, env'dan olinmaydi. Ogohlantirish shu manzilga
        // jimgina ketib qolishi mumkin edi. Shuning uchun alohida kalit.
        $to = env('CONTENT_ALERT_EMAIL') ?: config('mail.from.address');

        if (! $to || str_contains((string) $to, 'example.com')) {
            Log::warning('Health check alert not sent: CONTENT_ALERT_EMAIL is not configured');

            return;
        }

        $body = "Kontent quvurida muammo:\n\n";
        foreach ($problems as $key => $detail) {
            $body .= "- {$key}: {$detail}\n";
        }

        try {
            Mail::raw($body, fn ($message) => $message->to($to)->subject('NextGenBeing: kontent quvuri to\'xtadi'));
        } catch (\Throwable $e) {
            Log::warning('Health check mail failed', ['error' => $e->getMessage()]);
        }
    }
}
```

- [ ] **Qadam 4: Testni ishga tushiring va o'tishini tasdiqlang**

Buyruq: `php artisan test --filter=ContentHealthCheckTest`
Kutilgan natija: PASS — 3 ta test.

- [ ] **Qadam 5: Jadvalga qo'shing va `.env.example` ni hujjatlashtiring**

`routes/console.php` oxiriga:

```php
// Kontent quvuri salomatligi — 2026-08 dagi to'rt haftalik jimlik takrorlanmasligi uchun.
Schedule::command('content:health-check')
    ->dailyAt('19:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
```

`.env.example` ga:

```
# Avtomatik kontent generatsiyasini yoqadi. Ikkita cron shu bayroqqa bog'liq:
#   - routes/console.php:26  (kunlik post generatsiyasi)
#   - routes/console.php:252 (haftalik tutorial generatsiyasi)
# Production'da ataylab false — AdSense uchun korpusni tozalash davrida o'chirilgan.
BLOG_AUTO_PUBLISH=false

# content:health-check ogohlantirishlari shu manzilga yuboriladi.
# Bo'sh qoldirilsa ogohlantirish YUBORILMAYDI (faqat log'ga yoziladi).
CONTENT_ALERT_EMAIL=
```

Va production `.env` ga haqiqiy manzilni qo'ying — aks holda §1.5 dagi "jimlik" muammosi qaytadi, faqat bu safar log ichida.

- [ ] **Qadam 6: To'liq to'plamni ishga tushiring**

Buyruq: `php artisan test`
Kutilgan natija: barcha testlar PASS.

- [ ] **Qadam 7: Commit qiling**

```bash
git add app/Console/Commands/ContentHealthCheck.php tests/Feature/Content/ContentHealthCheckTest.php routes/console.php .env.example
git commit -m "feat(content): add content:health-check so pipeline silence raises an alarm"
```

---

## Yakuniy tekshiruv (kod emas, qo'lda)

- [ ] Production'da yangi darvozalarni **quruq** ishga tushiring va rad etish darajasini ko'ring:

```bash
ssh root@161.35.73.129 "cd /var/www/nextgenbeing && php artisan content:health-check --dry-run"
```

- [ ] Rad etilgan draftlardan namuna o'qing va rad etish to'g'ri ekanini tasdiqlang (spec §13). 255 taning ko'pchiligi rad etilishi **kutilgan natija**.
- [ ] `BLOG_AUTO_PUBLISH` ni **o'zgartirmang** — bu alohida qaror (spec §1.1).

## Bu rejada YO'Q narsalar

- C2 (trend mavzular) va C3 (marketplace repozitsiyasi) — har biri o'z rejasini oladi.
- Tutorial generatsiyasi sifati (spec §1.3) — 25 ta draftdan 0 tasi o'tadi; bu alohida ish.
- blog-bot tiklash (Python o'rnatish, venv) — spec §13, kod emas.
