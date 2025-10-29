<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModerationService
{
    /**
     * Check content for quality, appropriateness, and relevance using AI
     *
     * @param string $title
     * @param string $content
     * @param string $excerpt
     * @return array {
     *   'passed': bool,
     *   'score': int (0-100),
     *   'flags': array,
     *   'recommendations': array,
     *   'reason': string
     * }
     */
    public function moderateContent(string $title, string $content, string $excerpt): array
    {
        $apiKey = config('services.groq.api_key');

        if (!$apiKey) {
            Log::warning('Groq API key not configured for content moderation');
            // Default to manual review if AI check fails
            return [
                'passed' => false,
                'score' => 50,
                'flags' => ['ai_check_unavailable'],
                'recommendations' => ['Manual review required - AI check unavailable'],
                'reason' => 'AI moderation service unavailable',
            ];
        }

        try {
            $prompt = "You are a content moderation AI for a technical blog platform. Review this blog post for:

1. **Content Quality** (technical accuracy, depth, usefulness)
2. **Appropriateness** (no pornographic, violent, or offensive content)
3. **Relevance** (related to technology, software, or technical topics)
4. **Safety** (no hate speech, harassment, illegal content, spam)
5. **Authenticity** (real information, not completely fabricated)

POST TO REVIEW:

**Title:** {$title}

**Excerpt:** {$excerpt}

**Content:** " . substr(strip_tags($content), 0, 3000) . "

MODERATION RULES:
✅ APPROVE if:
- Technical/technology-related content
- Educational or informative
- Well-written and useful
- Safe for work (SFW)
- No harmful content

❌ FLAG/REJECT if:
- Pornographic or sexually explicit content
- Violence, gore, or disturbing content
- Hate speech or harassment
- Spam or promotional junk
- Completely irrelevant to technology
- Malicious code or security exploits without proper context
- Plagiarism or copyright infringement indicators
- Fabricated or dangerously misleading information

SCORING (0-100):
- 90-100: Excellent quality, approve immediately
- 70-89: Good quality, minor issues
- 50-69: Moderate quality, needs review
- 30-49: Low quality, likely reject
- 0-29: Very poor quality or harmful, reject

Return ONLY valid JSON:
{
  \"passed\": true/false,
  \"score\": 0-100,
  \"flags\": [\"flag1\", \"flag2\"],
  \"recommendations\": [\"suggestion1\", \"suggestion2\"],
  \"reason\": \"Brief explanation of decision\"
}

Flags can be: \"low_quality\", \"inappropriate_content\", \"off_topic\", \"spam\", \"plagiarism\", \"harmful_content\", \"explicit_content\", \"needs_review\"";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional content moderator for a technical blog. You ensure content is high-quality, appropriate, and relevant. You MUST return ONLY valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.3, // Lower temperature for consistent moderation
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                Log::error('Content moderation API failed', ['error' => $response->body()]);
                return $this->defaultPendingResult();
            }

            $content = $response->json()['choices'][0]['message']['content'];

            // Parse JSON response
            $result = json_decode($content, true);
            if (!$result && preg_match('/\{.*\}/s', $content, $matches)) {
                $result = json_decode($matches[0], true);
            }

            if (!$result || !isset($result['passed']) || !isset($result['score'])) {
                Log::error('Invalid moderation response format', ['response' => $content]);
                return $this->defaultPendingResult();
            }

            // Ensure all required fields exist
            $result['flags'] = $result['flags'] ?? [];
            $result['recommendations'] = $result['recommendations'] ?? [];
            $result['reason'] = $result['reason'] ?? 'No reason provided';

            Log::info('Content moderation completed', [
                'title' => $title,
                'passed' => $result['passed'],
                'score' => $result['score'],
                'flags' => $result['flags'],
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Content moderation exception', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);

            return $this->defaultPendingResult();
        }
    }

    /**
     * Quick check for obviously inappropriate content (pornographic keywords, etc.)
     */
    public function quickCheck(string $title, string $content): array
    {
        $flags = [];
        $text = strtolower($title . ' ' . $content);

        // Check for pornographic keywords
        $pornKeywords = ['porn', 'xxx', 'sex tape', 'nude', 'nsfw explicit', 'adult content', 'pornography'];
        foreach ($pornKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $flags[] = 'explicit_content';
                break;
            }
        }

        // Check for spam indicators
        $spamKeywords = ['click here', 'buy now', 'limited offer', 'act fast', 'guaranteed money'];
        $spamCount = 0;
        foreach ($spamKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $spamCount++;
            }
        }
        if ($spamCount >= 2) {
            $flags[] = 'potential_spam';
        }

        // Check for hate speech indicators
        $hateKeywords = ['hate', 'kill all', 'death to', 'inferior race'];
        foreach ($hateKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $flags[] = 'potential_hate_speech';
                break;
            }
        }

        return [
            'has_issues' => !empty($flags),
            'flags' => $flags,
        ];
    }

    /**
     * Default result when AI check fails - requires manual review
     */
    private function defaultPendingResult(): array
    {
        return [
            'passed' => false, // Requires manual review
            'score' => 50,
            'flags' => ['ai_check_failed'],
            'recommendations' => ['Manual moderation required'],
            'reason' => 'AI moderation check could not be completed',
        ];
    }
}
