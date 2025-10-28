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

        $this->info("✅ Content plan created successfully!");
        $this->info("   Month: {$plan->month}");
        $this->info("   Theme: {$plan->theme}");
        $this->info("   Topics: " . count($plan->planned_topics));
        $this->newLine();
        $this->info("📋 Planned Topics:");
        foreach ($plan->planned_topics as $index => $topic) {
            $this->line("   " . ($index + 1) . ". {$topic}");
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

Generate a cohesive monthly content plan that explores ONE major theme from multiple angles.

THEME SELECTION (if not specified):
Choose from:
- AI & Machine Learning Evolution
- Quantum Computing Fundamentals
- Blockchain & Web3 Innovation
- Extended Reality (XR) Development
- Edge Computing & IoT
- Biotechnology & HealthTech
- Clean Energy Technology
- Space Technology
- Advanced Software Architecture
- Next-Gen Databases
- Cybersecurity Innovation
- Robotics & Automation
- FinTech & DeFi

CONTENT PLANNING PRINCIPLES:
1. Pick ONE cohesive theme for the month
2. Generate 20-25 diverse topics within that theme
3. Each topic should explore a DIFFERENT aspect/technology
4. Mix of: fundamentals, practical implementations, case studies, comparisons, trends
5. Avoid repetitive patterns (don't make all topics \"How to...\")

TOPIC VARIETY EXAMPLES:
✅ GOOD (varied approaches):
- \"Understanding Quantum Algorithms: Shor vs Grover\"
- \"Building Your First Quantum Circuit with Qiskit\"
- \"Post-Quantum Cryptography: Preparing for the Future\"
- \"Quantum Machine Learning: Current State and Limitations\"
- \"IBM Q vs Google Cirq: Platform Comparison\"

❌ BAD (repetitive):
- \"How to Use Quantum Computing\"
- \"How to Build Quantum Apps\"
- \"How to Learn Quantum\"
- \"How to Start Quantum Development\"

TOPIC TYPES TO INCLUDE:
- Fundamentals (2-3 topics)
- Practical implementations (8-10 topics)
- Tool/platform comparisons (2-3 topics)
- Case studies/real-world examples (3-4 topics)
- Future trends/predictions (2-3 topics)
- Best practices/patterns (3-4 topics)
- Security/performance/scalability (2-3 topics)

Return ONLY this JSON:
{
  \"theme\": \"Monthly theme name\",
  \"description\": \"2-3 sentence description of what this month covers\",
  \"topics\": [
    \"Specific topic title 1\",
    \"Specific topic title 2\",
    ... (20-25 topics total)
  ]
}";

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

            if ($data && isset($data['theme']) && isset($data['topics']) && count($data['topics']) >= 15) {
                return $data;
            }

            $this->error('Invalid response format from AI');
            return null;

        } catch (\Exception $e) {
            $this->error('Error generating plan: ' . $e->getMessage());
            return null;
        }
    }
}
