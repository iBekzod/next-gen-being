<?php

namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Comment;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class PostStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $total = Post::query()->count();
        $published = Post::query()->published()->count();
        $featured = Post::query()->where('is_featured', true)->count();
        $pendingComments = Comment::query()->where('status', 'pending')->count();

        return [
            Card::make('Total posts', number_format($total))
                ->description('All posts in the library')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
            Card::make('Published', number_format($published))
                ->description('Visible on the website')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('success'),
            Card::make('Featured', number_format($featured))
                ->description('Highlighted on landing pages')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),
            Card::make('Pending comments', number_format($pendingComments))
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color($pendingComments > 0 ? 'danger' : 'gray'),
        ];
    }
}
