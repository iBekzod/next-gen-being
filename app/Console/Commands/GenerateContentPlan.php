<?php

namespace App\Console\Commands;

use App\Models\ContentPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateContentPlan extends Command
{
    protected $signature = 'content:plan
                            {month? : Month in YYYY-MM format (defaults to next month)}
                            {--theme= : Specific theme for the month}';

    protected $description = 'Generate a monthly content plan with diverse topics';

    public function handle(): int
    {
        $month = $this->argument('month') ?? now()->addMonth()->format('Y-m');
        $customTheme = $this->option('theme');

        // Check if plan already exists
        $existing = ContentPlan::where('month', $month)->first();
        if ($existing) {
            if (!$this->confirm("A plan for {$month} already exists ({$existing->theme}). Overwrite?")) {
                return self::SUCCESS;
            }
            $existing->delete();
        }

        $this->info("📅 Generating content plan for {$month}...");

        // Generate plan using AI
        $planData = $this->generatePlanWithAI($month, $customTheme);

        if (!$planData) {
            $this->error('Failed to generate content plan');
            return self::FAILURE;
        }

        // Create the plan
        $plan = ContentPlan::create([
            'month' => $month,
            'theme' => $planData['theme'],
            'description' => $planData['description'],
            'planned_topics' => $planData['topics'],
            'status' => 'active',
        ]);

        // Count free vs premium
        $freeCount = count(array_filter($plan->planned_topics, fn($t) => ($t['type'] ?? 'free') === 'free'));
        $premiumCount = count($plan->planned_topics) - $freeCount;

        $this->info("✅ Content plan created successfully!");
        $this->info("   Month: {$plan->month}");
        $this->info("   Theme: {$plan->theme}");
        $this->info("   Total Topics: " . count($plan->planned_topics));
        $this->info("   FREE: {$freeCount} (80%)");
        $this->info("   PREMIUM: {$premiumCount} (20%)");
        $this->newLine();
        $this->info("📋 Planned Topics:");

        foreach ($plan->planned_topics as $index => $topic) {
            $title = is_array($topic) ? $topic['title'] : $topic;
            $type = is_array($topic) ? ($topic['type'] ?? 'free') : 'free';
            $week = is_array($topic) ? ($topic['week'] ?? '?') : '?';
            $badge = $type === 'premium' ? '💎 PREMIUM' : '🆓 FREE';
            $this->line("   " . ($index + 1) . ". [{$badge}] [Week {$week}] {$title}");
        }

        return self::SUCCESS;
    }

    private function generatePlanWithAI(string $month, ?string $customTheme): ?array
    {
        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            $this->error('Groq API key not configured');
            return null;
        }

        $monthName = \Carbon\Carbon::parse($month . '-01')->format('F Y');

        $prompt = $customTheme
            ? "Create a monthly content plan for {$monthName} with theme: {$customTheme}"
            : "Create a diverse monthly content plan for {$monthName} covering emerging technologies";

        $prompt .= "

Generate a STRATEGIC monthly content plan designed to BUILD AUDIENCE → ESTABLISH TRUST → DRIVE SUBSCRIPTIONS.

🎯 CONVERSION FUNNEL STRATEGY (CRITICAL):

**MONTH STRUCTURE (30 posts total):**
- Week 1-2: 15 posts (100% FREE) - \"Discovery & Value Proof\"
- Week 3: 8 posts (75% FREE, 25% PREMIUM) - \"Trust Building\"
- Week 4: 7 posts (60% FREE, 40% PREMIUM) - \"Conversion Push\"

**FREE Content Strategy (24 posts - 80%):**
Purpose: Attract traffic, prove expertise, build trust
- High-value tutorials that solve REAL problems
- Comparisons & benchmarks (drive search traffic)
- Complete project builds (showcase depth)
- Feature deep-dives (demonstrate expertise)
Examples: \"ChatGPT vs Claude\", \"Building E-commerce with Laravel\", \"PostgreSQL Hidden Features\"

**PREMIUM Content Strategy (6 posts - 20%):**
Purpose: Create FOMO, demonstrate exclusive value
- Advanced architecture patterns (production-scale solutions)
- Complete SaaS templates with full source code
- Performance optimization secrets (10x improvements)
- Security deep-dives with real vulnerability examples
- Insider knowledge from high-scale applications
- Automation scripts & deployment strategies
Examples: \"Multi-Tenant SaaS Architecture\", \"Scaling to 100M Requests/Day\", \"Complete Stripe Integration Template\"

THEME SELECTION (if not specified):
Choose from:
- Full-Stack Development Mastery (Laravel, React, Next.js)
- AI Integration & LLM Applications
- Cloud Architecture & DevOps
- Performance Optimization & Scaling
- Modern Frontend Frameworks
- API Design & Microservices
- Database Optimization & Design
- Security & Authentication
- Testing & CI/CD Automation
- SaaS Development from Scratch

⚠️ CRITICAL RULES FOR SUBSCRIPTIONS:
1. **Value Ladder**: Free content must be GOOD, premium must be EXCEPTIONAL
2. **Specificity**: Premium topics must promise concrete, actionable solutions
3. **Exclusivity**: Premium should offer something NOT easily found elsewhere
4. **Urgency**: Create content series where free posts lead to premium conclusions
5. **Social Proof**: Include success metrics (\"Used by 10k+ developers\")

TOPIC VARIETY (AVOID REPETITION):
✅ GOOD (varied, specific, valuable):
- \"ChatGPT vs Claude vs Gemini: Real Performance Benchmarks\" (FREE)
- \"Building a Complete Multi-Tenant SaaS with Laravel 11\" (PREMIUM)
- \"Laravel Octane: 10x Performance Without Infrastructure Changes\" (FREE)
- \"Production-Ready Authentication: JWT, OAuth2, and Session Security\" (PREMIUM)
- \"React Server Components: When to Use vs Client Components\" (FREE)

❌ BAD (generic, repetitive):
- \"Introduction to Laravel\"
- \"Getting Started with React\"
- \"How to Install Docker\"

CONTENT DISTRIBUTION:
Week 1-2 (100% FREE - 15 posts):
- 5x Comparison/Benchmarks (drive search traffic)
- 5x Complete Tutorials (prove expertise)
- 3x Feature Deep-Dives (show depth)
- 2x Best Practices Lists (easy engagement)

Week 3 (75% FREE, 25% PREMIUM - 8 posts):
- 6x FREE Advanced Tutorials (build trust)
- 2x PREMIUM Architecture/Scaling (create interest)

Week 4 (60% FREE, 40% PREMIUM - 7 posts):
- 4x FREE Quick Wins (maintain traffic)
- 3x PREMIUM Complete Solutions (drive conversions)

Return ONLY this JSON:
{
  \"theme\": \"Monthly theme name\",
  \"description\": \"2-3 sentence description of what this month covers and the conversion strategy\",
  \"topics\": [
    {\"title\": \"Specific topic 1\", \"type\": \"free\", \"week\": 1, \"category\": \"comparison\"},
    {\"title\": \"Specific topic 2\", \"type\": \"free\", \"week\": 1, \"category\": \"tutorial\"},
    {\"title\": \"Premium topic 1\", \"type\": \"premium\", \"week\": 3, \"category\": \"architecture\"},
    ... (30 topics total: 24 free, 6 premium)
  ]
}

Categories: comparison, tutorial, deep-dive, best-practices, architecture, scaling, security, complete-project";

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a content strategist planning diverse, engaging technical content. You create cohesive monthly themes with varied topic approaches. You MUST return ONLY valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.8, // Higher temperature for creativity
                    'max_tokens' => 2000,
                ]);

            if (!$response->successful()) {
                $this->error('AI API request failed: ' . $response->body());
                return null;
            }

            $content = $response->json()['choices'][0]['message']['content'];

            // Try to parse JSON from response
            $data = json_decode($content, true);
            if (!$data && preg_match('/\{.*\}/s', $content, $matches)) {
                $data = json_decode($matches[0], true);
            }

            if ($data && isset($data['theme']) && isset($data['topics']) && count($data['topics']) >= 25) {
                // Validate topic structure
                $validTopics = array_filter($data['topics'], function($topic) {
                    return isset($topic['title']) && isset($topic['type']) && in_array($topic['type'], ['free', 'premium']);
                });

                if (count($validTopics) >= 25) {
                    return $data;
                }
            }

            $this->error('Invalid response format from AI');
            return null;

        } catch (\Exception $e) {
            $this->error('Error generating plan: ' . $e->getMessage());
            return null;
        }
    }
}
