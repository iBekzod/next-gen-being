<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\Content\NewsBriefGate;
use App\Services\ContentModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * HMAC-authenticated bot endpoint.
 *
 * Local blog-bot (running on dev laptop) uses this to submit posts generated via
 * Claude Code CLI (subscription-backed). Server inserts as draft, runs the same
 * quality gates as the existing AI cron, returns the post id.
 *
 * The bot also sends a heartbeat every 15 min so the server's daily cron knows to
 * skip its API-based fallback while the bot is alive.
 */
class BotPostController extends Controller
{
    private const HEARTBEAT_CACHE_KEY = 'bot:last_seen';
    private const TIMESTAMP_TOLERANCE_SECONDS = 300; // 5 min window

    public function submitPost(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature or stale timestamp'], 401);
        }

        // Refresh heartbeat too — submitting a post counts as "alive"
        Cache::put(self::HEARTBEAT_CACHE_KEY, now()->toIso8601String(), now()->addHours(48));

        $data = $request->validate([
            'title' => 'required|string|min:10|max:255',
            'content' => 'required|string|min:1000',
            'excerpt' => 'required|string|min:50|max:500',
            'tags' => 'array|max:10',
            'tags.*' => 'string|max:60',
            'category_slug' => 'nullable|string|max:100',
            'author_id' => 'required|integer|exists:users,id',
            'featured_image_url' => 'nullable|url|max:2048',
            'image_attribution' => 'nullable|array',
            'series_title' => 'nullable|string|max:255',
            // From blog-bot's pipeline (quality_gate.py): SEO fields and the
            // editor review that content:drip requires before auto-publishing.
            'seo' => 'nullable|array',
            'seo.description' => 'nullable|string|max:320',
            'seo.focus_keyword' => 'nullable|string|max:120',
            'seo.meta_title' => 'nullable|string|max:120',
            'quality' => 'nullable|array',
            'quality.passed' => 'nullable|boolean',
            'quality.mean' => 'nullable|numeric',
            'quality.scores' => 'nullable|array',
            'quality.summary' => 'nullable|string|max:1000',
        ]);

        // Quality gate — same one we apply to AI-generated posts
        $moderation = app(ContentModerationService::class)->moderateContent(
            $data['title'],
            $data['content'],
            $data['excerpt']
        );
        $moderationStatus = ($moderation['passed'] ?? false) && ($moderation['score'] ?? 0) >= 75
            ? 'approved'
            : 'pending';

        // Pick a category — by slug if provided, else infer from category list
        $categoryId = null;
        if (!empty($data['category_slug'])) {
            $categoryId = Category::where('slug', $data['category_slug'])->value('id');
        }
        $categoryId ??= Category::where('slug', 'web-development')->value('id')
            ?? Category::query()->value('id');

        // Use bot's pre-picked image if provided, otherwise fall back to server-side picker
        $imageData = null;
        if (!empty($data['featured_image_url'])) {
            $imageData = [
                'url' => $data['featured_image_url'],
                'attribution' => $data['image_attribution'] ?? null,
            ];
        } else {
            // Bot didn't pick one — server falls back to ImageGenerationService
            $imageTopic = trim(implode(' ', array_slice($data['tags'] ?? [], 0, 3)) . ' ' . $data['title']);
            try {
                $imageData = app(\App\Services\ImageGenerationService::class)
                    ->generateFeaturedImage($data['title'], $imageTopic);
            } catch (\Throwable $e) {
                Log::warning('Image fetch failed for bot post: ' . $e->getMessage());
            }
        }

        $post = Post::create([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'author_id' => $data['author_id'],
            'category_id' => $categoryId,
            'featured_image' => $imageData['url'] ?? null,
            'image_attribution' => $imageData['attribution'] ?? null,
            'status' => 'draft',
            'series_title' => $data['series_title'] ?? null,
            'is_premium' => false,
            'allow_comments' => true,
            'moderation_status' => $moderationStatus,
            'moderated_at' => $moderationStatus === 'approved' ? now() : null,
            'moderation_notes' => 'Submitted via blog-bot (Claude Code CLI, subscription-backed)',
            'ai_moderation_check' => $moderation,
            'seo_meta' => array_filter($data['seo'] ?? []) ?: null,
            'quality_report' => $data['quality'] ?? null,
        ]);

        // Attach tags
        if (!empty($data['tags'])) {
            $tagIds = [];
            foreach ($data['tags'] as $name) {
                $name = trim($name);
                if (!$name) continue;
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'is_active' => true]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        Log::info('Bot post submitted', [
            'post_id' => $post->id,
            'title' => $post->title,
            'moderation' => $moderationStatus,
        ]);

        return response()->json([
            'ok' => true,
            'post_id' => $post->id,
            'status' => $post->status,
            'moderation_status' => $moderationStatus,
            'edit_url' => url("/posts/{$post->slug}/edit"),
        ], 201);
    }

    /**
     * Daily "AI News" roundup (short-form, post_type=news_brief).
     *
     * The sender provides structured items (headline, English summary, source
     * link); the server builds the markdown itself, so the shape is fixed.
     * Passes NewsBriefGate (structure/sources/English) and moderation with the
     * long-form length floor exempted, then publishes immediately. One post per
     * date: a repeat for the same date returns the existing post.
     */
    public function submitNews(Request $request, NewsBriefGate $gate): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature or stale timestamp'], 401);
        }
        Cache::put(self::HEARTBEAT_CACHE_KEY, now()->toIso8601String(), now()->addHours(48));

        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'author_id' => 'required|integer|exists:users,id',
            'items' => 'required|array|min:1|max:20',
            'items.*.headline' => 'required|string|max:200',
            'items.*.summary' => 'required|string|max:1200',
            'items.*.source_url' => 'required|string|max:2048',
            'items.*.source_name' => 'nullable|string|max:80',
        ]);

        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date']);
        $title = 'AI News — ' . $date->format('F j, Y');

        $existing = Post::where('post_type', Post::TYPE_NEWS_BRIEF)->where('title', $title)->first();
        if ($existing) {
            return response()->json([
                'ok' => true,
                'duplicate' => true,
                'post_id' => $existing->id,
                'status' => $existing->status,
                'url' => url('/posts/' . $existing->slug),
            ], 200);
        }

        $items = array_values($data['items']);
        $failures = $gate->itemFailures($items);
        if ($failures !== []) {
            Log::warning('AI News rejected by NewsBriefGate', ['failures' => $failures]);

            return response()->json(['ok' => false, 'error' => 'gate_failed', 'failures' => $failures], 422);
        }

        $content = "Today's AI news in short: " . count($items) . " items, each with a link to its source.\n\n";
        foreach ($items as $item) {
            $name = trim((string) ($item['source_name'] ?? ''))
                ?: (string) preg_replace('/^www\./', '', (string) parse_url($item['source_url'], PHP_URL_HOST));
            $content .= '## ' . trim($item['headline']) . "\n\n" . trim($item['summary'])
                . "\n\n[Source: {$name}](" . trim($item['source_url']) . ")\n\n";
        }
        $content .= "Summaries are condensed from the linked sources; follow each link for the full story.\n";

        $excerpt = Str::limit(implode(' | ', array_map(fn ($i) => trim($i['headline']), $items)), 300, '...');
        if (mb_strlen($excerpt) < 50) {
            $excerpt = str_pad($excerpt, 50, '.');
        }

        $moderation = app(ContentModerationService::class)->moderateContent($title, $content, $excerpt, true);
        $flags = $moderation['flags'] ?? [];
        $moderatorDown = (bool) array_intersect($flags, ['moderation_unavailable', 'moderation_rate_limited']);
        $approved = ($moderation['passed'] ?? false) && ($moderation['score'] ?? 0) >= 75;

        // A moderator OUTAGE is not a verdict: the brief already passed the
        // deterministic gate and holds only sourced items, so it publishes and
        // the outage is recorded. An actual rejection stays a draft.
        $publish = $approved || $moderatorDown;

        $category = Category::firstOrCreate(
            ['slug' => 'ai-news'],
            ['name' => 'AI News', 'description' => 'Daily short roundup of AI news with source links',
                'color' => '#6366f1', 'icon' => 'newspaper', 'is_active' => true]
        );

        $post = Post::create([
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'author_id' => $data['author_id'],
            'category_id' => $category->id,
            'post_type' => Post::TYPE_NEWS_BRIEF,
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
            'is_premium' => false,
            'allow_comments' => true,
            'moderation_status' => $approved ? 'approved' : 'pending',
            'moderated_at' => $approved ? now() : null,
            'moderation_notes' => 'Daily AI News via /api/bot/news'
                . ($moderatorDown ? ' (moderator unavailable; published on NewsBriefGate alone)' : ''),
            'ai_moderation_check' => $moderation,
            'quality_report' => ['passed' => true, 'gate' => 'news_brief', 'items' => count($items)],
        ]);

        $tagIds = [];
        foreach (['AI News', 'Artificial Intelligence'] as $name) {
            $tagIds[] = Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true])->id;
        }
        $post->tags()->sync($tagIds);

        Log::info('AI News submitted', ['post_id' => $post->id, 'status' => $post->status]);

        return response()->json([
            'ok' => true,
            'post_id' => $post->id,
            'status' => $post->status,
            'url' => $publish ? url('/posts/' . $post->slug) : null,
        ], 201);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        Cache::put(self::HEARTBEAT_CACHE_KEY, now()->toIso8601String(), now()->addHours(48));
        return response()->json(['ok' => true, 'recorded_at' => now()->toIso8601String()]);
    }

    /**
     * Serve the top of the trending-topic queue to the blog-bot, so it stops
     * picking topics from a hand-maintained YAML file.
     */
    public function nextTopic(Request $request, \App\Services\Content\TopicQueueService $queue): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $candidate = $queue->topCandidates(1)->first();

        return response()->json([
            'ok' => true,
            'topic' => $candidate ? [
                'title' => $candidate['title'],
                'category' => $candidate['category'],
                'sources' => $candidate['sources'],
            ] : null,
        ]);
    }

    /**
     * Verify HMAC-SHA256 signature: hmac(secret, "<timestamp>.<raw_body>") in hex.
     * Reject if timestamp is stale (> 5 min skew) to prevent replay attacks.
     */
    private function verifySignature(Request $request): bool
    {
        $secret = config('services.blog_bot.secret') ?: env('BOT_API_SECRET');
        if (empty($secret)) {
            Log::warning('BOT_API_SECRET not configured; rejecting bot request');
            return false;
        }

        $timestamp = $request->header('X-Bot-Timestamp');
        $signature = $request->header('X-Bot-Signature');
        if (!$timestamp || !$signature) return false;

        // Timestamp skew check
        try {
            $ts = \Carbon\Carbon::parse($timestamp);
        } catch (\Throwable) {
            return false;
        }
        if (abs(now()->diffInSeconds($ts, false)) > self::TIMESTAMP_TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);
        return hash_equals($expected, $signature);
    }
}
