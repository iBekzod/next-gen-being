<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SeoService
{
    public function generateSitemap(): void
    {
        $sitemap = Sitemap::create();

        // Add homepage
        $sitemap->add(
            Url::create(route('home'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // Add posts index
        $sitemap->add(
            Url::create(route('posts.index'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        // Add published posts
        Post::published()
            ->select(['slug', 'updated_at'])
            ->chunk(1000, function ($posts) use ($sitemap) {
                foreach ($posts as $post) {
                    $sitemap->add(
                        Url::create(route('posts.show', $post->slug))
                            ->setLastModificationDate($post->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            });

        // Add categories
        Category::active()
            ->select(['slug', 'updated_at'])
            ->each(function ($category) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('categories.show', $category->slug))
                        ->setLastModificationDate($category->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7)
                );
            });

        // Add tags
        Tag::active()
            ->whereHas('publishedPosts')
            ->select(['slug', 'updated_at'])
            ->each(function ($tag) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('tags.show', $tag->slug))
                        ->setLastModificationDate($tag->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.6)
                );
            });

        // Write sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    public function generateRssFeed(): string
    {
        $posts = Post::published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(50)
            ->get();

        $rss = view('feeds.rss', compact('posts'))->render();

        Storage::disk('public')->put('feed.xml', $rss);

        return $rss;
    }

    public function generateStructuredData(Post $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'image' => $post->featured_image ? asset($post->featured_image) : null,
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => route('authors.show', $post->author->id),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('uploads/logo.png'),
                ],
            ],
            'datePublished' => $post->published_at?->toISOString(),
            'dateModified' => $post->updated_at->toISOString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('posts.show', $post->slug),
            ],
            'articleSection' => $post->category->name,
            'keywords' => $post->tags->pluck('name')->join(', '),
            'wordCount' => str_word_count(strip_tags($post->content)),
            'timeRequired' => 'PT' . $post->read_time . 'M',
        ];
    }

    public function getMetaTags(string $title = null, string $description = null, string $image = null, array $extra = []): array
    {
        $siteName = config('app.name');
        $siteUrl = config('app.url');

        $meta = [
            'title' => $title ?: setting('default_meta_title', $siteName),
            'description' => $description ?: setting('default_meta_description'),
            'image' => $image ?: asset('images/og-default.jpg'),
            'url' => request()->url(),
            'site_name' => $siteName,
            'type' => 'article',
            'locale' => app()->getLocale(),
        ];

        return array_merge($meta, $extra);
    }

    public function pingSearchEngines(): void
    {
        $sitemapUrl = urlencode(url('/sitemap.xml'));

        $searchEngines = [
            "https://www.google.com/ping?sitemap={$sitemapUrl}",
            "https://www.bing.com/ping?sitemap={$sitemapUrl}",
            "https://submissions.ask.com/ping?sitemap={$sitemapUrl}",
        ];

        foreach ($searchEngines as $pingUrl) {
            try {
                file_get_contents($pingUrl);
            } catch (\Exception $e) {
                logger()->warning("Failed to ping search engine: {$pingUrl}");
            }
        }
    }
}
