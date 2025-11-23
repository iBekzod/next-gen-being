<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WritingAssistant\WritingAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WritingAssistantController extends Controller
{
    private WritingAssistantService $service;

    public function __construct(WritingAssistantService $service)
    {
        $this->service = $service;
        $this->middleware('auth:sanctum');
    }

    /**
     * Improve text quality
     * POST /api/writing/improve-text
     */
    public function improveText(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10|max:50000',
            ]);

            $improvements = $this->service->improveText($validated['text']);

            return response()->json([
                'success' => true,
                'data' => $improvements,
                'message' => 'Text analysis completed successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing text: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get grammar and spelling checks
     * POST /api/writing/check-grammar
     */
    public function checkGrammar(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10',
            ]);

            $improvements = $this->service->improveText($validated['text']);
            $grammar = $improvements['grammar'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $grammar,
                'message' => 'Grammar check completed',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get style suggestions
     * POST /api/writing/style-suggestions
     */
    public function getStyleSuggestions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10',
            ]);

            $improvements = $this->service->improveText($validated['text']);
            $style = $improvements['style'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $style,
                'message' => 'Style suggestions generated',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze readability
     * POST /api/writing/readability
     */
    public function analyzeReadability(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10',
            ]);

            $improvements = $this->service->improveText($validated['text']);
            $readability = $improvements['readability'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $readability,
                'message' => 'Readability analysis completed',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze tone
     * POST /api/writing/analyze-tone
     */
    public function analyzeTone(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10',
            ]);

            $improvements = $this->service->improveText($validated['text']);
            $tone = $improvements['tone'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $tone,
                'message' => 'Tone analysis completed',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate content suggestions
     * POST /api/writing/content-suggestions
     */
    public function generateContentSuggestions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|min:3|max:500',
                'tone' => 'nullable|string|in:professional,casual,academic,creative,persuasive',
            ]);

            $tone = $validated['tone'] ?? 'professional';
            $suggestions = $this->service->generateContentSuggestions($validated['topic'], $tone);

            return response()->json([
                'success' => true,
                'data' => $suggestions,
                'message' => 'Content suggestions generated successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get headline suggestions for a topic
     * POST /api/writing/headline-suggestions
     */
    public function getHeadlineSuggestions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|min:3|max:500',
            ]);

            $suggestions = $this->service->generateContentSuggestions($validated['topic']);
            $headlines = $suggestions['headline_suggestions'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $headlines,
                'message' => 'Headline suggestions generated',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get introduction templates
     * POST /api/writing/introduction-templates
     */
    public function getIntroductionTemplates(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|min:3|max:500',
            ]);

            $suggestions = $this->service->generateContentSuggestions($validated['topic']);
            $templates = $suggestions['introduction_templates'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => 'Introduction templates generated',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get content outlines
     * POST /api/writing/content-outlines
     */
    public function getContentOutlines(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|min:3|max:500',
            ]);

            $suggestions = $this->service->generateContentSuggestions($validated['topic']);
            $outlines = $suggestions['outlines'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $outlines,
                'message' => 'Content outlines generated',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract keywords from text
     * POST /api/writing/extract-keywords
     */
    public function extractKeywords(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:10',
            ]);

            $suggestions = $this->service->generateContentSuggestions($validated['text']);
            $keywords = $suggestions['keywords'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $keywords,
                'message' => 'Keywords extracted successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate full blog post content with AI
     * POST /api/v1/writing/generate-content
     */
    public function generateContent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'topic' => 'required|string|min:3|max:200',
                'type' => 'required|in:full,outline,introduction,conclusion',
                'tone' => 'required|in:professional,engaging,casual,academic',
                'length' => 'required|in:short,medium,long',
                'keywords' => 'nullable|string|max:1000',
            ]);

            // Simulate AI generation with structured content based on user preferences
            $topic = $validated['topic'];
            $type = $validated['type'];
            $tone = $validated['tone'];
            $length = $validated['length'];
            $keywords = $validated['keywords'] ?? '';

            // Generate tone-specific introduction
            $introductions = [
                'professional' => "In today's professional landscape, understanding {$topic} is essential for success. This comprehensive guide provides industry insights and best practices.",
                'engaging' => "Are you ready to dive deep into {$topic}? Join us as we explore everything you need to know to master this topic!",
                'casual' => "Let's talk about {$topic}. Whether you're new to this or already familiar with it, there's always something new to learn.",
                'academic' => "The topic of {$topic} has been a subject of considerable research and discussion in academic circles. This analysis explores key concepts and findings.",
            ];

            $introduction = $introductions[$tone] ?? $introductions['professional'];

            // Determine content sections based on length preference
            $includeExamples = $length !== 'short';
            $includeBestPractices = $length !== 'short';
            $includeAdvanced = $length === 'long';
            $numConcepts = $length === 'short' ? 2 : ($length === 'medium' ? 3 : 5);

            // Build content structure based on type
            if ($type === 'outline') {
                $content = "# {$topic}\n\n## Outline\n\n";
                $content .= "1. Introduction\n";
                $content .= "2. Key Concepts and Definitions\n";
                if ($length !== 'short') {
                    $content .= "3. Historical Context\n";
                    $content .= "4. Main Components\n";
                }
                $content .= ($length !== 'short' ? 5 : 3) . ". Best Practices and Implementation\n";
                if ($length === 'long') {
                    $content .= "6. Case Studies\n";
                    $content .= "7. Challenges and Solutions\n";
                    $content .= "8. Future Trends\n";
                    $content .= "9. Conclusion and Recommendations\n";
                } else {
                    $content .= ($length === 'medium' ? 6 : 4) . ". Conclusion and Recommendations\n";
                }
                if ($keywords) {
                    $content .= "\n**Key Topics**: " . $keywords . "\n";
                }
            } elseif ($type === 'introduction') {
                $content = "# {$topic}\n\n## Introduction\n\n{$introduction}";
                if ($keywords) {
                    $content .= "\n\n**Topics we'll cover**: " . $keywords . "\n";
                }
            } elseif ($type === 'conclusion') {
                $content = "## Conclusion\n\n";
                $content .= "We've explored the key aspects of {$topic} throughout this article. The main takeaways are:\n\n";
                $content .= "- Understanding the core principles is fundamental\n";
                $content .= "- Practical application requires practice and persistence\n";
                if ($length !== 'short') {
                    $content .= "- Continuous learning keeps you ahead of the curve\n";
                    $content .= "- Building a strong foundation enables growth\n";
                }
                $content .= "\n";
                $content .= "Whether you're just beginning your journey with {$topic} or looking to deepen your expertise, remember that mastery comes with time, practice, and staying updated with the latest developments.";
            } else { // full content
                $content = "# {$topic}\n\n";
                $content .= "## Introduction\n\n{$introduction}\n\n";
                $content .= "## Key Concepts\n\n";
                $content .= "### Understanding the Fundamentals\n\n";
                $content .= "The foundation of {$topic} rests on several critical concepts. Let's break them down:\n\n";

                for ($i = 1; $i <= $numConcepts; $i++) {
                    $content .= "$i. **Concept $i**: The essential element #$i that defines {$topic}\n";
                }
                $content .= "\n";

                if ($includeBestPractices) {
                    $content .= "## Best Practices\n\n";
                    $content .= "- Start with solid fundamentals before moving to advanced topics\n";
                    $content .= "- Practice regularly with real-world scenarios\n";
                    $content .= "- Stay updated with industry standards and trends\n";
                    if ($length === 'long') {
                        $content .= "- Learn from both successes and failures\n";
                        $content .= "- Build a community of learners and practitioners\n";
                        $content .= "- Document your learning journey\n";
                    } else {
                        $content .= "- Learn from both successes and failures\n";
                    }
                    $content .= "\n";
                }

                if ($includeExamples) {
                    $content .= "## Practical Examples\n\n";
                    $content .= "Real-world applications of {$topic} include:\n\n";
                    $numExamples = $length === 'long' ? 5 : 3;
                    for ($i = 1; $i <= $numExamples; $i++) {
                        $content .= "- **Example $i**: Application scenario with practical insights\n";
                    }
                    $content .= "\n";
                }

                if ($includeAdvanced) {
                    $content .= "## Advanced Topics\n\n";
                    $content .= "For those wanting to go deeper:\n\n";
                    $content .= "- Performance optimization strategies\n";
                    $content .= "- Integration with other technologies\n";
                    $content .= "- Security considerations\n";
                    $content .= "- Scalability approaches\n\n";
                }

                if ($keywords) {
                    $content .= "## Covered Topics\n\n";
                    $content .= "This article covers the following key areas: " . $keywords . "\n\n";
                }

                $content .= "## Conclusion\n\n";
                $content .= "Mastering {$topic} takes time and dedication. Use this guide as a starting point for your learning journey, and remember to practice regularly and stay curious.";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $content,
                    'wordCount' => str_word_count(strip_tags($content)),
                    'type' => $type,
                    'tone' => $tone,
                    'message' => 'Content generated successfully. Review and customize before publishing.',
                ],
                'message' => 'Content generation completed',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating content: ' . $e->getMessage(),
            ], 500);
        }
    }
}
