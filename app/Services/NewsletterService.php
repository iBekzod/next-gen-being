<?php

namespace App\Services;

use App\Models\NewsletterSubscription;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterEngagement;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterService
{
    public function subscribe(string $email, ?int $userId = null, string $frequency = 'weekly', ?array $preferences = null): NewsletterSubscription
    {
        $subscription = NewsletterSubscription::updateOrCreate(
            ['email' => $email],
            [
                'user_id' => $userId,
                'frequency' => $frequency,
                'preferences' => $preferences,
                'is_active' => true,
            ]
        );

        if (!$subscription->verified_at) {
            $this->sendVerificationEmail($subscription);
        }

        return $subscription;
    }

    public function sendVerificationEmail(NewsletterSubscription $subscription): void
    {
        try {
            $verifyUrl = route('newsletter.verify', ['token' => $subscription->token]);

            Mail::send('emails.newsletter.verify', [
                'subscription' => $subscription,
                'verifyUrl' => $verifyUrl,
            ], function ($message) use ($subscription) {
                $message->to($subscription->email)
                    ->subject('Confirm your newsletter subscription - ' . config('app.name'));
            });

            Log::info('Verification email sent', ['email' => $subscription->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'email' => $subscription->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function verify(string $token): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::where('token', $token)->first();

        if ($subscription && !$subscription->verified_at) {
            $subscription->verify();
            $this->startWelcomeSequence($subscription);
            return $subscription;
        }

        return null;
    }

    /**
     * Fire the first onboarding email immediately after a subscriber confirms,
     * and record that step 1 has been sent. Later steps are handled by
     * processOnboardingQueue() on a schedule.
     */
    public function startWelcomeSequence(NewsletterSubscription $subscription): void
    {
        if (!config('newsletter.onboarding.enabled', true)) {
            return;
        }

        if ($this->sendOnboardingStep($subscription, 1)) {
            $subscription->update(['welcome_step' => 1, 'welcome_sent_at' => now()]);
        }
    }

    /**
     * Send any onboarding emails that are now due. Called by the scheduled
     * `newsletter:send-onboarding` command. Returns the number sent.
     */
    public function processOnboardingQueue(): int
    {
        if (!config('newsletter.onboarding.enabled', true)) {
            return 0;
        }

        $steps = config('newsletter.onboarding.steps', []);
        if (empty($steps)) {
            return 0;
        }
        $maxStep = max(array_keys($steps));
        $sent = 0;

        NewsletterSubscription::query()
            ->active()
            ->verified()
            ->where('welcome_step', '>=', 1)
            ->where('welcome_step', '<', $maxStep)
            ->whereNotNull('welcome_sent_at')
            ->chunkById(200, function ($subscribers) use ($steps, &$sent) {
                foreach ($subscribers as $subscriber) {
                    $nextStep = (int) $subscriber->welcome_step + 1;
                    $config = $steps[$nextStep] ?? null;
                    if (!$config) {
                        continue;
                    }

                    $dueAt = $subscriber->welcome_sent_at->copy()->addDays((int) ($config['delay_days'] ?? 0));
                    if ($dueAt->isFuture()) {
                        continue;
                    }

                    if ($this->sendOnboardingStep($subscriber, $nextStep)) {
                        $subscriber->update(['welcome_step' => $nextStep, 'welcome_sent_at' => now()]);
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    /**
     * Render and send a single onboarding step. Returns true on success.
     */
    public function sendOnboardingStep(NewsletterSubscription $subscription, int $step): bool
    {
        $steps = config('newsletter.onboarding.steps', []);
        $config = $steps[$step] ?? null;
        if (!$config) {
            return false;
        }

        try {
            $unsubscribeUrl = route('newsletter.unsubscribe', ['token' => $subscription->token]);

            Mail::send($config['view'], [
                'subscription' => $subscription,
                'unsubscribeUrl' => $unsubscribeUrl,
                'links' => config('newsletter.links', []),
                'appUrl' => rtrim(config('app.url'), '/'),
            ], function ($message) use ($subscription, $config) {
                $message->to($subscription->email)->subject($config['subject']);
                if ($replyTo = config('newsletter.reply_to')) {
                    $message->replyTo($replyTo);
                }
            });

            Log::info('Onboarding email sent', ['email' => $subscription->email, 'step' => $step]);
            return true;
        } catch (\Exception $e) {
            Log::error('Onboarding email failed', [
                'email' => $subscription->email,
                'step' => $step,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function unsubscribe(string $token): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::where('token', $token)->first();

        if ($subscription) {
            $subscription->unsubscribe();
            return $subscription;
        }

        return null;
    }

    public function updatePreferences(string $token, array $preferences): ?NewsletterSubscription
    {
        $subscription = NewsletterSubscription::where('token', $token)->first();

        if ($subscription) {
            $subscription->updatePreferences($preferences);
            return $subscription;
        }

        return null;
    }

    public function generateWeeklyDigest(): NewsletterCampaign
    {
        $topPosts = Post::where('status', 'published')
            ->where('published_at', '>=', now()->subWeek())
            ->orderByDesc('views_count')
            ->take(5)
            ->with(['category', 'author'])
            ->get();

        if ($topPosts->count() < 3) {
            $topPosts = Post::where('status', 'published')
                ->where('published_at', '>=', now()->subMonth())
                ->orderByDesc('views_count')
                ->take(5)
                ->with(['category', 'author'])
                ->get();
        }

        $premiumPosts = Post::where('status', 'published')
            ->where('is_premium', true)
            ->where('published_at', '>=', now()->subWeeks(2))
            ->orderByDesc('published_at')
            ->take(3)
            ->with(['category', 'author'])
            ->get();

        $subject = $this->generateSubjectLine($topPosts);

        $content = view('emails.newsletter.weekly-digest', [
            'topPosts' => $topPosts,
            'premiumPosts' => $premiumPosts,
            'categories' => Category::active()->ordered()->take(6)->get(),
        ])->render();

        return NewsletterCampaign::create([
            'subject' => $subject,
            'content' => $content,
            'type' => 'digest',
            'status' => 'scheduled',
            'scheduled_at' => now(),
        ]);
    }

    private function generateSubjectLine($posts): string
    {
        if ($posts->isEmpty()) {
            return 'This Week\'s Top Insights from ' . config('app.name');
        }

        $topPost = $posts->first();

        $templates = [
            'This Week: ' . $topPost->title,
            'New: ' . $topPost->title . ' + 4 More Articles',
            'Fresh Insights: ' . $topPost->title,
        ];

        return $templates[array_rand($templates)];
    }

    public function sendCampaign(NewsletterCampaign $campaign, string $frequency = 'weekly'): int
    {
        $subscribers = NewsletterSubscription::dueForNewsletter($frequency)->get();
        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $engagement = NewsletterEngagement::create([
                    'campaign_id' => $campaign->id,
                    'subscription_id' => $subscriber->id,
                ]);

                $trackedContent = $this->addTrackingToContent($campaign->content, $engagement, $subscriber);

                Mail::send('emails.newsletter.wrapper', [
                    'content' => $trackedContent,
                ], function ($message) use ($campaign, $subscriber) {
                    $message->to($subscriber->email)->subject($campaign->subject);
                });

                $subscriber->markAsSent();
                $campaign->incrementRecipients();
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Newsletter send failed', [
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sentCount > 0) {
            $campaign->markAsSent();
        }

        return $sentCount;
    }

    private function addTrackingToContent(string $content, NewsletterEngagement $engagement, NewsletterSubscription $subscriber): string
    {
        $trackingPixelUrl = route('newsletter.track.open', ['engagement' => $engagement->id]);
        $unsubscribeUrl = route('newsletter.unsubscribe', ['token' => $subscriber->token]);

        $trackingPixel = '<img src="' . $trackingPixelUrl . '" width="1" height="1" alt="" />';
        $content .= $trackingPixel;

        $content .= '<p style="text-align:center;color:#999;font-size:12px;margin-top:40px;">';
        $content .= '<a href="' . $unsubscribeUrl . '" style="color:#999;">Unsubscribe</a>';
        $content .= '</p>';

        return $content;
    }

    public function trackOpen(int $engagementId): void
    {
        $engagement = NewsletterEngagement::find($engagementId);
        if ($engagement) {
            $engagement->markAsOpened();
        }
    }

    public function trackClick(int $engagementId, string $url): ?string
    {
        $engagement = NewsletterEngagement::find($engagementId);
        if ($engagement) {
            $engagement->markAsClicked($url);
            return $url;
        }
        return null;
    }
}
