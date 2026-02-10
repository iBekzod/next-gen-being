<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        try {
            $cacheKey = Setting::cacheKey($key);

            $value = Cache::rememberForever($cacheKey, function () use ($key) {
                $setting = Setting::query()->where('key', $key)->first();

                return $setting?->value ?? Setting::CACHE_MISS;
            });

            return $value === Setting::CACHE_MISS ? $default : $value;
        } catch (\Exception $e) {
            // Log error but don't break the page
            \Log::error("Setting helper error for key '{$key}': " . $e->getMessage());
            return $default;
        }
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date in a human-readable format.
     *
     * @param \Carbon\Carbon|string $date
     * @param string $format
     * @return string
     */
    function format_date($date, string $format = 'M j, Y')
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }
}

if (!function_exists('read_time')) {
    /**
     * Calculate the estimated reading time for content.
     *
     * @param string $content
     * @param int $wordsPerMinute
     * @return int
     */
    function read_time(string $content, int $wordsPerMinute = 200): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / $wordsPerMinute);

        return max(1, $minutes);
    }
}

if (!function_exists('excerpt')) {
    /**
     * Generate an excerpt from content.
     *
     * @param string $content
     * @param int $length
     * @param string $suffix
     * @return string
     */
    function excerpt(string $content, int $length = 150, string $suffix = '...'): string
    {
        $cleanContent = strip_tags($content);

        if (strlen($cleanContent) <= $length) {
            return $cleanContent;
        }

        return rtrim(substr($cleanContent, 0, $length)) . $suffix;
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Check if the given route is active.
     *
     * @param string|array $routes
     * @param string $activeClass
     * @param string $inactiveClass
     * @return string
     */
    function is_active_route($routes, string $activeClass = 'active', string $inactiveClass = ''): string
    {
        if (is_string($routes)) {
            $routes = [$routes];
        }

        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $activeClass;
            }
        }

        return $inactiveClass;
    }
}

if (!function_exists('generate_meta_tags')) {
    /**
     * Generate meta tags for SEO.
     *
     * @param array $data
     * @return string
     */
    function generate_meta_tags(array $data = []): string
    {
        $defaults = [
            'title' => setting('default_meta_title'),
            'description' => setting('default_meta_description'),
            'keywords' => setting('default_meta_keywords'),
            'author' => setting('site_name'),
            'image' => asset('images/og-default.jpg'),
            'url' => request()->url(),
            'type' => 'website',
        ];

        $meta = array_merge($defaults, $data);
        $tags = [];

        // Basic meta tags
        $tags[] = '<meta name="title" content="' . e($meta['title']) . '">';
        $tags[] = '<meta name="description" content="' . e($meta['description']) . '">';
        $tags[] = '<meta name="keywords" content="' . e($meta['keywords']) . '">';
        $tags[] = '<meta name="author" content="' . e($meta['author']) . '">';

        // Open Graph tags
        $tags[] = '<meta property="og:type" content="' . e($meta['type']) . '">';
        $tags[] = '<meta property="og:url" content="' . e($meta['url']) . '">';
        $tags[] = '<meta property="og:title" content="' . e($meta['title']) . '">';
        $tags[] = '<meta property="og:description" content="' . e($meta['description']) . '">';
        $tags[] = '<meta property="og:image" content="' . e($meta['image']) . '">';

        // Twitter Card tags
        $tags[] = '<meta property="twitter:card" content="summary_large_image">';
        $tags[] = '<meta property="twitter:url" content="' . e($meta['url']) . '">';
        $tags[] = '<meta property="twitter:title" content="' . e($meta['title']) . '">';
        $tags[] = '<meta property="twitter:description" content="' . e($meta['description']) . '">';
        $tags[] = '<meta property="twitter:image" content="' . e($meta['image']) . '">';

        return implode("\n", $tags);
    }
}

if (!function_exists('lemonsqueezy_store_url')) {
    /**
     * Get the LemonSqueezy store URL.
     *
     * @param string|null $path Optional path to append
     * @return string
     */
    function lemonsqueezy_store_url(?string $path = null): string
    {
        $domain = config('services.lemonsqueezy.store_domain', 'store.nextgenbeing.com');
        $url = 'https://' . $domain;

        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }
}

