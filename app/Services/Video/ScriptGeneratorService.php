<?php

namespace App\Services\Video;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Exception;

class ScriptGeneratorService
{
    /**
     * Generate a video script from a blog post
     *
     * @param Post $post
     * @param string $type Video type (youtube, tiktok, reel, short)
     * @return array ['text' => string, 'timestamps' => array]
     */
    public function generateScript(Post $post, string $type): array
    {
        $targetDuration = $this->getTargetDuration($type);
        $wordsPerSecond = 2.5; // Average speaking pace
        $targetWords = (int)($targetDuration * $wordsPerSecond);

        // Build prompt based on video type
        $prompt = $this->buildPrompt($post, $type, $targetWords);

        // Use GPT-4 to generate script
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new Exception('OpenAI API key not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4-turbo-preview',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt($type),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => $this->getMaxTokens($type),
        ]);

        if (!$response->successful()) {
            throw new Exception('Script generation failed: ' . $response->body());
        }

        $data = $response->json();
        $scriptText = $data['choices'][0]['message']['content'] ?? '';

        // Generate timestamps for each sentence
        $timestamps = $this->generateTimestamps($scriptText, $targetDuration);

        return [
            'text' => $scriptText,
            'timestamps' => $timestamps,
        ];
    }

    /**
     * Get target duration based on video type
     */
    protected function getTargetDuration(string $type): int
    {
        return match($type) {
            'youtube' => 600,  // 10 minutes
            'tiktok' => 60,    // 60 seconds
            'reel' => 90,      // 90 seconds
            'short' => 60,     // 60 seconds
            default => 60,
        };
    }

    /**
     * Build prompt for script generation
     */
    protected function buildPrompt(Post $post, string $type, int $targetWords): string
    {
        $content = strip_tags($post->content);
        $content = substr($content, 0, 3000); // Limit to avoid token limits

        $styleGuide = match($type) {
            'youtube' => 'detailed tutorial style with clear sections and examples',
            'tiktok' => 'fast-paced, attention-grabbing, with a hook in the first 3 seconds',
            'reel' => 'engaging and visually descriptive, perfect for short-form video',
            'short' => 'quick tips format, punchy and to-the-point',
            default => 'engaging and conversational',
        };

        return <<<PROMPT
Convert this blog post into a video script for {$type}.

**Blog Post Title:** {$post->title}
**Blog Post Content:**
{$content}

**Requirements:**
- Target length: approximately {$targetWords} words
- Style: {$styleGuide}
- Include a strong hook in the first sentence
- Use natural, conversational language
- Break into clear sections/points
- End with a call-to-action
- DO NOT include camera directions or visual cues
- Just write the voiceover script

Write the complete voiceover script now:
PROMPT;
    }

    /**
     * Get system prompt based on video type
     */
    protected function getSystemPrompt(string $type): string
    {
        return match($type) {
            'youtube' => 'You are a professional YouTube tutorial creator. Write engaging, educational scripts that keep viewers watching.',
            'tiktok' => 'You are a viral TikTok content creator. Write punchy, fast-paced scripts that grab attention immediately.',
            'reel' => 'You are an Instagram Reels expert. Write visually engaging scripts perfect for short-form vertical video.',
            'short' => 'You are a YouTube Shorts creator. Write quick, valuable tips in under 60 seconds.',
            default => 'You are a professional video scriptwriter. Write engaging, clear scripts for social media videos.',
        };
    }

    /**
     * Get max tokens based on video type
     */
    protected function getMaxTokens(string $type): int
    {
        return match($type) {
            'youtube' => 2000,  // ~10 minutes
            'tiktok' => 300,    // ~60 seconds
            'reel' => 400,      // ~90 seconds
            'short' => 300,     // ~60 seconds
            default => 300,
        };
    }

    /**
     * Generate timestamps for each sentence
     */
    protected function generateTimestamps(string $script, int $totalDuration): array
    {
        // Split script into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($script), -1, PREG_SPLIT_NO_EMPTY);

        $timestamps = [];
        $wordsPerSecond = 2.5;
        $currentTime = 0;

        foreach ($sentences as $sentence) {
            $wordCount = str_word_count($sentence);
            $duration = $wordCount / $wordsPerSecond;

            $timestamps[] = [
                'start' => round($currentTime, 2),
                'end' => round($currentTime + $duration, 2),
                'text' => trim($sentence),
            ];

            $currentTime += $duration;
        }

        // Normalize to fit total duration
        if ($currentTime > 0) {
            $scaleFactor = $totalDuration / $currentTime;
            foreach ($timestamps as &$timestamp) {
                $timestamp['start'] = round($timestamp['start'] * $scaleFactor, 2);
                $timestamp['end'] = round($timestamp['end'] * $scaleFactor, 2);
            }
        }

        return $timestamps;
    }

    /**
     * Extract key points from script (for visual selection)
     */
    public function extractKeyPoints(string $script): array
    {
        // Extract nouns and key phrases for stock footage search
        $sentences = preg_split('/(?<=[.!?])\s+/', $script);
        $keyPoints = [];

        foreach ($sentences as $sentence) {
            // Simple extraction - get first few words of each sentence
            $words = explode(' ', $sentence);
            $keywords = array_slice($words, 0, 5);
            $keyPoints[] = implode(' ', $keywords);
        }

        return $keyPoints;
    }
}
