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
