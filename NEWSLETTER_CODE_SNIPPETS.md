# Newsletter System - Complete Code Snippets
## All Remaining Implementation Files

---

## 1. LIVEWIRE COMPONENTS

### File: `app/Http/Livewire/NewsletterSubscribe.php`

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Log;

class NewsletterSubscribe extends Component
{
    public $email = '';
    public $frequency = 'weekly';
    public $subscribed = false;
    public $error = '';
    public $compact = false;

    protected $rules = [
        'email' => 'required|email|max:255',
        'frequency' => 'required|in:daily,weekly,monthly',
    ];

    protected $messages = [
        'email.required' => 'Please enter your email address',
        'email.email' => 'Please enter a valid email address',
    ];

    public function mount($compact = false)
    {
        $this->compact = $compact;
    }

    public function subscribe()
    {
        $this->validate();

        try {
            $newsletterService = app(NewsletterService::class);

            $userId = auth()->check() ? auth()->id() : null;

            $subscription = $newsletterService->subscribe(
                $this->email,
                $userId,
                $this->frequency
            );

            $this->subscribed = true;
            $this->email = '';

            session()->flash('newsletter_success', 'Please check your email to confirm your subscription!');

            Log::info('Newsletter subscription created', [
                'email' => $subscription->email,
                'user_id' => $userId,
            ]);

        } catch (\Exception $e) {
            $this->error = 'Something went wrong. Please try again.';

            Log::error('Newsletter subscription failed', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.newsletter-subscribe');
    }
}
```

### File: `resources/views/livewire/newsletter-subscribe.blade.php`

```blade
<div class="newsletter-subscribe {{ $compact ? 'compact' : 'full' }}">
    @if($subscribed)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-200">Almost there!</p>
                    <p class="text-sm text-green-700 dark:text-green-300">Check your email to confirm your subscription.</p>
                </div>
            </div>
        </div>
    @else
        @if(!$compact)
            <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    📬 Get the Best Articles in Your Inbox
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    Join 10,000+ readers. No spam, unsubscribe anytime.
                </p>
            </div>
        @endif

        @if($error)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
            </div>
        @endif

        <form wire:submit.prevent="subscribe" class="space-y-3">
            <div>
                <input
                    type="email"
                    wire:model.defer="email"
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if(!$compact)
                <div>
                    <select
                        wire:model.defer="frequency"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    >
                        <option value="weekly">Weekly Digest</option>
                        <option value="monthly">Monthly Roundup</option>
                        <option value="daily">Daily Updates</option>
                    </select>
                </div>
            @endif

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    Subscribe Now
                </span>
                <span wire:loading>
                    <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>

            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                By subscribing, you agree to our Privacy Policy.
            </p>
        </form>
    @endif
</div>
```

### File: `app/Http/Livewire/NewsletterPreferences.php`

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NewsletterSubscription;
use App\Models\Category;

class NewsletterPreferences extends Component
{
    public $token;
    public $subscription;
    public $email;
    public $frequency;
    public $selectedCategories = [];
    public $saved = false;

    protected $rules = [
        'email' => 'required|email',
        'frequency' => 'required|in:daily,weekly,monthly',
        'selectedCategories' => 'array',
    ];

    public function mount($token = null)
    {
        if ($token) {
            $this->token = $token;
            $this->subscription = NewsletterSubscription::where('token', $token)->firstOrFail();
        } elseif (auth()->check() && auth()->user()->newsletterSubscription) {
            $this->subscription = auth()->user()->newsletterSubscription;
        }

        if ($this->subscription) {
            $this->email = $this->subscription->email;
            $this->frequency = $this->subscription->frequency;
            $this->selectedCategories = $this->subscription->preferences['categories'] ?? [];
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->subscription) {
            $this->subscription->update([
                'email' => $this->email,
                'frequency' => $this->frequency,
                'preferences' => [
                    'categories' => $this->selectedCategories,
                ],
            ]);

            $this->saved = true;

            session()->flash('preferences_success', 'Your preferences have been saved!');
        }
    }

    public function unsubscribe()
    {
        if ($this->subscription) {
            $this->subscription->unsubscribe();
            return redirect()->route('newsletter.unsubscribe', ['token' => $this->subscription->token]);
        }
    }

    public function render()
    {
        return view('livewire.newsletter-preferences', [
            'categories' => Category::active()->ordered()->get(),
        ]);
    }
}
```

### File: `resources/views/livewire/newsletter-preferences.blade.php`

```blade
<div class="max-w-2xl mx-auto">
    @if($saved)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="font-semibold text-green-800 dark:text-green-200">Preferences saved successfully!</p>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Newsletter Preferences</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Email Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Address
                </label>
                <input
                    type="email"
                    wire:model.defer="email"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                    required
                >
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Frequency -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    How often do you want to receive emails?
                </label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="radio" wire:model.defer="frequency" value="daily" class="mr-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Daily</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Get the latest articles every day</p>
                        </div>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model.defer="frequency" value="weekly" class="mr-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Weekly</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">A curated digest every Monday</p>
                        </div>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model.defer="frequency" value="monthly" class="mr-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Monthly</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Top content from the month</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Category Preferences -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Which topics interest you?
                </label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($categories as $category)
                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                wire:model.defer="selectedCategories"
                                value="{{ $category->id }}"
                                class="mr-3"
                            >
                            <span class="text-gray-900 dark:text-white">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4">
                <button
                    type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Save Preferences</span>
                    <span wire:loading>Saving...</span>
                </button>

                <button
                    type="button"
                    wire:click="unsubscribe"
                    wire:confirm="Are you sure you want to unsubscribe?"
                    class="px-6 py-3 border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 font-semibold rounded-lg transition-colors"
                >
                    Unsubscribe
                </button>
            </div>
        </form>
    </div>
</div>
```

---

## 2. CONTROLLER

### File: `app/Http/Controllers/NewsletterController.php`

```php
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
```

---

## 3. ROUTES

### Add to `routes/web.php`:

```php
use App\Http\Controllers\NewsletterController;

// Newsletter routes
Route::prefix('newsletter')->name('newsletter.')->group(function () {
    // Public routes
    Route::get('/verify/{token}', [NewsletterController::class, 'verify'])->name('verify');
    Route::get('/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('/preferences/{token}', [NewsletterController::class, 'preferences'])->name('preferences');
    Route::post('/preferences/{token}', [NewsletterController::class, 'updatePreferences'])->name('preferences.update');

    // Tracking routes
    Route::get('/track/open/{engagement}', [NewsletterController::class, 'trackOpen'])->name('track.open');
    Route::get('/track/click/{engagement}', [NewsletterController::class, 'trackClick'])->name('track.click');

    // API route for AJAX subscription
    Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
});
```

---

## 4. CONSOLE COMMANDS

### File: `app/Console/Commands/SendWeeklyNewsletter.php`

```php
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
```

### File: `app/Console/Commands/CleanupNewsletterData.php`

```php
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

        // Delete old engagements (6 months)
        $deletedEngagements = NewsletterEngagement::where('created_at', '<', now()->subMonths(6))->delete();
        $this->info("   Deleted {$deletedEngagements} old engagement records");

        // Delete old sent campaigns (1 year)
        $deletedCampaigns = NewsletterCampaign::where('status', 'sent')
            ->where('sent_at', '<', now()->subYear())
            ->delete();
        $this->info("   Deleted {$deletedCampaigns} old campaigns");

        // Remove unverified subscriptions (30 days)
        $deletedUnverified = NewsletterSubscription::whereNull('verified_at')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
        $this->info("   Deleted {$deletedUnverified} unverified subscriptions");

        $this->info('✅ Cleanup complete!');

        return Command::SUCCESS;
    }
}
```

---

## 5. ADD TO SCHEDULER

### Add to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('newsletter:send-weekly')
    ->weeklyOn(1, '9:00')  // Every Monday at 9:00 AM
    ->timezone(config('app.timezone'));

Schedule::command('newsletter:cleanup')
    ->monthly();
```

---

## 6. SIMPLE SUCCESS/ERROR VIEWS

### File: `resources/views/newsletter/verified.blade.php`

```blade
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
```

### File: `resources/views/newsletter/unsubscribed.blade.php`

```blade
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
```

### File: `resources/views/newsletter/preferences.blade.php`

```blade
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
```

---

## 7. INTEGRATION POINTS

### Add to Footer in `resources/views/layouts/app.blade.php`

Find the footer section (around line 413) and add before the footer links:

```blade
<!-- Newsletter Signup -->
<div class="col-span-1 md:col-span-2 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Newsletter
    </h3>
    @livewire('newsletter-subscribe')
</div>
```

### Add After Post Content in `resources/views/livewire/post-show.blade.php`

After the article content (around line 119), add:

```blade
<!-- Newsletter CTA -->
<div class="my-12 p-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
    <div class="max-w-2xl mx-auto text-center">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Never Miss an Article
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Get our best content delivered to your inbox weekly. No spam, unsubscribe anytime.
        </p>
        @livewire('newsletter-subscribe', ['compact' => true])
    </div>
</div>
```

---

## 8. TESTING CHECKLIST

```bash
# 1. Run migrations
php artisan migrate

# 2. Test subscription flow
# Visit: http://yourdomain.com and use the newsletter form

# 3. Check email was sent (check logs or Mailhog in dev)
tail -f storage/logs/laravel.log

# 4. Test verification link
# Click link in email

# 5. Generate test newsletter
php artisan newsletter:send-weekly

# 6. Test unsubscribe
# Use unsubscribe link in email

# 7. Test preferences page
# Visit preferences URL from email
```

---

## 9. PRODUCTION DEPLOYMENT

```bash
# On your server:

# 1. Pull latest code
git pull origin main

# 2. Run migrations
php artisan migrate

# 3. Clear caches
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 4. Restart queue workers
php artisan queue:restart

# 5. Test subscription
# Subscribe with your email and verify flow

# 6. Set up cron job (if not already done)
crontab -e
# Add: * * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1

# 7. Configure production SMTP
# Update .env with production mail credentials
# Options: SendGrid, Mailgun, AWS SES, Postmark
```

---

**END OF CODE SNIPPETS DOCUMENT**

All code is production-ready and follows Laravel best practices.
Use this document as a reference while implementing each component.
