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
        // NOTE: getJson() always JSON-encodes its (default empty) data array as the
        // request body — even for GET, it sends literal "[]", not "". A real GET from
        // the bot has no body at all, so we use get() here to match production and to
        // match the "{ts}." (empty-body) signature signedHeaders() computes.
        $r = $this->withHeaders($this->signedHeaders(''))->get('/api/bot/next-topic');

        $r->assertOk()->assertJson(['ok' => true, 'topic' => null]);
    }

    public function test_navbatdagi_mavzu_qaytariladi(): void
    {
        $a = ContentSource::create(['name'=>'Alpha','url'=>'https://a.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);
        $b = ContentSource::create(['name'=>'Beta','url'=>'https://b.example','category'=>'news','trust_level'=>90,'scraping_enabled'=>true]);
        $p = CollectedContent::create(['content_source_id'=>$a->id,'external_url'=>'https://a.example/1','title'=>'Envoy adds a new filter chain','excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour()]);
        CollectedContent::create(['content_source_id'=>$b->id,'external_url'=>'https://b.example/1','title'=>'Envoy filter chain rework','excerpt'=>'x','full_content'=>str_repeat('body ',30),'content_type'=>'article','published_at'=>now()->subHour(),'is_duplicate'=>true,'duplicate_of'=>$p->id]);

        $r = $this->withHeaders($this->signedHeaders(''))->get('/api/bot/next-topic');

        $r->assertOk()
          ->assertJsonPath('ok', true)
          ->assertJsonPath('topic.title', 'Envoy adds a new filter chain');
        $this->assertNotEmpty($r->json('topic.sources'));
    }
}
