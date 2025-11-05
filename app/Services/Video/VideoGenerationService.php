<?php

namespace App\Services\Video;

use App\Models\Post;
use App\Models\User;
use App\Models\VideoGeneration;
use App\Services\Video\ScriptGeneratorService;
use App\Services\Video\VoiceoverService;
use App\Services\Video\StockFootageService;
use App\Services\Video\CaptionGeneratorService;
use App\Services\Video\VideoEditorService;
use Illuminate\Support\Facades\Log;
use Exception;

class VideoGenerationService
{
    protected ScriptGeneratorService $scriptGenerator;
    protected VoiceoverService $voiceoverService;
    protected StockFootageService $stockFootageService;
    protected CaptionGeneratorService $captionGenerator;
    protected VideoEditorService $videoEditor;

    public function __construct(
        ScriptGeneratorService $scriptGenerator,
        VoiceoverService $voiceoverService,
        StockFootageService $stockFootageService,
        CaptionGeneratorService $captionGenerator,
        VideoEditorService $videoEditor
    ) {
        $this->scriptGenerator = $scriptGenerator;
        $this->voiceoverService = $voiceoverService;
        $this->stockFootageService = $stockFootageService;
        $this->captionGenerator = $captionGenerator;
        $this->videoEditor = $videoEditor;
    }

    /**
     * Generate a video from a blog post
     *
     * @param Post $post The blog post to convert
     * @param string $type Video type: 'youtube', 'tiktok', 'reel', 'short'
     * @return VideoGeneration
     */
    public function generateFromPost(Post $post, string $type = 'tiktok'): VideoGeneration
    {
        $user = $post->author;

        // Check if user can generate videos
        if (!$user->canGenerateVideo()) {
            throw new Exception('You have reached your video generation limit. Please upgrade to Video Pro.');
        }

        // Create video generation record
        $videoGeneration = VideoGeneration::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'video_type' => $type,
            'status' => 'queued',
        ]);

        try {
            // Mark as processing
            $videoGeneration->markAsProcessing();

            // Step 1: Generate script from blog post
            Log::info("Generating script for post {$post->id}");
            $script = $this->scriptGenerator->generateScript($post, $type);
            $videoGeneration->update(['script' => $script['text']]);

            // Step 2: Calculate target duration and resolution
            $specs = $this->getVideoSpecs($type);
            $videoGeneration->update([
                'duration_seconds' => $specs['duration'],
                'resolution' => $specs['resolution'],
            ]);

            // Step 3: Generate voiceover from script
            Log::info("Generating voiceover for post {$post->id}");
            $voiceoverUrl = $this->voiceoverService->generateVoiceover(
                $script['text'],
                $user,
                $type
            );
            $videoGeneration->update(['voiceover_url' => $voiceoverUrl]);

            // Step 4: Fetch stock footage based on post content
            Log::info("Fetching stock footage for post {$post->id}");
            $videoClips = $this->stockFootageService->fetchFootage(
                $post,
                $specs['duration']
            );
            $videoGeneration->update(['video_clips' => $videoClips]);

            // Step 5: Generate captions/subtitles
            Log::info("Generating captions for post {$post->id}");
            $captionsUrl = $this->captionGenerator->generateCaptions(
                $script['timestamps'],
                $specs['duration']
            );
            $videoGeneration->update(['captions_url' => $captionsUrl]);

            // Step 6: Combine everything into final video
            Log::info("Combining video elements for post {$post->id}");
            $finalVideo = $this->videoEditor->combineVideo([
                'clips' => $videoClips,
                'voiceover' => $voiceoverUrl,
                'captions' => $captionsUrl,
                'duration' => $specs['duration'],
                'resolution' => $specs['resolution'],
                'user' => $user,
                'post' => $post,
            ]);

            // Mark as completed
            $videoGeneration->markAsCompleted(
                $finalVideo['video_url'],
                $finalVideo['thumbnail_url'],
                $finalVideo['file_size_mb']
            );

            // Update user's video generation count
            $user->increment('videos_generated');

            // Calculate cost (for tracking)
            $cost = $this->calculateCost($type, $specs['duration']);
            $videoGeneration->update([
                'generation_cost' => $cost,
                'ai_credits_used' => 1,
            ]);

            Log::info("Video generation completed for post {$post->id}");

            return $videoGeneration;

        } catch (Exception $e) {
            Log::error("Video generation failed for post {$post->id}: " . $e->getMessage());
            $videoGeneration->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get video specifications based on type
     */
    protected function getVideoSpecs(string $type): array
    {
        return match($type) {
            'youtube' => [
                'duration' => 600, // 10 minutes
                'resolution' => '1920x1080',
                'format' => 'mp4',
            ],
            'tiktok' => [
                'duration' => 60, // 60 seconds
                'resolution' => '1080x1920',
                'format' => 'mp4',
            ],
            'reel' => [
                'duration' => 90, // 90 seconds
                'resolution' => '1080x1920',
                'format' => 'mp4',
            ],
            'short' => [
                'duration' => 60, // 60 seconds
                'resolution' => '1080x1920',
                'format' => 'mp4',
            ],
            default => [
                'duration' => 60,
                'resolution' => '1080x1920',
                'format' => 'mp4',
            ],
        };
    }

    /**
     * Calculate generation cost
     */
    protected function calculateCost(string $type, int $duration): float
    {
        // Base costs:
        // - OpenAI TTS: $0.015 per minute
        // - Pexels: FREE
        // - FFmpeg: FREE
        // - Storage: ~$0.002 per 100MB

        $minutes = $duration / 60;
        $ttsCost = $minutes * 0.015;
        $storageCost = 0.002; // Estimated

        return round($ttsCost + $storageCost, 2);
    }

    /**
     * Check if user can generate a specific video type
     */
    public function canGenerateType(User $user, string $type): bool
    {
        // Free tier cannot generate any videos
        if ($user->video_tier === 'free') {
            return false;
        }

        // Video Pro can generate all types
        if ($user->video_tier === 'video_pro') {
            return true;
        }

        return false;
    }

    /**
     * Get estimated generation time
     */
    public function getEstimatedTime(string $type): int
    {
        return match($type) {
            'youtube' => 300, // 5 minutes
            'tiktok' => 120, // 2 minutes
            'reel' => 150, // 2.5 minutes
            'short' => 120, // 2 minutes
            default => 120,
        };
    }

    /**
     * Regenerate video with different settings
     */
    public function regenerate(VideoGeneration $video, array $options = []): VideoGeneration
    {
        // Create new video generation based on existing one
        return $this->generateFromPost(
            $video->post,
            $options['type'] ?? $video->video_type
        );
    }
}
