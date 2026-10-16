<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\User;
use Database\Seeders\ThreeDScenesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThreeDScenesTest extends TestCase
{
    use RefreshDatabase;

    private function seedScenes(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        User::factory()->create();
        (new ThreeDScenesSeeder())->run();
    }

    public function test_seeds_three_published_scenes_idempotently(): void
    {
        $this->seedScenes();
        (new ThreeDScenesSeeder())->run();

        $scenes = MarketplaceListing::where('category', '3d-scenes')->get();
        $this->assertCount(3, $scenes);
        foreach ($scenes as $scene) {
            $this->assertSame('published', $scene->status);
            $this->assertSame('static', $scene->demo_type);
            Storage::disk('public')->assertExists("demos/{$scene->slug}/index.html");
            $this->assertMatchesRegularExpression("#^demos/{$scene->slug}/poster-[0-9a-f]{8}\.jpg$#", $scene->thumbnail);
            Storage::disk('public')->assertExists($scene->thumbnail);
            // No invented social proof, ever.
            $this->assertSame(0, (int) $scene->sales_count);
            $this->assertFalse($scene->hasReviews());
            $this->assertLessThanOrEqual(165, mb_strlen($scene->seoDescription()));
        }
    }

    public function test_one_scene_is_free_and_two_cost_four_dollars(): void
    {
        $this->seedScenes();

        $free = MarketplaceListing::where('slug', 'stellar-drift-galaxy-hero')->firstOrFail();
        $tier = $free->tiers()->firstOrFail();
        $this->assertSame('prompt', $tier->tier);
        $this->assertTrue((bool) $tier->is_free);
        $this->assertNotEmpty($tier->file_path);
        $md = Storage::disk('private')->get($tier->file_path);
        $this->assertStringContainsString('Master prompt', $md);
        $this->assertStringContainsString('<!doctype html>', $md, 'free file must include the full source');
        $this->assertStringNotContainsString('{{SOURCE}}', $md);

        foreach (['liquid-glass-blob-hero', 'meridian-wire-terrain-flyover'] as $slug) {
            $paid = MarketplaceListing::where('slug', $slug)->firstOrFail();
            $this->assertCount(1, $paid->tiers);
            $tier = $paid->tiers->first();
            $this->assertSame('design', $tier->tier);
            $this->assertFalse((bool) $tier->is_free);
            $this->assertEquals(4, (float) $tier->price);
            $this->assertStringEndsWith('.zip', $tier->file_path);
            Storage::disk('private')->assertExists($tier->file_path);

            // the ZIP holds the prompt and the source
            $tmp = tempnam(sys_get_temp_dir(), 'z');
            file_put_contents($tmp, Storage::disk('private')->get($tier->file_path));
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tmp));
            $this->assertNotFalse($zip->locateName('PROMPT.md'));
            $this->assertNotFalse($zip->locateName('scene.html'));
            $this->assertStringContainsString('three', $zip->getFromName('scene.html'));
            $zip->close();
            @unlink($tmp);
        }
    }

    public function test_category_filter_lists_the_three_scenes_with_posters_not_iframes(): void
    {
        $this->seedScenes();

        $html = $this->get(route('marketplace.index', ['category' => '3d-scenes']))
            ->assertOk()
            ->assertSee('3D Scenes')
            ->assertSee('Free')
            ->getContent();

        foreach (['stellar-drift-galaxy-hero', 'liquid-glass-blob-hero', 'meridian-wire-terrain-flyover'] as $slug) {
            $this->assertStringContainsString("/market/{$slug}", $html);
            $this->assertMatchesRegularExpression("#demos/{$slug}/poster-[0-9a-f]{8}\.jpg#", $html);
        }
        // WebGL must never load in the grid: no card iframes for poster items.
        $this->assertStringNotContainsString('class="m-demo"', $html);
        $this->assertStringContainsString('three.js', $html);
    }

    public function test_item_page_has_demo_meta_jsonld_and_no_fake_rating(): void
    {
        $this->seedScenes();
        $scene = MarketplaceListing::where('slug', 'liquid-glass-blob-hero')->firstOrFail();

        $res = $this->get(route('marketplace.show', $scene))->assertOk();
        $html = $res->getContent();

        $res->assertSee('demos/liquid-glass-blob-hero/index.html', false);
        $res->assertSee('<meta name="description" content="'.e($scene->seoDescription()).'"', false);
        $this->assertMatchesRegularExpression('#property="og:image" content="[^"]*/storage/demos/liquid-glass-blob-hero/poster-[0-9a-f]{8}\.jpg"#', $html);
        $res->assertSee('Prompt + source');

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $product = null;
        foreach ($m[1] as $json) {
            $ld = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (($ld['@type'] ?? null) === 'Product') {
                $product = $ld;
            }
        }
        $this->assertNotNull($product, 'Product JSON-LD missing');
        $this->assertSame('Offer', $product['offers']['@type']);
        $this->assertSame('4.00', $product['offers']['price']);
        $this->assertSame('USD', $product['offers']['priceCurrency']);
        $this->assertArrayNotHasKey('aggregateRating', $product);
        $this->assertArrayNotHasKey('review', $product);
        $this->assertMatchesRegularExpression('#poster-[0-9a-f]{8}\.jpg$#', $product['image']);
    }

    public function test_free_scene_page_has_the_email_gate_in_english(): void
    {
        $this->seedScenes();
        $scene = MarketplaceListing::where('slug', 'stellar-drift-galaxy-hero')->firstOrFail();

        $res = $this->get(route('marketplace.show', $scene))->assertOk();
        $res->assertSee(route('marketplace.prompt.request', $scene), false);
        $res->assertSee('name="email"', false);
        $res->assertSee('Get it free');

        $html = $res->getContent();
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        foreach ($m[1] as $json) {
            $ld = json_decode($json, true);
            if (($ld['@type'] ?? null) === 'Product') {
                $this->assertSame('0.00', $ld['offers']['price']);
            }
        }
    }

    public function test_paid_scene_requires_login_to_buy(): void
    {
        $this->seedScenes();
        $tier = MarketplaceListing::where('slug', 'meridian-wire-terrain-flyover')->firstOrFail()->tiers()->firstOrFail();

        $this->post(route('digital-products.purchase', $tier))->assertRedirect(route('login'));
    }
}
