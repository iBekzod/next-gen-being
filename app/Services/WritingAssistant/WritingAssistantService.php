<?php

namespace App\Services\WritingAssistant;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WritingAssistantService
{
    private $openaiApiKey;
    private $useApiCalls = true;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        // Disable API calls if key is not configured or in testing
        $this->useApiCalls = !empty($this->openaiApiKey) && app()->environment() !== 'testing';
    }

    /**
     * Improve text quality with AI-powered analysis
     */
    public function improveText(string $text): array
    {
        try {
            if (strlen($text) < 10) {
                return [
                    'error' => 'Text is too short for analysis',
                    'original' => $text,
                ];
            }

            $improvements = [
                'original' => $text,
                'suggestions' => [],
                'score' => 0,
            ];

            // Grammar and spelling - AI-powered
            $improvements['grammar'] = $this->checkGrammarWithAI($text);

            // Style improvements - AI-powered
            $improvements['style'] = $this->suggestStyleImprovementsWithAI($text);

            // Readability analysis (local calculation)
            $improvements['readability'] = $this->analyzeReadability($text);

            // Tone analysis - AI-powered
            $improvements['tone'] = $this->analyzeToneWithAI($text);

            // Calculate overall quality score
            $improvements['score'] = $this->calculateQualityScore($improvements);

            return $improvements;

        } catch (Exception $e) {
            Log::error("Writing assistant error: {$e->getMessage()}");
            // Fallback to local analysis on API error
            return $this->improveTextLocal($text);
        }
    }

    /**
     * Fallback to local analysis
     */
    private function improveTextLocal(string $text): array
    {
        return [
            'original' => $text,
            'grammar' => $this->checkGrammar($text),
            'style' => $this->suggestStyleImprovements($text),
            'readability' => $this->analyzeReadability($text),
            'tone' => $this->analyzeTone($text),
            'score' => 75,
            'note' => 'Using local analysis (API unavailable)',
        ];
    }

    /**
     * Check grammar with AI API
     */
    private function checkGrammarWithAI(string $text): array
    {
        if (!$this->useApiCalls) {
            return $this->checkGrammar($text);
        }

        try {
            $response = Http::withToken($this->openaiApiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a grammar and spelling expert. Analyze the following text and provide a JSON response with: grammar_score (0-100), issues_found (count), and issues (array of objects with type, text, suggestion, and severity).'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze this text for grammar and spelling issues:\n\n{$text}"
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 500
                ])
                ->timeout(10);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '{}');

                // Try to parse JSON from response
                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    $parsed = json_decode($matches[0], true);
                    if (is_array($parsed)) {
                        return $parsed + ['api_used' => true];
                    }
                }
            }

            // Fallback to local analysis
            return $this->checkGrammar($text);
        } catch (Exception $e) {
            Log::warning("Grammar API error: {$e->getMessage()}");
            return $this->checkGrammar($text);
        }
    }

    /**
     * Suggest style improvements with AI
     */
    private function suggestStyleImprovementsWithAI(string $text): array
    {
        if (!$this->useApiCalls) {
            return $this->suggestStyleImprovements($text);
        }

        try {
            $response = Http::withToken($this->openaiApiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a writing style expert. Analyze the following text and provide a JSON response with: style_score (0-100), suggestions_count, and suggestions (array of objects with type, recommendation, and severity).'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze this text for style improvements:\n\n{$text}"
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 500
                ])
                ->timeout(10);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '{}');

                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    $parsed = json_decode($matches[0], true);
                    if (is_array($parsed)) {
                        return $parsed + ['api_used' => true];
                    }
                }
            }

            return $this->suggestStyleImprovements($text);
        } catch (Exception $e) {
            Log::warning("Style API error: {$e->getMessage()}");
            return $this->suggestStyleImprovements($text);
        }
    }

    /**
     * Analyze tone with AI
     */
    private function analyzeToneWithAI(string $text): array
    {
        if (!$this->useApiCalls) {
            return $this->analyzeTone($text);
        }

        try {
            $response = Http::withToken($this->openaiApiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a writing tone analyst. Analyze the following text and provide a JSON response with: scores (object with formal, positive, negative, confident scores 0-100), dominant_tone (string), and analysis (string).'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze the tone of this text:\n\n{$text}"
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 300
                ])
                ->timeout(10);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '{}');

                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    $parsed = json_decode($matches[0], true);
                    if (is_array($parsed)) {
                        return $parsed + ['api_used' => true];
                    }
                }
            }

            return $this->analyzeTone($text);
        } catch (Exception $e) {
            Log::warning("Tone API error: {$e->getMessage()}");
            return $this->analyzeTone($text);
        }
    }

    /**
     * Generate content suggestions
     */
    public function generateContentSuggestions(string $topic, string $tone = 'professional'): array
    {
        try {
            return [
                'topic' => $topic,
                'tone' => $tone,
                'outlines' => $this->generateOutlines($topic),
                'headline_suggestions' => $this->generateHeadlines($topic),
                'introduction_templates' => $this->generateIntroductions($topic),
                'keywords' => $this->extractKeywords($topic),
            ];

        } catch (Exception $e) {
            Log::error("Content generation error: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check grammar and spelling
     */
    private function checkGrammar(string $text): array
    {
        $issues = [];
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;

            // Basic grammar checks
            if (!preg_match('/[A-Z]/', $sentence[0])) {
                $issues[] = [
                    'type' => 'capitalization',
                    'text' => $sentence,
                    'suggestion' => ucfirst($sentence),
                    'severity' => 'low',
                ];
            }

            // Check for common mistakes
            if (preg_match('/\b(their|there|they\'re)\b/i', $sentence, $matches)) {
                $issues[] = [
                    'type' => 'homophone',
                    'text' => $matches[0],
                    'severity' => 'high',
                ];
            }
        }

        return [
            'issues_found' => count($issues),
            'issues' => $issues,
            'grammar_score' => max(0, 100 - (count($issues) * 10)),
        ];
    }

    /**
     * Suggest style improvements
     */
    private function suggestStyleImprovements(string $text): array
    {
        $suggestions = [];
        $wordCount = str_word_count($text);

        // Check for passive voice
        $passivePattern = '/\b(is|are|was|were)\s+\w+ed\b/i';
        if (preg_match_all($passivePattern, $text, $matches)) {
            $suggestions[] = [
                'type' => 'passive_voice',
                'count' => count($matches[0]),
                'recommendation' => 'Consider using active voice for stronger writing',
                'severity' => 'low',
            ];
        }

        // Check for complex sentences
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $longSentences = array_filter($sentences, fn ($s) => str_word_count(trim($s)) > 20);

        if (count($longSentences) > 0) {
            $suggestions[] = [
                'type' => 'sentence_length',
                'count' => count($longSentences),
                'recommendation' => 'Break up long sentences for better readability',
                'severity' => 'medium',
            ];
        }

        // Check for repeated words
        $words = array_map('strtolower', str_word_count($text, 1));
        $wordFrequency = array_count_values($words);
        $repeatedWords = array_filter($wordFrequency, fn ($count) => $count > 3);

        if (count($repeatedWords) > 0) {
            $suggestions[] = [
                'type' => 'repetition',
                'words' => array_keys($repeatedWords),
                'recommendation' => 'Vary your vocabulary to avoid repetition',
                'severity' => 'low',
            ];
        }

        return [
            'suggestions_count' => count($suggestions),
            'suggestions' => $suggestions,
            'style_score' => max(0, 100 - (count($suggestions) * 15)),
        ];
    }

    /**
     * Analyze readability
     */
    private function analyzeReadability(string $text): array
    {
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text);
        $characters = strlen($text);

        // Calculate Flesch Reading Ease
        $syllables = $this->countSyllables($text);
        $fleshScore = 206.835 - (1.015 * ($words / max(1, count($sentences)))) - (84.6 * ($syllables / max(1, $words)));
        $fleshScore = max(0, min(100, $fleshScore));

        // Determine reading level
        $readingLevel = $this->getReadingLevel($fleshScore);

        return [
            'word_count' => $words,
            'sentence_count' => count(array_filter($sentences, fn ($s) => !empty(trim($s)))),
            'average_sentence_length' => count($sentences) > 0 ? round($words / count($sentences), 1) : 0,
            'average_word_length' => $words > 0 ? round($characters / $words, 1) : 0,
            'flesch_score' => round($fleshScore, 1),
            'reading_level' => $readingLevel,
            'recommendation' => $this->getReadabilityRecommendation($readingLevel),
        ];
    }

    /**
     * Analyze tone
     */
    private function analyzeTone(string $text): array
    {
        $tones = [
            'formal' => $this->calculateFormalityScore($text),
            'positive' => $this->calculateSentimentScore($text, 'positive'),
            'negative' => $this->calculateSentimentScore($text, 'negative'),
            'confident' => $this->calculateConfidenceScore($text),
        ];

        $dominantTone = array_keys($tones, max($tones))[0];

        return [
            'scores' => $tones,
            'dominant_tone' => $dominantTone,
            'analysis' => "Your writing has a predominantly {$dominantTone} tone.",
        ];
    }

    /**
     * Generate content outlines
     */
    private function generateOutlines(string $topic): array
    {
        return [
            [
                'title' => 'Problem-Solution Structure',
                'sections' => [
                    'Introduction & Problem Overview',
                    'Why This Problem Matters',
                    'Proposed Solutions',
                    'Implementation Steps',
                    'Conclusion & Call to Action',
                ],
            ],
            [
                'title' => 'Chronological Structure',
                'sections' => [
                    'Historical Background',
                    'Current Situation',
                    'Recent Developments',
                    'Future Outlook',
                    'Conclusion',
                ],
            ],
            [
                'title' => 'How-To Structure',
                'sections' => [
                    'Introduction & Why This Matters',
                    'Materials/Prerequisites',
                    'Step-by-Step Instructions',
                    'Tips & Tricks',
                    'Conclusion',
                ],
            ],
        ];
    }

    /**
     * Generate headline suggestions
     */
    private function generateHeadlines(string $topic): array
    {
        return [
            "The Complete Guide to {$topic}",
            "Everything You Need to Know About {$topic}",
            "{$topic}: A Comprehensive Overview",
            "Why {$topic} Matters in 2025",
            "How to Master {$topic}: Expert Tips",
            "The Future of {$topic}: Trends & Predictions",
            "{$topic} Explained: A Beginner's Guide",
            "Common {$topic} Mistakes and How to Avoid Them",
        ];
    }

    /**
     * Generate introduction templates
     */
    private function generateIntroductions(string $topic): array
    {
        return [
            "Have you ever wondered about {$topic}? You're not alone. Millions of people...",
            "{$topic} is one of the most important topics in today's world. In this guide, we'll explore...",
            "Did you know that {$topic} affects nearly every aspect of our lives? Let's dive into...",
            "Whether you're new to {$topic} or an experienced enthusiast, this guide will help you...",
            "The landscape of {$topic} has changed dramatically over the past few years. Here's what you need to know...",
        ];
    }

    /**
     * Extract keywords from text
     */
    private function extractKeywords(string $text): array
    {
        $words = str_word_count(strtolower($text), 1);
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being'];

        $keywords = array_filter($words, function ($word) use ($stopwords) {
            return strlen($word) > 3 && !in_array($word, $stopwords);
        });

        $frequency = array_count_values($keywords);
        arsort($frequency);

        return array_slice(array_keys($frequency), 0, 10);
    }

    /**
     * Count syllables in text
     */
    private function countSyllables(string $text): int
    {
        $syllableCount = 0;
        $words = str_word_count($text, 1);

        foreach ($words as $word) {
            $syllableCount += max(1, preg_match_all('/[aeiou]/i', $word));
        }

        return $syllableCount;
    }

    /**
     * Get reading level description
     */
    private function getReadingLevel(float $score): string
    {
        return match (true) {
            $score >= 90 => '5th Grade',
            $score >= 80 => '6th Grade',
            $score >= 70 => '7th Grade',
            $score >= 60 => '8th-9th Grade',
            $score >= 50 => '10th-12th Grade',
            $score >= 30 => 'College',
            default => 'Post-Graduate',
        };
    }

    /**
     * Get readability recommendation
     */
    private function getReadabilityRecommendation(string $level): string
    {
        return match ($level) {
            '5th Grade', '6th Grade', '7th Grade' => 'Excellent! Your writing is very easy to read.',
            '8th-9th Grade', '10th-12th Grade' => 'Good readability. Most readers will understand your content.',
            'College' => 'Your writing is complex. Consider simplifying for broader audience.',
            default => 'Very complex. Strongly consider breaking down sentences and using simpler vocabulary.',
        };
    }

    /**
     * Calculate formality score
     */
    private function calculateFormalityScore(string $text): int
    {
        $formalIndicators = ['therefore', 'furthermore', 'however', 'thus', 'consequently', 'aforementioned'];
        $informalIndicators = ['gonna', 'wanna', 'cool', 'awesome', 'hey', 'lol'];

        $formalCount = 0;
        $informalCount = 0;

        foreach ($formalIndicators as $word) {
            $formalCount += substr_count(strtolower($text), $word);
        }

        foreach ($informalIndicators as $word) {
            $informalCount += substr_count(strtolower($text), $word);
        }

        if ($formalCount + $informalCount === 0) {
            return 50; // Neutral
        }

        return (int) (($formalCount / ($formalCount + $informalCount)) * 100);
    }

    /**
     * Calculate sentiment score
     */
    private function calculateSentimentScore(string $text, string $type): int
    {
        $positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'love', 'best'];
        $negativeWords = ['bad', 'terrible', 'horrible', 'awful', 'poor', 'worst', 'hate', 'negative', 'sad'];

        $words = str_word_count(strtolower($text), 1);
        $count = 0;

        if ($type === 'positive') {
            foreach ($positiveWords as $word) {
                $count += substr_count(strtolower($text), $word);
            }
        } else {
            foreach ($negativeWords as $word) {
                $count += substr_count(strtolower($text), $word);
            }
        }

        return min(100, $count * 5);
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidenceScore(string $text): int
    {
        $confidenceWords = ['definitely', 'certainly', 'absolutely', 'proven', 'guaranteed', 'will', 'must'];
        $uncertaintyWords = ['maybe', 'possibly', 'might', 'could', 'perhaps', 'seems', 'appears'];

        $confidenceCount = 0;
        $uncertaintyCount = 0;

        foreach ($confidenceWords as $word) {
            $confidenceCount += substr_count(strtolower($text), $word);
        }

        foreach ($uncertaintyWords as $word) {
            $uncertaintyCount += substr_count(strtolower($text), $word);
        }

        if ($confidenceCount + $uncertaintyCount === 0) {
            return 50;
        }

        return (int) (($confidenceCount / ($confidenceCount + $uncertaintyCount)) * 100);
    }

    /**
     * Calculate overall quality score
     */
    private function calculateQualityScore(array $analysis): int
    {
        $grammarScore = $analysis['grammar']['grammar_score'] ?? 0;
        $styleScore = $analysis['style']['style_score'] ?? 0;
        $readabilityScore = $analysis['readability']['flesch_score'] ?? 0;

        return (int) (($grammarScore * 0.4 + $styleScore * 0.3 + $readabilityScore * 0.3));
    }
}