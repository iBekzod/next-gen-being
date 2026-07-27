<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\Route;
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

        // Add published posts (exclude noindex'd ones — they shouldn't be in the sitemap)
        Post::published()
            ->where('noindex', false)
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

        // Add marketplace (index + every published listing)
        if (Route::has('marketplace.index') && class_exists(MarketplaceListing::class)) {
            $sitemap->add(
                Url::create(route('marketplace.index'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.9)
            );

            MarketplaceListing::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->select(['slug', 'updated_at', 'published_at'])
                ->each(function ($listing) use ($sitemap) {
                    $sitemap->add(
                        Url::create(route('marketplace.show', $listing->slug))
                            ->setLastModificationDate($listing->updated_at ?? $listing->published_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                });
        }

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
            'author' => $this->buildAuthorSchema($post),
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

    /**
     * Build the schema.org Person object for the post's author with full E-E-A-T
     * signals: the author's real, verifiable identity (`sameAs` → social
     * profiles), their photo, and bio. These are the machine-readable signals
     * Google uses to attribute expertise and trust to bylined content — the
     * difference between "anonymous AI text" and "written by a known person".
     */
    protected function buildAuthorSchema(Post $post): array
    {
        $author = $post->author;

        $schema = [
            '@type' => 'Person',
            'name' => $author->name,
            'url' => $author->slug
                ? route('authors.show', $author->slug)
                : route('authors.show', $author->id),
        ];

        if (!empty($author->bio)) {
            $schema['description'] = $author->bio;
        }

        if (!empty($author->avatar)) {
            $schema['image'] = str_starts_with($author->avatar, 'http')
                ? $author->avatar
                : asset($author->avatar);
        }

        // sameAs links the byline to the author's external identity. Skip the
        // internal /authors/ profile URL (that's `url`, not a separate identity).
        $sameAs = array_values(array_filter([
            $author->linkedin ?? null,
            $author->twitter ?? null,
            (!empty($author->website) && !str_contains($author->website, '/authors/'))
                ? $author->website
                : null,
        ]));

        if (!empty($sameAs)) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
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
