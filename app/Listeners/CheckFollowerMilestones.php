<?php

namespace App\Listeners;

use App\Events\UserFollowed;
use App\Models\BloggerEarning;
use App\Notifications\MilestoneAchievedNotification;
use App\Notifications\NewFollowerNotification;
use App\Services\BloggerMonetizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckFollowerMilestones implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        protected BloggerMonetizationService $monetizationService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(UserFollowed $event): void
    {
        $blogger = $event->followedUser;
        $follower = $event->follower;

        // Check if the followed user is a blogger
        if (!$blogger->hasRole('blogger')) {
            return;
        }

        // Send new follower notification to the blogger
        $blogger->notify(new NewFollowerNotification($follower));

        // Check and award follower milestones
        $awarded = $this->monetizationService->checkFollowerMilestones($blogger);

        if (!empty($awarded)) {
            Log::info('Follower milestones automatically awarded', [
                'blogger_id' => $blogger->id,
                'follower_id' => $follower->id,
                'milestones_awarded' => $awarded,
            ]);

            // Send notification for each milestone achieved
            foreach ($awarded as $milestoneData) {
                $earning = BloggerEarning::find($milestoneData['earning_id']);
                if ($earning) {
                    $blogger->notify(new MilestoneAchievedNotification(
                        $milestoneData['milestone'],
                        $milestoneData['amount'],
                        $earning
                    ));
                }
            }
        }
    }
}
