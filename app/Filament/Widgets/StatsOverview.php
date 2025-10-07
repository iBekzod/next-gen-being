<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Subscription;
use App\Models\LandingLead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Posts', Post::count())
                ->description('Published: ' . Post::where('status', 'published')->count())
                ->descriptionIcon('heroicon-o-document-text')
                ->color('success')
                ->chart($this->getPostsChart()),

            Stat::make('Total Users', User::count())
                ->description(User::where('is_active', true)->count() . ' active users')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart($this->getUsersChart()),

            Stat::make('Active Subscriptions', Subscription::where('status', 'active')->count())
                ->description('Total revenue stream')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('success')
                ->chart($this->getSubscriptionsChart()),

            Stat::make('Total Comments', Comment::count())
                ->description(Comment::where('status', 'approved')->count() . ' approved')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('warning')
                ->chart($this->getCommentsChart()),

            Stat::make('Landing Leads', LandingLead::count())
                ->description(LandingLead::whereDate('created_at', today())->count() . ' today')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('info')
                ->chart($this->getLeadsChart()),

            Stat::make('Total Views', Post::sum('views_count'))
                ->description('All time post views')
                ->descriptionIcon('heroicon-o-eye')
                ->color('primary'),
        ];
    }

    protected function getPostsChart(): array
    {
        return Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getUsersChart(): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getSubscriptionsChart(): array
    {
        return Subscription::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->where('status', 'active')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getCommentsChart(): array
    {
        return Comment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    protected function getLeadsChart(): array
    {
        return LandingLead::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }
}
