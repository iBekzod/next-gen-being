<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
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
            'is_premium' => false,
            'allow_comments' => true,
            'moderation_status' => $moderationStatus,
            'moderated_at' => $moderationStatus === 'approved' ? now() : null,
            'moderation_notes' => 'Submitted via blog-bot (Claude Code CLI, subscription-backed)',
            'ai_moderation_check' => $moderation,
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

    public function heartbeat(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        Cache::put(self::HEARTBEAT_CACHE_KEY, now()->toIso8601String(), now()->addHours(48));
        return response()->json(['ok' => true, 'recorded_at' => now()->toIso8601String()]);
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
