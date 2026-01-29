<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\LandingLead;
use LemonSqueezy\Laravel\Subscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        try {
            $totalPosts = Post::count();
            $publishedPosts = Post::where('status', 'published')->count();
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            // LemonSqueezy uses 'active' status for active subscriptions
            $activeSubscriptions = Subscription::whereIn('status', ['active', 'on_trial'])->count();
            $totalComments = Comment::count();
            $approvedComments = Comment::where('status', 'approved')->count();
            $totalLeads = LandingLead::count();
            $todayLeads = LandingLead::whereDate('created_at', today())->count();
            $totalViews = Post::sum('views_count') ?? 0;

            return [
                Stat::make('Total Posts', $totalPosts)
                    ->description('Published: ' . $publishedPosts)
                    ->descriptionIcon('heroicon-o-document-text')
                    ->color('success'),

                Stat::make('Total Users', $totalUsers)
                    ->description($activeUsers . ' active users')
                    ->descriptionIcon('heroicon-o-users')
                    ->color('primary'),

                Stat::make('Active Subscriptions', $activeSubscriptions)
                    ->description('Total revenue stream')
                    ->descriptionIcon('heroicon-o-credit-card')
                    ->color('success'),

                Stat::make('Total Comments', $totalComments)
                    ->description($approvedComments . ' approved')
                    ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                    ->color('warning'),

                Stat::make('Landing Leads', $totalLeads)
                    ->description($todayLeads . ' today')
                    ->descriptionIcon('heroicon-o-envelope')
                    ->color('info'),

                Stat::make('Total Views', $totalViews)
                    ->description('All time post views')
                    ->descriptionIcon('heroicon-o-eye')
                    ->color('primary'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Error', 'Unable to load stats')
                    ->description('Please check database connection')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }

}
