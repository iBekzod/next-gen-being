<?php

namespace App\Filament\Blogger\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.blogger.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();

        // Get counts
        $draftPosts = \App\Models\Post::where('author_id', $user->id)
            ->where('status', 'draft')
            ->count();

        $publishedPosts = \App\Models\Post::where('author_id', $user->id)
            ->where('status', 'published')
            ->count();

        $connectedAccounts = \App\Models\SocialMediaAccount::where('user_id', $user->id)
            ->count();

        $activeJobs = \App\Models\JobStatus::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $failedJobs = \App\Models\JobStatus::where('user_id', $user->id)
            ->where('status', 'failed')
            ->count();

        // Check if user has posts that can generate videos
        $postsReadyForVideo = \App\Models\Post::where('author_id', $user->id)
            ->where('status', 'published')
            ->whereDoesntHave('videoGenerations', function ($query) {
                $query->where('status', 'completed');
            })
            ->count();

        // Check if user has videos ready to publish
        $videosReadyToPublish = \App\Models\VideoGeneration::whereHas('post', function ($query) use ($user) {
                $query->where('author_id', $user->id);
            })
            ->where('status', 'completed')
            ->whereDoesntHave('post.socialMediaPosts', function ($query) {
                $query->where('status', 'published');
            })
            ->count();

        return [
            'draftPosts' => $draftPosts,
            'publishedPosts' => $publishedPosts,
            'connectedAccounts' => $connectedAccounts,
            'activeJobs' => $activeJobs,
            'failedJobs' => $failedJobs,
            'postsReadyForVideo' => $postsReadyForVideo,
            'videosReadyToPublish' => $videosReadyToPublish,
            'aiQuotaUsed' => $user->ai_posts_used ?? 0,
            'aiQuotaLimit' => $user->ai_posts_limit ?? 'unlimited',
        ];
    }
}
