<?php
/**
 * Content Curation System - Diagnostic Script
 * Check the status of each phase in the pipeline
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ContentSource;
use App\Models\CollectedContent;
use App\Models\ContentAggregation;
use App\Models\Post;
use App\Models\SourceReference;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║     Content Curation System - Pipeline Diagnostic Report      ║\n";
echo "║                      " . now()->format('Y-m-d H:i:s') . "                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// PHASE 1: CONTENT SOURCES
echo "📌 PHASE 1: CONTENT SOURCES\n";
echo str_repeat("─", 60) . "\n";
$sourceCount = ContentSource::count();
$activeSources = ContentSource::where('scraping_enabled', true)->count();
echo "Total sources configured: " . $sourceCount . "\n";
echo "Active sources (scraping enabled): " . $activeSources . "\n\n";

if ($sourceCount === 0) {
    echo "❌ ERROR: No sources initialized!\n";
    echo "   Run: php artisan content:init-sources\n\n";
} else {
    echo "✅ Sources are initialized\n\n";
}

// PHASE 2: COLLECTED CONTENT
echo "📌 PHASE 2: COLLECTED CONTENT\n";
echo str_repeat("─", 60) . "\n";
$collectedCount = CollectedContent::count();
$byType = CollectedContent::selectRaw('content_type, COUNT(*) as count')
    ->groupBy('content_type')
    ->pluck('count', 'content_type');

echo "Total articles collected: " . $collectedCount . "\n";
if ($byType->isNotEmpty()) {
    echo "By type:\n";
    foreach ($byType as $type => $count) {
        echo "  - " . ucfirst($type) . ": " . $count . "\n";
    }
    echo "\n";
} else {
    echo "\n";
}

if ($collectedCount === 0) {
    echo "❌ ERROR: No articles collected!\n";
    echo "   This is the first critical step. Without articles, nothing else works.\n";
    echo "   Try:\n";
    echo "   1. php artisan content:scrape-all --limit=5\n";
    echo "   2. docker-compose logs -f ngb-app (watch for errors)\n";
    echo "   3. Check network: docker-compose exec ngb-app curl -I https://dev.to/feed\n\n";
} else {
    echo "✅ Articles are being collected\n\n";
}

// Check for low-quality articles
$lowQuality = CollectedContent::whereRaw('char_length(full_content) < 500')->count();
if ($lowQuality > 0) {
    echo "⚠️  WARNING: " . $lowQuality . " articles have low word count (<100 words)\n";
    echo "   These may be filtered out during processing\n\n";
}

// PHASE 3: CONTENT AGGREGATIONS
echo "📌 PHASE 3: CONTENT AGGREGATIONS\n";
echo str_repeat("─", 60) . "\n";
$aggCount = ContentAggregation::count();
$avgConfidence = ContentAggregation::avg('confidence_score') ?? 0;

echo "Total aggregations created: " . $aggCount . "\n";
if ($aggCount > 0) {
    echo "Average confidence score: " . round($avgConfidence * 100) . "%\n";
    echo "Min confidence: " . round(ContentAggregation::min('confidence_score') * 100) . "%\n";
    echo "Max confidence: " . round(ContentAggregation::max('confidence_score') * 100) . "%\n\n";
} else {
    echo "\n";
}

if ($aggCount === 0 && $collectedCount > 0) {
    echo "❌ ERROR: Aggregations not created!\n";
    echo "   Deduplication may not have run. Try:\n";
    echo "   php artisan content:deduplicate --hours=24\n\n";
} elseif ($aggCount > 0) {
    echo "✅ Content aggregations created\n\n";
}

// PHASE 4: CURATED POSTS
echo "📌 PHASE 4: CURATED POSTS (Main Output)\n";
echo str_repeat("─", 60) . "\n";
$curatedCount = Post::where('is_curated', true)->count();
$avgParaphConfidence = Post::where('is_curated', true)->avg('paraphrase_confidence_score') ?? 0;
$verifiedCount = Post::where('is_curated', true)->where('is_fact_verified', true)->count();

echo "Total curated posts created: " . $curatedCount . "\n";
if ($curatedCount > 0) {
    echo "Verified (fact checked): " . $verifiedCount . " / " . $curatedCount . "\n";
    echo "Average paraphrase confidence: " . round($avgParaphConfidence * 100) . "%\n\n";
} else {
    echo "\n";
}

if ($curatedCount === 0 && $aggCount > 0) {
    echo "❌ ERROR: No curated posts created!\n";
    echo "   This is the main issue. The paraphrasing step needs to run.\n";
    echo "   Possible causes:\n";
    echo "   1. ANTHROPIC_API_KEY not set in .env\n";
    echo "   2. Paraphrasing command not run\n";
    echo "   3. Paraphrasing job failed\n\n";

    echo "   Fixes:\n";
    echo "   1. Add API key: grep ANTHROPIC_API_KEY .env\n";
    echo "   2. Run paraphrasing: php artisan content:paraphrase-pending --limit=5\n";
    echo "   3. Check errors: docker-compose logs ngb-app | grep -i error\n";
    echo "   4. Check failed jobs: php artisan queue:failed\n\n";
} elseif ($curatedCount > 0) {
    echo "✅ Curated posts created successfully\n\n";
}

// PHASE 5: TRANSLATIONS
echo "📌 PHASE 5: TRANSLATIONS\n";
echo str_repeat("─", 60) . "\n";
$translationCount = Post::where('base_post_id', '!=', null)->count();
$languages = Post::where('base_post_id', '!=', null)
    ->selectRaw('base_language, COUNT(*) as count')
    ->groupBy('base_language')
    ->pluck('count', 'base_language');

echo "Total translated posts: " . $translationCount . "\n";
if ($languages->isNotEmpty()) {
    echo "Languages: ";
    echo implode(", ", $languages->keys()->map(fn($l) => strtoupper($l))->toArray()) . "\n\n";
} else {
    echo "\n";
}

if ($translationCount === 0 && $curatedCount > 0) {
    echo "⚠️  WARNING: No translations created yet\n";
    echo "   Translations only happen AFTER curated posts are created.\n";
    echo "   Try: php artisan content:translate-pending --languages=es,fr,de --limit=5\n\n";
} elseif ($translationCount > 0) {
    echo "✅ Posts translated to multiple languages\n\n";
}

// PHASE 6: REFERENCES
echo "📌 PHASE 6: SOURCE REFERENCES\n";
echo str_repeat("─", 60) . "\n";
$refCount = SourceReference::count();
$formats = SourceReference::selectRaw('citation_style, COUNT(*) as count')
    ->groupBy('citation_style')
    ->pluck('count', 'citation_style');

echo "Total references tracked: " . $refCount . "\n";
if ($formats->isNotEmpty()) {
    echo "Citation formats: ";
    echo implode(", ", $formats->keys()->toArray()) . "\n\n";
} else {
    echo "\n";
}

// SUMMARY
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      PIPELINE SUMMARY                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$pipeline = [
    'Sources' => $sourceCount,
    'Collected' => $collectedCount,
    'Aggregations' => $aggCount,
    'Curated Posts ⭐' => $curatedCount,
    'Translations' => $translationCount,
    'References' => $refCount,
];

foreach ($pipeline as $stage => $count) {
    $status = match ($count) {
        0 => '❌',
        default => '✅'
    };
    echo $status . " " . str_pad($stage, 25) . ": " . $count . "\n";
}

echo "\n";

// NEXT STEPS
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                       NEXT STEPS                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($collectedCount === 0) {
    echo "1️⃣  FIRST: Collect articles\n";
    echo "   php artisan content:scrape-all --limit=5\n";
    echo "   (Check internet connectivity and RSS feeds)\n\n";
}

if ($collectedCount > 0 && $aggCount === 0) {
    echo "2️⃣  THEN: Find duplicate content\n";
    echo "   php artisan content:deduplicate --hours=24\n\n";
}

if ($aggCount > 0 && $curatedCount === 0) {
    echo "3️⃣  THEN: Create curated posts\n";
    echo "   (Requires ANTHROPIC_API_KEY in .env)\n";
    echo "   php artisan content:paraphrase-pending --limit=5\n\n";
}

if ($curatedCount > 0 && $translationCount === 0) {
    echo "4️⃣  THEN: Translate to other languages\n";
    echo "   php artisan content:translate-pending --languages=es,fr,de --limit=5\n\n";
}

echo "═" . str_repeat("═", 58) . "═\n\n";

// API KEY CHECK
echo "🔑 API KEY STATUS\n";
echo str_repeat("─", 60) . "\n";
$apiKey = env('ANTHROPIC_API_KEY');
if (empty($apiKey)) {
    echo "❌ ANTHROPIC_API_KEY not set in .env\n";
    echo "   Paraphrasing and translation will fail without this.\n";
    echo "   Add to .env: ANTHROPIC_API_KEY=your-key-here\n";
} else {
    echo "✅ ANTHROPIC_API_KEY is configured\n";
    echo "   Key: " . substr($apiKey, 0, 10) . "..." . substr($apiKey, -4) . "\n";
}

echo "\n";

// DATABASE CONNECTION CHECK
echo "🗄️  DATABASE STATUS\n";
echo str_repeat("─", 60) . "\n";
try {
    \DB::connection()->getPdo();
    echo "✅ Database connected and operational\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n";

echo "═" . str_repeat("═", 58) . "═\n";
echo "Report generated at: " . now()->format('Y-m-d H:i:s') . "\n";
echo "═" . str_repeat("═", 58) . "═\n\n";
