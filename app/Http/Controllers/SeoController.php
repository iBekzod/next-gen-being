<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('seo:sitemap.xml', now()->addMinutes(60), function () {
            $entries = $this->buildSitemapEntries();

            return view('seo.sitemap', [
                'entries' => $entries,
            ])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = Cache::remember('seo:robots.txt', now()->addMinutes(60), function () {
            $lines = [
                'User-agent: *',
                'Allow: /',
                '',
                'User-agent: GPTBot',
                'Allow: /',
                'User-agent: ChatGPT-User',
                'Allow: /',
                'User-agent: Claude-Web',
                'Allow: /',
            ];

            $custom = trim((string) setting('seo_custom_robots', ''));
            if ($custom !== '') {
                $lines[] = '';
                foreach (preg_split('/\r?\n/', $custom) as $customLine) {
                    $lines[] = rtrim($customLine);
                }
            }

            if (Route::has('seo.sitemap')) {
                $lines[] = '';
                $lines[] = 'Sitemap: ' . route('seo.sitemap');
            }

            return implode(PHP_EOL, array_filter($lines, fn ($line) => $line !== null));
        });

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    protected function buildSitemapEntries(): array
    {
        $entries = [];
        $now = now();

        $entries[] = $this->formatEntry(route('home'), $now, 'daily', '1.0');
        if (Route::has('subscription.plans')) {
            $entries[] = $this->formatEntry(route('subscription.plans'), $now, 'weekly', '0.9');
        }
        $entries[] = $this->formatEntry(route('posts.index'), $now, 'daily', '0.9');
        $entries[] = $this->formatEntry(route('privacy'), $now, 'yearly', '0.3');
        $entries[] = $this->formatEntry(route('terms'), $now, 'yearly', '0.3');
        if (Route::has('refund')) {
            $entries[] = $this->formatEntry(route('refund'), $now, 'yearly', '0.3');
        }

        Post::published()
            ->with('category')
            ->latest('updated_at')
            ->get()
            ->each(function (Post $post) use (&$entries) {
                $entries[] = $this->formatEntry(
                    route('posts.show', $post->slug),
                    $post->updated_at ?? $post->published_at,
                    'weekly',
                    $post->is_featured ? '0.9' : '0.8'
                );
            });

        Category::active()
            ->ordered()
            ->withCount('publishedPosts')
            ->get()
            ->each(function (Category $category) use (&$entries) {
                if ($category->published_posts_count > 0) {
                    $entries[] = $this->formatEntry(
                        route('categories.show', $category->slug),
                        $category->updated_at,
                        'weekly',
                        '0.6'
                    );
                }
            });

        Tag::active()
            ->withCount('publishedPosts')
            ->orderByDesc('published_posts_count')
            ->get()
            ->each(function (Tag $tag) use (&$entries) {
                if ($tag->published_posts_count > 0) {
                    $entries[] = $this->formatEntry(
                        route('tags.show', $tag->slug),
                        $tag->updated_at,
                        'weekly',
                        '0.5'
                    );
                }
            });

        return $entries;
    }

    protected function formatEntry(string $url, $lastModified = null, string $changeFrequency = 'weekly', string $priority = '0.7'): array
    {
        $lastModString = null;
        if ($lastModified) {
            $lastModString = optional($lastModified)->copy()->tz('UTC')->toAtomString();
        }

        return [
            'loc' => $url,
            'lastmod' => $lastModString,
            'changefreq' => $changeFrequency,
            'priority' => $priority,
        ];
    }
}
