<?php

namespace App\Filament\Resources\VideoGenerationResource\Widgets;

use App\Models\VideoGeneration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class VideoGenerationStats extends BaseWidget
{
    protected function getStats(): array
    {
        $scheduled = VideoGeneration::scheduled()->count();
        $queued = VideoGeneration::queued()->count();
        $processing = VideoGeneration::processing()->count();
        $completed = VideoGeneration::completed()->count();
        $failed = VideoGeneration::failed()->count();

        $todayCompleted = VideoGeneration::completed()
            ->whereDate('completed_at', today())
            ->count();

        $avgProcessingTime = VideoGeneration::completed()
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get()
            ->avg(fn ($video) => $video->getProcessingTime());

        $totalCreditsUsed = VideoGeneration::sum('ai_credits_used');
        $totalCost = VideoGeneration::sum('generation_cost');

        return [
            Stat::make('Scheduled Videos', $scheduled)
                ->description('Ready at scheduled time')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 8, 5]),

            Stat::make('Queue Size', $queued + $processing)
                ->description($processing . ' currently processing')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->chart([2, 4, 3, 5, 2, 3, 4]),

            Stat::make('Completed Today', $todayCompleted)
                ->description($completed . ' total completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 6, 7]),

            Stat::make('Failed Videos', $failed)
                ->description($failed > 0 ? 'Needs attention' : 'All good')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($failed > 0 ? 'danger' : 'secondary'),

            Stat::make('Avg Processing Time', $avgProcessingTime ? Number::format($avgProcessingTime) . 's' : 'N/A')
                ->description('Per video generation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make('Total Cost', '$' . Number::format($totalCost, 2))
                ->description(Number::abbreviate($totalCreditsUsed) . ' credits used')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('secondary'),
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}