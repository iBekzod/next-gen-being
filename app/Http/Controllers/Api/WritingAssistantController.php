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
}
