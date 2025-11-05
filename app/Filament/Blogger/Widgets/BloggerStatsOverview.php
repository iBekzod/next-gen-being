<?php

namespace App\Filament\Blogger\Widgets;

use App\Services\BloggerMonetizationService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BloggerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $blogger = Auth::user();
        $service = app(BloggerMonetizationService::class);
        $stats = $service->getBloggerStats($blogger);

        $nextMilestone = $stats['followers']['next_milestone'];
        $nextMilestoneText = $nextMilestone
            ? "{$nextMilestone['remaining']} to \${$nextMilestone['amount']}"
            : "All milestones reached!";

        return [
            Stat::make('Total Followers', $stats['followers']['count'])
                ->description($nextMilestoneText)
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Posts', $stats['posts']['total'])
                ->description("{$stats['posts']['published']} published, {$stats['posts']['draft']} drafts")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Premium Posts', $stats['posts']['premium'])
                ->description('Exclusive content')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Total Earnings', '$' . number_format($stats['earnings']['total'], 2))
                ->description("Pending: \$" . number_format($stats['earnings']['pending'], 2))
                ->descriptionIcon($stats['eligible_for_payout'] ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($stats['eligible_for_payout'] ? 'success' : 'gray'),
        ];
    }
}
