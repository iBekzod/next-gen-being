<?php

namespace App\Services\Video;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Exception;

class VideoEditorService
{
    /**
     * Combine all video elements into final video
     *
     * @param array $options [
     *   'clips' => array,
     *   'voiceover' => string,
     *   'captions' => string,
     *   'duration' => int,
     *   'resolution' => string,
     *   'user' => User,
     *   'post' => Post
     * ]
     * @return array ['video_url' => string, 'thumbnail_url' => string, 'file_size_mb' => float]
     */
    public function combineVideo(array $options): array
    {
        $clips = $options['clips'];
        $voiceoverUrl = $options['voiceover'];
        $captionsUrl = $options['captions'];
        $duration = $options['duration'];
        $resolution = $options['resolution'];
        $user = $options['user'];
        $post = $options['post'];

        // Create temp directory for this video
        $tempDir = storage_path('app/temp/' . uniqid('video_'));
        mkdir($tempDir, 0755, true);

        try {
            // Step 1: Download all stock footage clips
            $localClips = $this->downloadClips($clips, $tempDir);

            // Step 2: Download voiceover
            $localVoiceover = $this->downloadFile($voiceoverUrl, $tempDir, 'voiceover.mp3');

            // Step 3: Download captions
            $localCaptions = $this->downloadFile($captionsUrl, $tempDir, 'captions.vtt');

            // Step 4: Create clips list file for FFmpeg
            $clipsListFile = $this->createClipsListFile($localClips, $tempDir);

            // Step 5: Concatenate video clips
            $concatenatedVideo = $tempDir . '/concatenated.mp4';
            $this->concatenateClips($clipsListFile, $concatenatedVideo, $resolution);

            // Step 6: Add voiceover audio
            $videoWithAudio = $tempDir . '/with_audio.mp4';
            $this->addAudio($concatenatedVideo, $localVoiceover, $videoWithAudio, $duration);

            // Step 7: Add captions overlay
            $videoWithCaptions = $tempDir . '/with_captions.mp4';
            $this->addCaptions($videoWithAudio, $localCaptions, $videoWithCaptions);

            // Step 8: Add branding (intro/outro) if Video Pro tier
            $finalVideo = $tempDir . '/final.mp4';
            if ($user->hasVideoProSubscription() && $this->hasCustomBranding($user)) {
                $this->addBranding($videoWithCaptions, $finalVideo, $user);
            } else {
                // Just copy the file
                copy($videoWithCaptions, $finalVideo);
            }

            // Step 9: Generate thumbnail
            $thumbnail = $tempDir . '/thumbnail.jpg';
            $this->generateThumbnail($finalVideo, $thumbnail);

            // Step 10: Upload to storage
            $videoUrl = $this->uploadToStorage($finalVideo, $post->id);
            $thumbnailUrl = $this->uploadToStorage($thumbnail, $post->id);

            // Step 11: Get file size
            $fileSizeMb = filesize($finalVideo) / 1024 / 1024;

            // Cleanup temp directory
            $this->cleanup($tempDir);

            return [
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'file_size_mb' => round($fileSizeMb, 2),
            ];

        } catch (Exception $e) {
            // Cleanup on error
            $this->cleanup($tempDir);
            throw $e;
        }
    }

    /**
     * Download all video clips
     */
    protected function downloadClips(array $clips, string $tempDir): array
    {
        $localClips = [];

        foreach ($clips as $index => $clip) {
            $filename = "clip_{$index}.mp4";
            $localPath = $this->downloadFile($clip['url'], $tempDir, $filename);
            $localClips[] = [
                'path' => $localPath,
                'duration' => $clip['duration'],
            ];
        }

        return $localClips;
    }

    /**
     * Download a file from URL
     */
    protected function downloadFile(string $url, string $tempDir, string $filename): string
    {
        // If it's already a local file (starts with /storage/)
        if (str_starts_with($url, '/storage/')) {
            return storage_path('app/public/' . str_replace('/storage/', '', $url));
        }

        // If it's a full URL, download it
        $content = file_get_contents($url);
        $localPath = $tempDir . '/' . $filename;
        file_put_contents($localPath, $content);

        return $localPath;
    }

    /**
     * Create FFmpeg clips list file
     */
    protected function createClipsListFile(array $clips, string $tempDir): string
    {
        $listFile = $tempDir . '/clips_list.txt';
        $content = '';

        foreach ($clips as $clip) {
            $content .= "file '" . $clip['path'] . "'\n";
        }

        file_put_contents($listFile, $content);

        return $listFile;
    }

    /**
     * Concatenate video clips using FFmpeg
     */
    protected function concatenateClips(string $listFile, string $output, string $resolution): void
    {
        list($width, $height) = explode('x', $resolution);

        $command = sprintf(
            'ffmpeg -f concat -safe 0 -i %s -vf "scale=%s:%s:force_original_aspect_ratio=increase,crop=%s:%s" -c:v libx264 -preset fast -crf 23 -y %s',
            escapeshellarg($listFile),
            $width,
            $height,
            $width,
            $height,
            escapeshellarg($output)
        );

        $this->executeFFmpeg($command);
    }

    /**
     * Add audio (voiceover) to video
     */
    protected function addAudio(string $videoPath, string $audioPath, string $output, int $duration): void
    {
        $command = sprintf(
            'ffmpeg -i %s -i %s -t %d -c:v copy -c:a aac -b:a 128k -map 0:v:0 -map 1:a:0 -shortest -y %s',
            escapeshellarg($videoPath),
            escapeshellarg($audioPath),
            $duration,
            escapeshellarg($output)
        );

        $this->executeFFmpeg($command);
    }

    /**
     * Add captions overlay to video
     */
    protected function addCaptions(string $videoPath, string $captionsPath, string $output): void
    {
        // Convert absolute path to relative for FFmpeg subtitle filter
        $captionsPathEscaped = str_replace(['\\', ':'], ['\\\\\\\\', '\\\\:'], $captionsPath);

        $command = sprintf(
            'ffmpeg -i %s -vf "subtitles=%s:force_style=\'FontName=Arial,FontSize=24,PrimaryColour=&HFFFFFF,OutlineColour=&H000000,BorderStyle=3,Outline=2,Shadow=0,Alignment=2\'" -c:a copy -y %s',
            escapeshellarg($videoPath),
            $captionsPathEscaped,
            escapeshellarg($output)
        );

        $this->executeFFmpeg($command);
    }

    /**
     * Add custom branding (intro/outro)
     */
    protected function addBranding(string $videoPath, string $output, User $user): void
    {
        $tempDir = dirname($videoPath);

        // Download custom intro/outro
        $intro = null;
        $outro = null;

        if ($user->custom_video_intro_url) {
            $intro = $this->downloadFile($user->custom_video_intro_url, $tempDir, 'intro.mp4');
        }

        if ($user->custom_video_outro_url) {
            $outro = $this->downloadFile($user->custom_video_outro_url, $tempDir, 'outro.mp4');
        }

        // If we have intro or outro, concatenate them
        if ($intro || $outro) {
            $listFile = $tempDir . '/branding_list.txt';
            $content = '';

            if ($intro) {
                $content .= "file '" . $intro . "'\n";
            }

            $content .= "file '" . $videoPath . "'\n";

            if ($outro) {
                $content .= "file '" . $outro . "'\n";
            }

            file_put_contents($listFile, $content);

            $command = sprintf(
                'ffmpeg -f concat -safe 0 -i %s -c copy -y %s',
                escapeshellarg($listFile),
                escapeshellarg($output)
            );

            $this->executeFFmpeg($command);
        } else {
            // No branding, just copy
            copy($videoPath, $output);
        }
    }

    /**
     * Generate thumbnail from video
     */
    protected function generateThumbnail(string $videoPath, string $output): void
    {
        // Extract frame at 2 seconds
        $command = sprintf(
            'ffmpeg -i %s -ss 00:00:02 -vframes 1 -vf "scale=1280:720:force_original_aspect_ratio=increase,crop=1280:720" -y %s',
            escapeshellarg($videoPath),
            escapeshellarg($output)
        );

        $this->executeFFmpeg($command);
    }

    /**
     * Execute FFmpeg command
     */
    protected function executeFFmpeg(string $command): void
    {
        $process = Process::run($command);

        if (!$process->successful()) {
            throw new Exception("FFmpeg command failed: " . $process->errorOutput());
        }
    }

    /**
     * Upload file to storage (S3, Cloudflare R2, or local)
     */
    protected function uploadToStorage(string $filePath, int $postId): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $filename = 'videos/' . $postId . '/' . uniqid() . '.' . $extension;

        // Upload to S3/R2 or local storage
        $disk = config('filesystems.default');
        Storage::disk($disk)->put($filename, file_get_contents($filePath));

        return Storage::disk($disk)->url($filename);
    }

    /**
     * Check if user has custom branding
     */
    protected function hasCustomBranding(User $user): bool
    {
        return !empty($user->custom_video_intro_url) || !empty($user->custom_video_outro_url);
    }

    /**
     * Cleanup temporary files
     */
    protected function cleanup(string $tempDir): void
    {
        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
    }

    /**
     * Recursively delete directory
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Check if FFmpeg is installed
     */
    public function checkFFmpegInstalled(): bool
    {
        try {
            $process = Process::run('ffmpeg -version');
            return $process->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get FFmpeg version
     */
    public function getFFmpegVersion(): ?string
    {
        try {
            $process = Process::run('ffmpeg -version');
            if ($process->successful()) {
                preg_match('/ffmpeg version ([^\s]+)/', $process->output(), $matches);
                return $matches[1] ?? null;
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }
}
