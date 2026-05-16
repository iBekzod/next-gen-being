<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrewarmCacheCommand extends Command
{
    protected $signature = 'cache:prewarm
        {--limit=20 : How many top posts to warm}
        {--include-categories : Also warm category pages}
        {--include-tags : Also warm popular tag pages}';

    protected $description = 'Pre-fetch hot URLs to warm view + query caches so first real visitor gets a fast response.';

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        $limit = (int) $this->option('limit');
        $urls = [];

        // Top-level pages
        foreach (['/', '/posts', '/tutorials', '/authors', '/paths', '/collections', '/pricing'] as $u) {
            $urls[] = $base . $u;
        }

        // Hottest posts (by views)
        Post::where('status', 'published')
            ->whereNotNull('slug')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->pluck('slug')
            ->each(function($slug) use (&$urls, $base) { $urls[] = "{$base}/posts/{$slug}"; });

        // Author profiles
        User::whereNotNull('slug')->pluck('slug')
            ->each(function($slug) use (&$urls, $base) { $urls[] = "{$base}/authors/{$slug}"; });

        if ($this->option('include-categories')) {
            Category::where('is_active', true)->pluck('slug')
                ->each(function($slug) use (&$urls, $base) { $urls[] = "{$base}/categories/{$slug}"; });
        }

        if ($this->option('include-tags')) {
            Tag::limit(20)->pluck('slug')
                ->each(function($slug) use (&$urls, $base) { $urls[] = "{$base}/tags/{$slug}"; });
        }

        $urls = array_values(array_unique($urls));
        $this->info("Warming " . count($urls) . " URLs...");

        $ok = $fail = 0;
        $start = microtime(true);
        foreach ($urls as $url) {
            try {
                $res = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'NextGenBeing-CachePrewarmer/1.0'])
                    ->get($url);
                if ($res->successful()) {
                    $ok++;
                    $this->line("  <fg=green>200</> " . strlen($res->body()) / 1024 . "kb  {$url}");
                } else {
                    $fail++;
                    $this->line("  <fg=red>{$res->status()}</>  {$url}");
                }
            } catch (\Throwable $e) {
                $fail++;
                Log::warning("Prewarm failed for {$url}: " . $e->getMessage());
            }
        }
        $dur = round(microtime(true) - $start, 1);
        $this->info("Done in {$dur}s · {$ok} OK · {$fail} failed");
        return self::SUCCESS;
    }
}
