<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class BlogStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $publishedCount = Post::query()->published()->count();
        $draftCount = Post::query()->where('status', 'draft')->count();
        $pendingComments = Comment::query()->where('status', 'pending')->count();

        $viewsLast30 = Post::query()
            ->where('published_at', '>=', Carbon::now()->subDays(30))
            ->sum('views_count');

        return [
            Card::make('Published posts', number_format($publishedCount))
                ->description('Active articles live on the site')
                ->descriptionIcon('heroicon-o-globe')
                ->color('success'),
            Card::make('Drafts & scheduled', number_format($draftCount + Post::query()->where('status', 'scheduled')->count()))
                ->description('Ready for review or awaiting publish')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->color('warning'),
            Card::make('Pending comments', number_format($pendingComments))
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-o-chat-bubble-bottom-center-text')
                ->color($pendingComments > 0 ? 'danger' : 'gray'),
            Card::make('Views (last 30 days)', number_format($viewsLast30))
                ->description('Total recorded across all posts')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
