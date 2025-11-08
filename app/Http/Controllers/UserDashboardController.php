<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_posts' => $user->posts()->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'total_views' => $user->posts()->sum('views_count'),
            'total_likes' => $user->posts()->sum('likes_count'),
            'total_comments' => $user->posts()->sum('comments_count'),
            'bookmarks' => $user->interactions()->where('type', 'bookmark')->count(),
        ];

        return view('dashboard.index', compact('stats'));
    }

    /**
     * Display the user's posts.
     */
    public function posts(Request $request): View
    {
        $filter = $request->get('filter', 'all');

        return view('dashboard.posts', [
            'filter' => $filter,
        ]);
    }

    /**
     * Display the user's bookmarked posts.
     */
    public function bookmarks(): View
    {
        return view('dashboard.bookmarks');
    }

    /**
     * Display the user earnings dashboard.
     */
    public function earnings(): View
    {
        $user = Auth::user();

        // Get earnings data
        $totalEarnings = $user->earnings()->paid()->sum('amount');
        $thisMonthEarnings = $user->earnings()
            ->paid()
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');
        $pendingPayouts = $user->earnings()->pending()->sum('amount');

        // Get top earning posts (from earnings metadata)
        $topPosts = $user->earnings()
            ->paid()
            ->where('type', 'premium_content')
            ->orderBy('amount', 'desc')
            ->limit(5)
            ->get();

        // Get earnings by type
        $earningsByType = $user->earnings()
            ->paid()
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        // Get 30-day earnings history for chart
        $thirtyDaysAgo = now()->subDays(30);
        $dailyEarnings = $user->earnings()
            ->paid()
            ->where('paid_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get pending payout requests
        $payoutRequests = $user->payoutRequests()
            ->where('status', '!=', 'rejected')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.earnings', compact(
            'user',
            'totalEarnings',
            'thisMonthEarnings',
            'pendingPayouts',
            'topPosts',
            'earningsByType',
            'dailyEarnings',
            'payoutRequests'
        ));
    }

    /**
     * Display the payout management dashboard.
     */
    public function payouts(): View
    {
        $user = Auth::user();

        // Get payout statistics
        $totalRequested = $user->payoutRequests()->sum('amount');
        $totalPaid = $user->payoutRequests()->where('status', 'completed')->sum('amount');
        $pendingCount = $user->payoutRequests()->where('status', 'pending')->count();
        $rejectedCount = $user->payoutRequests()->where('status', 'rejected')->count();

        // Calculate average payout
        $completedPayouts = $user->payoutRequests()->where('status', 'completed')->count();
        $averagePayout = $completedPayouts > 0 ? $totalPaid / $completedPayouts : 0;

        // Get all payout requests with pagination
        $payoutRequests = $user->payoutRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get payout method statistics
        $payoutMethods = $user->payoutRequests()
            ->selectRaw('payout_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payout_method')
            ->get();

        // Get monthly payout trend
        $thirtyDaysAgo = now()->subDays(30);
        $monthlyPayouts = $user->payoutRequests()
            ->where('status', 'completed')
            ->where('processed_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(processed_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get tax information (if any invoices exist)
        $invoices = $user->invoices();

        return view('dashboard.payouts', compact(
            'user',
            'totalRequested',
            'totalPaid',
            'pendingCount',
            'rejectedCount',
            'averagePayout',
            'payoutRequests',
            'payoutMethods',
            'monthlyPayouts',
            'invoices'
        ));
    }

    /**
     * Display the video management dashboard.
     */
    public function videos(): View
    {
        $user = Auth::user();

        // Get video statistics
        $totalVideos = $user->videoGenerations()->count();
        $completedVideos = $user->videoGenerations()->completed()->count();
        $processingCount = $user->videoGenerations()->processing()->count();
        $failedCount = $user->videoGenerations()->failed()->count();
        $queuedCount = $user->videoGenerations()->queued()->count();

        // Calculate total AI credits used
        $totalCreditsUsed = $user->videoGenerations()->sum('ai_credits_used');
        $totalGenerationCost = $user->videoGenerations()->sum('generation_cost');

        // Get video quality breakdown
        $videosByType = $user->videoGenerations()
            ->selectRaw('video_type, COUNT(*) as count, SUM(file_size_mb) as total_size')
            ->groupBy('video_type')
            ->get();

        // Get recent videos with their posts
        $recentVideos = $user->videoGenerations()
            ->with('post')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get processing timeline (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $processingTimeline = $user->videoGenerations()
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count, SUM(file_size_mb) as size')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.videos', compact(
            'user',
            'totalVideos',
            'completedVideos',
            'processingCount',
            'failedCount',
            'queuedCount',
            'totalCreditsUsed',
            'totalGenerationCost',
            'videosByType',
            'recentVideos',
            'processingTimeline'
        ));
    }

    /**
     * Display the social media manager dashboard.
     */
    public function socialMedia(): View
    {
        $user = Auth::user();

        // Get connected accounts
        $connectedAccounts = $user->socialMediaAccounts()->where('is_active', true)->get();
        $totalAccounts = $user->socialMediaAccounts()->count();
        $activeAccounts = $connectedAccounts->count();
        $expiredTokens = $connectedAccounts->filter(fn($acc) => $acc->isTokenExpired())->count();

        // Get accounts by platform
        $accountsByPlatform = $user->socialMediaAccounts()
            ->selectRaw('platform, COUNT(*) as count, SUM(follower_count) as total_followers')
            ->groupBy('platform')
            ->get();

        // Get auto-publishing status
        $autoPublishEnabled = $connectedAccounts->filter(fn($acc) => $acc->auto_publish)->count();

        // Get all social media accounts with pagination
        $socialMediaAccounts = $user->socialMediaAccounts()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get publishing schedule info
        $accountsWithSchedule = $connectedAccounts->filter(fn($acc) => !empty($acc->publish_schedule))->count();

        // Calculate total followers
        $totalFollowers = $connectedAccounts->sum('follower_count');

        return view('dashboard.social-media', compact(
            'user',
            'connectedAccounts',
            'totalAccounts',
            'activeAccounts',
            'expiredTokens',
            'accountsByPlatform',
            'autoPublishEnabled',
            'socialMediaAccounts',
            'accountsWithSchedule',
            'totalFollowers'
        ));
    }

    /**
     * Display the analytics dashboard.
     */
    public function analytics(): View
    {
        $user = Auth::user();

        // Get total stats
        $totalViews = $user->posts()->sum('views_count');
        $totalLikes = $user->posts()->sum('likes_count');
        $totalComments = $user->posts()->sum('comments_count');
        $totalShares = $user->posts()->sum('shares_count') ?? 0;

        // Calculate engagement rate
        $totalPosts = $user->posts()->count();
        $engagementRate = $totalPosts > 0 ? (($totalLikes + $totalComments + $totalShares) / ($totalViews * $totalPosts)) * 100 : 0;

        // Get popular posts (top 5)
        $topPosts = $user->posts()
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        // Get traffic by category
        $trafficByCategory = $user->posts()
            ->join('post_categories', 'posts.id', '=', 'post_categories.post_id')
            ->join('categories', 'post_categories.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(posts.views_count) as total_views, COUNT(posts.id) as count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_views')
            ->get();

        // Get daily view trends (last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $dailyViews = $user->posts()
            ->where('published_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(published_at) as date, SUM(views_count) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get engagement metrics by post type
        $engagementByType = $user->posts()
            ->selectRaw('post_type, COUNT(*) as count, SUM(views_count) as views, SUM(likes_count) as likes')
            ->groupBy('post_type')
            ->get();

        return view('dashboard.analytics', compact(
            'user',
            'totalViews',
            'totalLikes',
            'totalComments',
            'totalShares',
            'engagementRate',
            'topPosts',
            'trafficByCategory',
            'dailyViews',
            'engagementByType'
        ));
    }

    /**
     * Display the webhooks management dashboard.
     */
    public function webhooks(): View
    {
        $user = Auth::user();

        // Get webhook statistics
        $totalWebhooks = $user->webhooks()->count();
        $activeWebhooks = $user->webhooks()->where('status', 'active')->count();
        $failedWebhooks = $user->webhooks()->where('status', 'failed')->count();

        // Get total deliveries and success rate across all webhooks
        $webhookIds = $user->webhooks()->pluck('id');
        $totalDeliveries = \App\Models\WebhookLog::whereIn('webhook_id', $webhookIds)->count();

        $successfulDeliveries = \App\Models\WebhookLog::whereIn('webhook_id', $webhookIds)
            ->where('success', true)
            ->count();

        $overallSuccessRate = $totalDeliveries > 0
            ? round(($successfulDeliveries / $totalDeliveries) * 100, 2)
            : 0;

        // Get all webhooks with pagination
        $webhooks = $user->webhooks()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get recent delivery logs (last 50 across all webhooks)
        $recentLogs = \App\Models\WebhookLog::whereIn('webhook_id', $webhookIds)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('dashboard.webhooks', compact(
            'user',
            'totalWebhooks',
            'activeWebhooks',
            'failedWebhooks',
            'totalDeliveries',
            'overallSuccessRate',
            'webhooks',
            'recentLogs'
        ));
    }

    /**
     * Display the notifications center dashboard.
     */
    public function notifications(Request $request): View
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        // Get notification statistics
        $totalNotifications = $user->notifications()->count();
        $unreadCount = $user->notifications()->whereNull('read_at')->count();
        $mentionCount = $user->notifications()
            ->where('type', 'mention')
            ->whereNull('read_at')
            ->count();

        // Get notifications based on filter
        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'mentions') {
            $query->where('type', 'mention');
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get notification types breakdown
        $notificationsByType = $user->notifications()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        // Get recent read notifications
        $recentRead = $user->notifications()
            ->whereNotNull('read_at')
            ->orderBy('read_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.notifications', compact(
            'user',
            'totalNotifications',
            'unreadCount',
            'mentionCount',
            'filter',
            'notifications',
            'notificationsByType',
            'recentRead'
        ));
    }

    /**
     * Display the job status monitor dashboard.
     */
    public function jobStatus(): View
    {
        $user = Auth::user();

        // Get job status statistics
        $totalJobs = \App\Models\JobStatus::where('user_id', $user->id)->count();
        $pendingJobs = \App\Models\JobStatus::where('user_id', $user->id)->pending()->count();
        $processingJobs = \App\Models\JobStatus::where('user_id', $user->id)->processing()->count();
        $completedJobs = \App\Models\JobStatus::where('user_id', $user->id)->completed()->count();
        $failedJobs = \App\Models\JobStatus::where('user_id', $user->id)->failed()->count();

        // Get job types breakdown
        $jobsByType = \App\Models\JobStatus::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count, SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed_count')
            ->groupBy('type')
            ->get();

        // Get recent jobs with pagination
        $recentJobs = \App\Models\JobStatus::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get processing jobs (showing progress)
        $processingJobsList = \App\Models\JobStatus::where('user_id', $user->id)
            ->where('status', 'processing')
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        // Get failed jobs for alerting
        $failedJobsList = \App\Models\JobStatus::where('user_id', $user->id)
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate success rate
        $successRate = $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 2) : 0;

        return view('dashboard.job-status', compact(
            'user',
            'totalJobs',
            'pendingJobs',
            'processingJobs',
            'completedJobs',
            'failedJobs',
            'successRate',
            'jobsByType',
            'recentJobs',
            'processingJobsList',
            'failedJobsList'
        ));
    }

    /**
     * Display the content calendar dashboard.
     */
    public function contentCalendar(Request $request): View
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get all posts for the user
        $allPosts = $user->posts()->get();

        // Get posts for the selected month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $monthPosts = $user->posts()
            ->whereBetween('published_at', [$startDate, $endDate])
            ->orderBy('published_at')
            ->get();

        // Get scheduled posts
        $scheduledPosts = $user->posts()
            ->where('status', 'scheduled')
            ->where('published_at', '>=', now())
            ->orderBy('published_at')
            ->limit(10)
            ->get();

        // Get draft posts
        $draftPosts = $user->posts()
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get series information
        $series = $user->posts()
            ->whereNotNull('series_slug')
            ->selectRaw('series_slug, series_title, COUNT(*) as post_count, MAX(series_total_parts) as total_parts')
            ->groupBy('series_slug', 'series_title')
            ->orderBy('series_title')
            ->get();

        // Get upcoming 30 days posts
        $upcomingPosts = $user->posts()
            ->where('published_at', '>=', now())
            ->where('published_at', '<=', now()->addDays(30))
            ->orderBy('published_at')
            ->get();

        // Calculate statistics
        $publishedCount = $user->posts()->where('status', 'published')->count();
        $scheduledCount = $user->posts()->where('status', 'scheduled')->count();
        $draftCount = $user->posts()->where('status', 'draft')->count();

        return view('dashboard.content-calendar', compact(
            'user',
            'month',
            'year',
            'monthPosts',
            'scheduledPosts',
            'draftPosts',
            'series',
            'upcomingPosts',
            'publishedCount',
            'scheduledCount',
            'draftCount'
        ));
    }

    /**
     * Display the user settings page.
     */
    public function settings(): View
    {
        $user = Auth::user();

        return view('dashboard.settings', [
            'user' => $user,
            'subscription' => $user->subscription(),
        ]);
    }

    /**
     * Update user settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'twitter' => 'nullable|string|max:50',
            'linkedin' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');

            $validated['avatar'] = $user->getFirstMediaUrl('avatars');
        }

        $user->update($validated);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password is incorrect.',
            ]);
        }

        // Cancel any active subscriptions (LemonSqueezy will handle this automatically)
        if ($user->subscribed()) {
            try {
                $user->subscription()->cancel();
            } catch (\Exception $e) {
                // Log the error but continue with deletion
                \Log::error('Failed to cancel subscription during account deletion: ' . $e->getMessage());
            }
        }

        // Delete the user
        $user->delete();

        Auth::logout();

        return redirect()->route('home')
            ->with('success', 'Your account has been deleted successfully.');
    }
}
