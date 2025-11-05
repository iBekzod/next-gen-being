<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Storage;

class CaptionGeneratorService
{
    /**
     * Generate WebVTT captions from timestamped script
     *
     * @param array $timestamps Array of ['start' => float, 'end' => float, 'text' => string]
     * @param int $totalDuration Total video duration in seconds
     * @return string URL to WebVTT file
     */
    public function generateCaptions(array $timestamps, int $totalDuration): string
    {
        $vtt = $this->createWebVTT($timestamps);

        // Save WebVTT file
        $filename = 'captions/' . uniqid('captions_') . '.vtt';
        Storage::disk('public')->put($filename, $vtt);

        return Storage::disk('public')->url($filename);
    }

    /**
     * Create WebVTT format string from timestamps
     */
    protected function createWebVTT(array $timestamps): string
    {
        $vtt = "WEBVTT\n\n";

        foreach ($timestamps as $index => $timestamp) {
            $start = $this->formatTimestamp($timestamp['start']);
            $end = $this->formatTimestamp($timestamp['end']);
            $text = $this->formatText($timestamp['text']);

            $vtt .= ($index + 1) . "\n";
            $vtt .= "{$start} --> {$end}\n";
            $vtt .= "{$text}\n\n";
        }

        return $vtt;
    }

    /**
     * Format seconds to WebVTT timestamp format (HH:MM:SS.mmm)
     */
    protected function formatTimestamp(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%06.3f', $hours, $minutes, $secs);
    }

    /**
     * Format caption text (max 2 lines, 42 characters per line)
     */
    protected function formatText(string $text): string
    {
        // Remove extra whitespace
        $text = trim(preg_replace('/\s+/', ' ', $text));

        // WebVTT best practice: max 42 characters per line
        $maxLength = 42;

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        // Split into two lines at a natural break point
        $words = explode(' ', $text);
        $line1 = '';
        $line2 = '';

        foreach ($words as $word) {
            if (strlen($line1) + strlen($word) + 1 <= $maxLength) {
                $line1 .= ($line1 ? ' ' : '') . $word;
            } else {
                $line2 .= ($line2 ? ' ' : '') . $word;
            }
        }

        return $line1 . ($line2 ? "\n" . $line2 : '');
    }

    /**
     * Generate SRT format (alternative to WebVTT)
     */
    public function generateSRT(array $timestamps): string
    {
        $srt = '';

        foreach ($timestamps as $index => $timestamp) {
            $start = $this->formatSRTTimestamp($timestamp['start']);
            $end = $this->formatSRTTimestamp($timestamp['end']);
            $text = $timestamp['text'];

            $srt .= ($index + 1) . "\n";
            $srt .= "{$start} --> {$end}\n";
            $srt .= "{$text}\n\n";
        }

        $filename = 'captions/' . uniqid('captions_') . '.srt';
        Storage::disk('public')->put($filename, $srt);

        return Storage::disk('public')->url($filename);
    }

    /**
     * Format seconds to SRT timestamp format (HH:MM:SS,mmm)
     */
    protected function formatSRTTimestamp(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $millis = round(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $millis);
    }

    /**
     * Generate styled captions for social media (with emojis, highlights)
     */
    public function generateStyledCaptions(array $timestamps, string $style = 'tiktok'): string
    {
        $styledTimestamps = array_map(function($timestamp) use ($style) {
            $text = $timestamp['text'];

            if ($style === 'tiktok') {
                // Add emojis and emphasis for TikTok style
                $text = $this->addTikTokStyle($text);
            } elseif ($style === 'youtube') {
                // More formal, clear captions
                $text = $this->addYouTubeStyle($text);
            }

            return array_merge($timestamp, ['text' => $text]);
        }, $timestamps);

        return $this->createWebVTT($styledTimestamps);
    }

    /**
     * Add TikTok-style formatting
     */
    protected function addTikTokStyle(string $text): string
    {
        // Capitalize first word of each sentence
        $text = ucfirst($text);

        // Add emphasis to key words (simplified)
        $keywords = ['important', 'remember', 'tip', 'trick', 'hack', 'secret'];

        foreach ($keywords as $keyword) {
            $text = preg_replace(
                '/\b' . $keyword . '\b/i',
                strtoupper($keyword) . ' ✨',
                $text
            );
        }

        return $text;
    }

    /**
     * Add YouTube-style formatting
     */
    protected function addYouTubeStyle(string $text): string
    {
        // Clean, readable captions
        return ucfirst(trim($text));
    }

    /**
     * Generate caption thumbnails (burnt-in captions for preview)
     */
    public function generateBurntInCaptions(array $timestamps): array
    {
        // This would return FFmpeg commands to burn captions into video
        // For Phase 2, we return the structure

        return array_map(function($timestamp) {
            return [
                'text' => $timestamp['text'],
                'start' => $timestamp['start'],
                'end' => $timestamp['end'],
                'style' => [
                    'fontsize' => 24,
                    'fontcolor' => 'white',
                    'box' => 1,
                    'boxcolor' => 'black@0.5',
                    'boxborderw' => 5,
                ],
            ];
        }, $timestamps);
    }
}
