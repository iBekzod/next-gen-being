<?php
/**
 * Debug scraper - test content extraction from Dev.to
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;

// Test URL
$url = 'https://dev.to/paperium/semi-supervised-learning-with-generative-adversarial-networks-1fj0';

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║     Dev.to Content Extraction Debugging Script                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Testing URL: $url\n\n";

$response = Http::timeout(30)
    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
    ->get($url);

if (!$response->successful()) {
    echo "❌ Failed to fetch URL (HTTP " . $response->status() . ")\n";
    exit(1);
}

echo "✅ Successfully fetched URL (HTTP 200)\n\n";

$crawler = new Crawler($response->body());

// Test selectors
echo "─ Testing CSS Selectors:\n";

$selectors = [
    '.crayons-article__body' => 'Dev.to specific body',
    'article' => 'Generic article tag',
    '[role="main"]' => 'ARIA main role',
    '.content' => 'Content class',
    '[data-article-body]' => 'Data attribute',
];

foreach ($selectors as $selector => $desc) {
    try {
        $element = $crawler->filter($selector)->first();
        if ($element) {
            $content = $element->html();
            $text = strip_tags($content);
            $wordCount = str_word_count($text);
            $charCount = strlen($text);

            echo sprintf("  %-30s: Found (%d chars, %d words) %s\n",
                $selector,
                $charCount,
                $wordCount,
                $wordCount > 100 ? '✅' : '❌'
            );

            if ($wordCount > 100) {
                echo "     First 200 chars: " . substr($text, 0, 200) . "...\n\n";
            }
        } else {
            echo sprintf("  %-30s: Not found\n", $selector);
        }
    } catch (\Exception $e) {
        echo sprintf("  %-30s: Error\n", $selector);
    }
}

// Test paragraph extraction
echo "\n─ Testing Paragraph Extraction:\n";
$paragraphs = $crawler->filter('body p');
echo "  Total paragraphs: " . $paragraphs->count() . "\n";

if ($paragraphs->count() > 0) {
    $allText = '';
    $paragraphs->each(function (Crawler $node) use (&$allText) {
        $text = $node->text();
        if (!preg_match('/(navbar|menu|footer|nav|comment|reaction|follow)/i', $text)) {
            $allText .= $text . "\n";
        }
    });

    $wordCount = str_word_count($allText);
    $charCount = strlen($allText);

    echo "  Combined paragraph text: $charCount chars, $wordCount words\n";
    echo "  Quality check: " . ($wordCount > 100 ? '✅ PASS' : '❌ FAIL') . "\n";
    echo "  First 300 chars: " . substr($allText, 0, 300) . "...\n\n";
}

// Test div extraction
echo "\n─ Testing Div Container Extraction:\n";
$divs = $crawler->filter('div, section');
echo "  Total divs/sections: " . $divs->count() . "\n";

$largestText = '';
$largestCount = 0;

try {
    $divs->each(function (Crawler $node) use (&$largestText, &$largestCount) {
        $html = $node->html();
        if (!$html) return;

        // Remove script and style
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);

        $text = strip_tags($html);
        $charCount = strlen($text);

        if ($charCount > $largestCount && !preg_match('/(navbar|menu|footer|sidebar|comment)/i', $text)) {
            $largestCount = $charCount;
            $largestText = $text;
        }
    });
} catch (\Exception $e) {
    echo "  Error processing divs: " . $e->getMessage() . "\n";
}

if ($largestCount > 0) {
    $wordCount = str_word_count($largestText);
    echo "  Largest div/section: $largestCount chars, $wordCount words\n";
    echo "  Quality check: " . ($wordCount > 100 ? '✅ PASS' : '❌ FAIL') . "\n";
    echo "  First 300 chars: " . substr($largestText, 0, 300) . "...\n\n";
}

// Test body extraction
echo "\n─ Testing Full Body Extraction:\n";
$bodyHtml = $crawler->filter('body')->html() ?? '';

// Remove scripts and styles
$bodyHtml = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $bodyHtml);
$bodyHtml = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $bodyHtml);

$bodyText = strip_tags($bodyHtml);
$bodyText = preg_replace('/\s+/', ' ', $bodyText);
$bodyText = trim($bodyText);

$charCount = strlen($bodyText);
$wordCount = str_word_count($bodyText);

echo "  Body text: $charCount chars, $wordCount words\n";
echo "  Quality check: " . ($wordCount > 100 ? '✅ PASS' : '❌ FAIL') . "\n";
echo "  First 300 chars: " . substr($bodyText, 0, 300) . "...\n\n";

echo "═" . str_repeat("═", 62) . "═\n";
echo "\n📊 Summary:\n";
echo "  - Dev.to article fetched successfully\n";
echo "  - Content extraction needs optimization\n";
echo "  - Quality threshold: 100 words minimum\n\n";
