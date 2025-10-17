#!/bin/bash

# Complete Newsletter System Setup Script
# This script completes the remaining 20% of newsletter implementation
# Run this script from the project root directory

set -e  # Exit on error

echo "🚀 Completing Newsletter System Setup..."
echo "==========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the project root."
    exit 1
fi

# 1. Create NewsletterController
echo "📝 Creating NewsletterController..."
cat > app/Http/Controllers/NewsletterController.php << 'ENDCONTROLLER'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewsletterService;
use App\Models\NewsletterEngagement;

class NewsletterController extends Controller
{
    protected $newsletterService;

    public function __construct(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'frequency' => 'sometimes|in:daily,weekly,monthly',
        ]);

        $subscription = $this->newsletterService->subscribe(
            $request->email,
            auth()->id(),
            $request->input('frequency', 'weekly')
        );

        return response()->json([
            'success' => true,
            'message' => 'Please check your email to confirm your subscription.',
        ]);
    }

    public function verify($token)
    {
        $subscription = $this->newsletterService->verify($token);

        if ($subscription) {
            return view('newsletter.verified', compact('subscription'));
        }

        return view('newsletter.verify-failed');
    }

    public function unsubscribe($token)
    {
        $subscription = $this->newsletterService->unsubscribe($token);

        if ($subscription) {
            return view('newsletter.unsubscribed', compact('subscription'));
        }

        return redirect()->route('home')->with('error', 'Invalid unsubscribe link.');
    }

    public function preferences($token)
    {
        return view('newsletter.preferences', compact('token'));
    }

    public function updatePreferences(Request $request, $token)
    {
        $request->validate([
            'email' => 'required|email',
            'frequency' => 'required|in:daily,weekly,monthly',
            'categories' => 'sometimes|array',
        ]);

        $subscription = $this->newsletterService->updatePreferences($token, [
            'email' => $request->email,
            'frequency' => $request->frequency,
            'categories' => $request->input('categories', []),
        ]);

        if ($subscription) {
            return redirect()->back()->with('success', 'Preferences updated successfully!');
        }

        return redirect()->back()->with('error', 'Failed to update preferences.');
    }

    public function trackOpen($engagementId)
    {
        $this->newsletterService->trackOpen($engagementId);

        // Return 1x1 transparent pixel
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function trackClick($engagementId, Request $request)
    {
        $url = urldecode($request->query('url'));

        $redirectUrl = $this->newsletterService->trackClick($engagementId, $url);

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        return redirect()->route('home');
    }
}
ENDCONTROLLER

echo "✅ NewsletterController created"

# 2. Add routes to web.php
echo "🛣️  Adding newsletter routes..."

# Check if routes already exist
if grep -q "newsletter.verify" routes/web.php; then
    echo "⚠️  Newsletter routes already exist, skipping..."
else
    cat >> routes/web.php << 'ENDROUTES'

// Newsletter Routes
use App\Http\Controllers\NewsletterController;

Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::get('/verify/{token}', [NewsletterController::class, 'verify'])->name('verify');
    Route::get('/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('/preferences/{token}', [NewsletterController::class, 'preferences'])->name('preferences');
    Route::post('/preferences/{token}', [NewsletterController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/track/open/{engagement}', [NewsletterController::class, 'trackOpen'])->name('track.open');
    Route::get('/track/click/{engagement}', [NewsletterController::class, 'trackClick'])->name('track.click');
    Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
});
ENDROUTES
    echo "✅ Routes added to web.php"
fi

# 3. Create SendWeeklyNewsletter command
echo "⚙️  Creating SendWeeklyNewsletter command..."
cat > app/Console/Commands/SendWeeklyNewsletter.php << 'ENDCOMMAND'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsletterService;

class SendWeeklyNewsletter extends Command
{
    protected $signature = 'newsletter:send-weekly';
    protected $description = 'Send weekly newsletter to all weekly subscribers';

    public function handle(NewsletterService $newsletterService)
    {
        $this->info('🚀 Generating weekly newsletter campaign...');

        $campaign = $newsletterService->generateWeeklyDigest();

        $this->info('📧 Sending to subscribers...');

        $sentCount = $newsletterService->sendCampaign($campaign, 'weekly');

        $this->info("✅ Newsletter sent to {$sentCount} subscribers!");

        return Command::SUCCESS;
    }
}
ENDCOMMAND

echo "✅ SendWeeklyNewsletter command created"

# 4. Create CleanupNewsletterData command
echo "🧹 Creating CleanupNewsletterData command..."
cat > app/Console/Commands/CleanupNewsletterData.php << 'ENDCOMMAND'
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsletterSubscription;
use App\Models\NewsletterEngagement;
use App\Models\NewsletterCampaign;

class CleanupNewsletterData extends Command
{
    protected $signature = 'newsletter:cleanup';
    protected $description = 'Cleanup old newsletter data';

    public function handle()
    {
        $this->info('🧹 Cleaning up old newsletter data...');

        $deletedEngagements = NewsletterEngagement::where('created_at', '<', now()->subMonths(6))->delete();
        $this->info("   Deleted {$deletedEngagements} old engagement records");

        $deletedCampaigns = NewsletterCampaign::where('status', 'sent')
            ->where('sent_at', '<', now()->subYear())
            ->delete();
        $this->info("   Deleted {$deletedCampaigns} old campaigns");

        $deletedUnverified = NewsletterSubscription::whereNull('verified_at')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
        $this->info("   Deleted {$deletedUnverified} unverified subscriptions");

        $this->info('✅ Cleanup complete!');

        return Command::SUCCESS;
    }
}
ENDCOMMAND

echo "✅ CleanupNewsletterData command created"

# 5. Update scheduler in console.php
echo "📅 Updating scheduler configuration..."
if grep -q "newsletter:send-weekly" routes/console.php; then
    echo "⚠️  Scheduler already configured, skipping..."
else
    cat >> routes/console.php << 'ENDSCHEDULE'

// Newsletter Automation
Schedule::command('newsletter:send-weekly')
    ->weeklyOn(1, '9:00')
    ->timezone(config('app.timezone'));

Schedule::command('newsletter:cleanup')
    ->monthly();
ENDSCHEDULE
    echo "✅ Scheduler configuration added"
fi

# 6. Create newsletter views directory
echo "📄 Creating newsletter views..."
mkdir -p resources/views/newsletter

# 7. Create verified view
cat > resources/views/newsletter/verified.blade.php << 'ENDVIEW'
@extends('layouts.app')

@section('title', 'Subscription Confirmed')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            You're all set!
        </h1>

        <p class="text-gray-600 dark:text-gray-400 mb-8">
            Your newsletter subscription has been confirmed. You'll receive our {{ $subscription->frequency }} digest starting soon.
        </p>

        <div class="space-y-3">
            <a href="{{ route('home') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
                Go to Homepage
            </a>

            <a href="{{ route('newsletter.preferences', $subscription->token) }}" class="block w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-semibold px-6 py-3 rounded-lg transition-colors">
                Manage Preferences
            </a>
        </div>
    </div>
</div>
@endsection
ENDVIEW

# 8. Create unsubscribed view
cat > resources/views/newsletter/unsubscribed.blade.php << 'ENDVIEW'
@extends('layouts.app')

@section('title', 'Unsubscribed')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            You've been unsubscribed
        </h1>

        <p class="text-gray-600 dark:text-gray-400 mb-8">
            We're sorry to see you go. You won't receive any more emails from us.
        </p>

        <p class="text-sm text-gray-500 dark:text-gray-500 mb-8">
            Changed your mind? You can always subscribe again from our homepage.
        </p>

        <a href="{{ route('home') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors">
            Go to Homepage
        </a>
    </div>
</div>
@endsection
ENDVIEW

# 9. Create preferences view
cat > resources/views/newsletter/preferences.blade.php << 'ENDVIEW'
@extends('layouts.app')

@section('title', 'Newsletter Preferences')

@section('content')
<div class="min-h-screen py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
            Newsletter Preferences
        </h1>

        @livewire('newsletter-preferences', ['token' => $token])
    </div>
</div>
@endsection
ENDVIEW

echo "✅ Newsletter views created"

# 10. Create premium teaser email template
cat > resources/views/emails/newsletter/premium-teaser.blade.php << 'ENDVIEW'
@extends('emails.newsletter.layouts.base')

@section('content')
<h1>✨ Unlock Exclusive Premium Content</h1>

<p>Hi there!</p>

<p>You're missing out on some incredible insights. Our premium members get access to in-depth articles, tutorials, and exclusive content that can accelerate your learning.</p>

@if(isset($premiumPosts) && $premiumPosts->isNotEmpty())
    <h2>Here's what you're missing this week:</h2>

    @foreach($premiumPosts as $post)
        <div class="post-card" style="position: relative;">
            @if($post->featured_image)
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" style="width: 100%; height: auto; border-radius: 6px; margin-bottom: 15px; opacity: 0.6;">
            @endif

            <div class="post-meta">
                <span class="category-badge">{{ $post->category->name }}</span>
                <span class="premium-badge">Premium</span>
                <span style="color: #a0aec0;">• {{ $post->read_time }} min read</span>
            </div>

            <h3>🔒 {{ $post->title }}</h3>

            <p style="color: #718096;">{{ Str::limit($post->excerpt, 120) }}</p>

            <p style="font-size: 14px; color: #a0aec0; font-style: italic;">
                Upgrade to read the full article...
            </p>
        </div>
    @endforeach
@endif

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 40px; margin: 40px 0; text-align: center; color: white;">
    <h2 style="color: white; margin-top: 0;">🎁 Special Offer for You</h2>

    <p style="color: #e0e7ff; font-size: 18px; margin-bottom: 10px;">
        <strong>Get 20% OFF Premium Membership</strong>
    </p>

    <p style="color: #c7d2fe; margin-bottom: 30px;">
        Use code <span style="background-color: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px; font-weight: 600;">NEWSLETTER20</span> at checkout
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
        <tr>
            <td>
                <a href="{{ isset($subscriptionUrl) ? $subscriptionUrl : url('/subscription/plans') }}"
                   style="display: inline-block; padding: 18px 48px; background-color: white; color: #667eea; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 18px;">
                    Upgrade Now →
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #c7d2fe; font-size: 14px; margin-top: 20px; margin-bottom: 0;">
        ✓ Cancel anytime • ✓ 30-day money-back guarantee • ✓ Instant access
    </p>
</div>

<div style="background-color: #f7fafc; border-radius: 8px; padding: 30px; margin: 30px 0;">
    <h3 style="margin-top: 0;">What Premium Members Get:</h3>
    <ul style="margin: 10px 0; padding-left: 20px; color: #4a5568;">
        <li style="margin-bottom: 10px;">📚 Access to 50+ premium articles and tutorials</li>
        <li style="margin-bottom: 10px;">🎯 Advanced topics and in-depth guides</li>
        <li style="margin-bottom: 10px;">💾 Downloadable resources and code templates</li>
        <li style="margin-bottom: 10px;">⚡ Early access to new content</li>
        <li style="margin-bottom: 10px;">🎓 Learning paths and structured courses</li>
        <li style="margin-bottom: 10px;">💬 Priority support from our team</li>
    </ul>
</div>

<div style="text-align: center; padding: 20px 0;">
    <p style="font-size: 16px; color: #2d3748; margin-bottom: 15px;">
        <strong>Join 10,000+ premium members who are accelerating their learning.</strong>
    </p>
    <p style="font-size: 14px; color: #718096;">
        "Best investment I made this year. The premium content is gold!" - Sarah K.
    </p>
</div>

@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    Not interested? No problem! You'll continue receiving our free newsletter.
</p>
@endsection
ENDVIEW

echo "✅ Premium teaser template created"

# 11. Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# 12. Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "================================"
echo "✅ Newsletter System Setup Complete!"
echo "================================"
echo ""
echo "📋 NEXT STEPS:"
echo ""
echo "1. Add newsletter widget to footer:"
echo "   Edit: resources/views/layouts/app.blade.php"
echo "   Add: @livewire('newsletter-subscribe')"
echo ""
echo "2. Add newsletter CTA to blog posts:"
echo "   Edit: resources/views/livewire/post-show.blade.php"
echo "   Add: @livewire('newsletter-subscribe', ['compact' => true])"
echo ""
echo "3. Test the subscription flow:"
echo "   - Visit your homepage"
echo "   - Subscribe with your email"
echo "   - Check email for verification"
echo "   - Click verification link"
echo ""
echo "4. Test sending newsletter:"
echo "   php artisan newsletter:send-weekly"
echo ""
echo "5. Set up cron job (if not already done):"
echo "   crontab -e"
echo "   Add: * * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "📚 Documentation:"
echo "   - NEWSLETTER_IMPLEMENTATION_PLAN.md"
echo "   - NEWSLETTER_CODE_SNIPPETS.md"
echo ""
echo "🎉 Your newsletter system is ready to go!"
