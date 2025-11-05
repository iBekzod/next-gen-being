<?php

namespace App\Services\Video;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class VoiceoverService
{
    /**
     * Generate voiceover from script text
     *
     * @param string $scriptText
     * @param User $user
     * @param string $videoType
     * @return string URL to voiceover file
     */
    public function generateVoiceover(string $scriptText, User $user, string $videoType): string
    {
        // Choose TTS provider based on user tier
        if ($user->video_tier === 'video_pro' && config('services.elevenlabs.api_key')) {
            return $this->generateWithElevenLabs($scriptText, $videoType);
        }

        // Default to OpenAI TTS (available for all tiers)
        return $this->generateWithOpenAI($scriptText, $videoType);
    }

    /**
     * Generate voiceover using OpenAI TTS
     */
    protected function generateWithOpenAI(string $text, string $videoType): string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new Exception('OpenAI API key not configured');
        }

        // Choose voice based on video type
        $voice = $this->getOpenAIVoice($videoType);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/audio/speech', [
            'model' => 'tts-1-hd', // Higher quality
            'input' => $text,
            'voice' => $voice,
            'speed' => 1.0,
        ]);

        if (!$response->successful()) {
            throw new Exception('OpenAI TTS generation failed: ' . $response->body());
        }

        // Save audio file
        $filename = 'voiceovers/' . uniqid('vo_') . '.mp3';
        Storage::disk('public')->put($filename, $response->body());

        return Storage::disk('public')->url($filename);
    }

    /**
     * Generate voiceover using ElevenLabs (Premium quality)
     */
    protected function generateWithElevenLabs(string $text, string $videoType): string
    {
        $apiKey = config('services.elevenlabs.api_key');
        if (!$apiKey) {
            throw new Exception('ElevenLabs API key not configured');
        }

        $voiceId = config('services.elevenlabs.voice_id', '21m00Tcm4TlvDq8ikWAM'); // Default voice

        $response = Http::withHeaders([
            'xi-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
            'text' => $text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('ElevenLabs TTS generation failed: ' . $response->body());
        }

        // Save audio file
        $filename = 'voiceovers/' . uniqid('vo_elevenlabs_') . '.mp3';
        Storage::disk('public')->put($filename, $response->body());

        return Storage::disk('public')->url($filename);
    }

    /**
     * Get appropriate OpenAI voice for video type
     */
    protected function getOpenAIVoice(string $videoType): string
    {
        // Available voices: alloy, echo, fable, onyx, nova, shimmer
        return match($videoType) {
            'youtube' => config('services.openai.tts_voice', 'onyx'), // Professional
            'tiktok' => 'nova', // Energetic female
            'reel' => 'shimmer', // Warm female
            'short' => 'echo', // Clear male
            default => 'alloy', // Neutral
        };
    }

    /**
     * Get voiceover duration from audio file
     */
    public function getAudioDuration(string $filePath): float
    {
        // Use getID3 or ffprobe to get audio duration
        // For now, return estimated duration based on text length
        $fileSize = Storage::disk('public')->size($filePath);

        // Rough estimate: ~1MB per minute for MP3
        return ($fileSize / 1024 / 1024) * 60;
    }

    /**
     * Adjust voice speed to match target duration
     */
    public function adjustSpeed(string $audioPath, float $targetDuration): string
    {
        // This would use FFmpeg to speed up/slow down audio
        // For Phase 2, we'll implement basic version
        // Phase 3 can add more sophisticated timing adjustments

        return $audioPath; // Return original for now
    }

    /**
     * Estimate TTS cost
     */
    public function estimateCost(string $text, string $provider = 'openai'): float
    {
        $characters = mb_strlen($text);

        return match($provider) {
            'openai' => ($characters / 1000000) * 15, // $15 per 1M characters
            'elevenlabs' => ($characters / 1000) * 0.30, // $0.30 per 1K characters
            default => 0,
        };
    }
}
