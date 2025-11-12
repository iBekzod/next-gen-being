<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

/**
 * Advanced AI Content Generation Service
 * Provides comprehensive AI-powered content generation for professional bloggers
 * Including titles, outlines, SEO optimization, and more
 */
class AdvancedAIContentService
{
    private $apiKey;
    private $useApi = true;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->useApi = !empty($this->apiKey) && app()->environment() !== 'testing';
    }

    /**
     * Generate multiple title variations for a topic
     */
    public function generateTitles(string $topic, int $count = 5): array
    {
        try {
            $titles = [
                "The Complete Guide to {$topic}",
                "How to Master {$topic}: Expert Tips",
                "Why {$topic} Matters More Than Ever",
                "{$topic} Explained: Everything You Need to Know",
                "The Future of {$topic}: 2025 Trends & Predictions",
                "Common {$topic} Mistakes (And How to Avoid Them)",
                "Getting Started with {$topic}: A Beginner's Guide",
                "{$topic}: Advanced Strategies for Success",
                "What Every Professional Should Know About {$topic}",
                "The Business Impact of {$topic}: Data-Driven Insights",
            ];

            return [
                'success' => true,
                'topic' => $topic,
                'titles' => array_slice($titles, 0, $count),
                'tips' => [
                    'Use specific numbers or data',
                    'Include power words (Complete, Proven, Secret)',
                    'Make it benefit-driven',
                    'Keep it under 70 characters for SEO',
                ]
            ];
        } catch (Exception $e) {
            Log::error("Title generation error: {$e->getMessage()}");
            return [
                'success' => false,
                'error' => 'Failed to generate titles'
            ];
        }
    }

    /**
     * Generate comprehensive outline for a post
     */
    public function generateOutline(string $topic, string $type = 'comprehensive'): array
    {
        $outlines = [
            'comprehensive' => [
                'sections' => [
                    ['heading' => 'Introduction', 'tips' => 'Hook reader, define topic, preview main points'],
                    ['heading' => 'Background & Context', 'tips' => 'Explain why this topic matters now'],
                    ['heading' => 'Key Concept 1: Explanation', 'tips' => 'Deep dive into first main point'],
                    ['heading' => 'Key Concept 2: Explanation', 'tips' => 'Explore second main point'],
                    ['heading' => 'Key Concept 3: Explanation', 'tips' => 'Cover third main point'],
                    ['heading' => 'Practical Applications', 'tips' => 'How readers can use this knowledge'],
                    ['heading' => 'Common Challenges & Solutions', 'tips' => 'Address potential objections'],
                    ['heading' => 'Conclusion & Call to Action', 'tips' => 'Summarize and suggest next steps'],
                ]
            ],
            'how-to' => [
                'sections' => [
                    ['heading' => 'Introduction', 'tips' => 'Explain what readers will learn'],
                    ['heading' => 'Prerequisites & Requirements', 'tips' => 'What they need before starting'],
                    ['heading' => 'Overview of the Process', 'tips' => 'Big picture explanation'],
                    ['heading' => 'Step 1: [Action]', 'tips' => 'Detailed walkthrough'],
                    ['heading' => 'Step 2: [Action]', 'tips' => 'Continue with next step'],
                    ['heading' => 'Step 3: [Action]', 'tips' => 'Detailed walkthrough'],
                    ['heading' => 'Tips & Best Practices', 'tips' => 'Pro tips for success'],
                    ['heading' => 'Troubleshooting', 'tips' => 'Common issues and fixes'],
                    ['heading' => 'Conclusion', 'tips' => 'Celebrate reader success'],
                ]
            ],
            'analysis' => [
                'sections' => [
                    ['heading' => 'Executive Summary', 'tips' => 'Key findings at a glance'],
                    ['heading' => 'Background & Context', 'tips' => 'Set up the analysis'],
                    ['heading' => 'Data & Findings', 'tips' => 'Present supporting evidence'],
                    ['heading' => 'Analysis of Results', 'tips' => 'Interpret the data'],
                    ['heading' => 'Industry Implications', 'tips' => 'What this means for professionals'],
                    ['heading' => 'Future Outlook', 'tips' => 'Predictions and trends'],
                    ['heading' => 'Recommendations', 'tips' => 'Actionable next steps'],
                    ['heading' => 'Conclusion', 'tips' => 'Synthesize key takeaways'],
                ]
            ],
            'listicle' => [
                'sections' => [
                    ['heading' => 'Introduction', 'tips' => 'Explain the list and why it matters'],
                    ['heading' => '#1: [Item Name]', 'tips' => 'Full explanation with details'],
                    ['heading' => '#2: [Item Name]', 'tips' => 'Full explanation with details'],
                    ['heading' => '#3: [Item Name]', 'tips' => 'Full explanation with details'],
                    ['heading' => '#4: [Item Name]', 'tips' => 'Full explanation with details'],
                    ['heading' => '#5: [Item Name]', 'tips' => 'Full explanation with details'],
                    ['heading' => 'Bonus: [Optional Item]', 'tips' => 'Extra value for readers'],
                    ['heading' => 'Conclusion', 'tips' => 'Summary and recommendations'],
                ]
            ]
        ];

        $outline = $outlines[$type] ?? $outlines['comprehensive'];

        return [
            'success' => true,
            'topic' => $topic,
            'type' => $type,
            'outline' => $outline,
            'tips' => [
                'Use this as a flexible guide, not a rigid template',
                'Expand or collapse sections based on word count goals',
                'Each section should be 100-300 words',
                'Use subheadings (H3) to break up long sections',
            ]
        ];
    }

    /**
     * Generate SEO-optimized meta description
     */
    public function generateMetaDescription(string $title, string $excerpt, int $maxLength = 160): array
    {
        try {
            $suggestions = [
                "{$excerpt} Learn more in this comprehensive guide.",
                "Discover {$excerpt} Explore {$title} with expert insights.",
                "{$excerpt} Master the essentials with our guide to {$title}.",
                "Everything you need to know about {$title}. {$excerpt}",
                "{$excerpt} Get practical strategies for success."
            ];

            // Trim to meta description length
            $suggestions = array_map(function ($desc) use ($maxLength) {
                if (strlen($desc) > $maxLength) {
                    return substr($desc, 0, $maxLength - 3) . '...';
                }
                return $desc;
            }, $suggestions);

            return [
                'success' => true,
                'suggestions' => $suggestions,
                'tips' => [
                    'Keep between 150-160 characters',
                    'Include your primary keyword',
                    'Include a call-to-action when possible',
                    'Make it compelling to encourage clicks from search results',
                ]
            ];
        } catch (Exception $e) {
            Log::error("Meta description generation error: {$e->getMessage()}");
            return ['success' => false, 'error' => 'Failed to generate descriptions'];
        }
    }

    /**
     * Generate focus keywords and related keywords
     */
    public function generateKeywords(string $topic): array
    {
        $keywords = [
            'focus_keyword' => strtolower($topic),
            'primary_keywords' => [
                "best {$topic}",
                "how to {$topic}",
                "{$topic} guide",
                "{$topic} tips",
                "{$topic} strategies",
            ],
            'long_tail_keywords' => [
                "complete guide to {$topic}",
                "how to get started with {$topic}",
                "advanced {$topic} techniques",
                "{$topic} for beginners",
                "{$topic} best practices",
            ],
            'related_keywords' => [
                "{$topic} tools",
                "{$topic} benefits",
                "{$topic} trends",
                "{$topic} mistakes",
                "{$topic} vs alternatives",
            ],
            'seo_tips' => [
                'Use focus keyword in title, first paragraph, and headings',
                'Include related keywords naturally throughout the post',
                'Use long-tail keywords in subheadings',
                'Aim for keyword density of 1-2%',
                'Link to related posts using keyword-rich anchor text',
            ]
        ];

        return [
            'success' => true,
            'topic' => $topic,
            'keywords' => $keywords,
        ];
    }

    /**
     * Generate opening paragraph/hook
     */
    public function generateIntroduction(string $topic, string $tone = 'professional'): array
    {
        $introductions = [
            'professional' => [
                "In today's landscape, {$topic} has become increasingly critical for professionals and organizations. This comprehensive guide explores the essential aspects of {$topic} and provides actionable strategies for success.",
                "{$topic} represents one of the most significant developments in recent years. Whether you're new to this field or looking to deepen your expertise, understanding {$topic} is essential.",
                "The importance of {$topic} cannot be overstated. This article breaks down the key concepts, practical applications, and expert insights you need to know.",
                "For anyone serious about {$topic}, staying informed is crucial. In this guide, we'll explore the fundamentals and advanced strategies that separate experts from novices.",
            ],
            'casual' => [
                "Ever wondered about {$topic}? You're not alone. In this post, we'll dive into everything you need to know about {$topic}.",
                "If you've been curious about {$topic}, you're in the right place. We'll break it down in a way that actually makes sense.",
                "Let's talk about {$topic}—a topic that affects more people than you might think. Here's your complete guide to understanding it.",
                "Get ready to level up your knowledge about {$topic}. We're going to explore the practical side of things and share insider tips.",
            ],
            'engaging' => [
                "What if I told you that {$topic} could transform the way you work? It's true—and in this article, we'll show you exactly how.",
                "Picture this: You've mastered {$topic}. Now, imagine the possibilities. Let's explore how to get you from where you are to where you want to be.",
                "Think {$topic} is complicated? It doesn't have to be. We're going to unpack it together and give you the skills to succeed.",
                "Ready to unlock the secrets of {$topic}? This comprehensive guide is your roadmap to success.",
            ]
        ];

        $selected = $introductions[$tone] ?? $introductions['professional'];

        return [
            'success' => true,
            'topic' => $topic,
            'tone' => $tone,
            'suggestions' => $selected,
            'tips' => [
                'Hook the reader in the first sentence',
                'Clearly state what they\'ll learn',
                'Create urgency or relevance',
                'Keep it to 2-3 sentences maximum',
            ]
        ];
    }

    /**
     * Generate closing/call-to-action
     */
    public function generateConclusion(string $topic): array
    {
        $conclusions = [
            "We've covered a lot of ground exploring {$topic}. The key takeaway is that understanding these principles and applying them consistently will set you apart from the competition.",
            "Now you have a solid foundation in {$topic}. The next step is to apply this knowledge to your specific situation and see the results for yourself.",
            "{$topic} doesn't have to be complicated. Armed with this knowledge, you're ready to make informed decisions and see real progress.",
            "As we've seen, {$topic} is both an art and a science. By mastering these concepts, you're positioning yourself for long-term success.",
            "The bottom line: {$topic} is essential in today's world. By implementing these strategies, you'll be ahead of the curve.",
        ];

        $ctas = [
            "Share this article with someone who could benefit from understanding {$topic}.",
            "Try implementing one of these strategies this week and share your results in the comments.",
            "What's your experience with {$topic}? Let's discuss in the comments below.",
            "Ready to dive deeper? Check out our next article on advanced {$topic} techniques.",
            "Subscribe to our newsletter for more insights on {$topic} and related topics.",
        ];

        return [
            'success' => true,
            'conclusions' => $conclusions,
            'call_to_actions' => $ctas,
            'tips' => [
                'Summarize 2-3 key points',
                'Include a clear call-to-action',
                'Encourage engagement or next steps',
                'Keep it concise (2-3 sentences)',
            ]
        ];
    }

    /**
     * Generate content structure recommendations based on target word count
     */
    public function generateContentStructure(int $targetWordCount): array
    {
        $sections = match (true) {
            $targetWordCount < 500 => [
                ['section' => 'Introduction', 'words' => 50, 'percentage' => 10],
                ['section' => 'Main Point 1', 'words' => 150, 'percentage' => 30],
                ['section' => 'Main Point 2', 'words' => 150, 'percentage' => 30],
                ['section' => 'Conclusion & CTA', 'words' => 50, 'percentage' => 10],
            ],
            $targetWordCount < 1500 => [
                ['section' => 'Introduction', 'words' => 150, 'percentage' => 10],
                ['section' => 'Context/Background', 'words' => 150, 'percentage' => 10],
                ['section' => 'Main Point 1', 'words' => 300, 'percentage' => 20],
                ['section' => 'Main Point 2', 'words' => 300, 'percentage' => 20],
                ['section' => 'Main Point 3', 'words' => 300, 'percentage' => 20],
                ['section' => 'Conclusion & CTA', 'words' => 150, 'percentage' => 10],
            ],
            default => [
                ['section' => 'Introduction', 'words' => 300, 'percentage' => 10],
                ['section' => 'Context/Background', 'words' => 300, 'percentage' => 10],
                ['section' => 'Main Point 1', 'words' => 600, 'percentage' => 20],
                ['section' => 'Main Point 2', 'words' => 600, 'percentage' => 20],
                ['section' => 'Main Point 3', 'words' => 600, 'percentage' => 20],
                ['section' => 'Advanced Strategies', 'words' => 300, 'percentage' => 10],
                ['section' => 'Conclusion & CTA', 'words' => 300, 'percentage' => 10],
            ]
        };

        return [
            'success' => true,
            'target_word_count' => $targetWordCount,
            'recommended_structure' => $sections,
            'tips' => [
                'Introduction should hook and preview content',
                'Each main point should have subheadings',
                'Use short paragraphs (2-4 sentences)',
                'Include examples and practical tips',
                'Break up text with lists and formatting',
                'Conclusion should reinforce key points and include CTA',
            ]
        ];
    }
}
