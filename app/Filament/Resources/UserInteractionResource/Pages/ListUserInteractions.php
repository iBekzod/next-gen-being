<?php

namespace App\Filament\Resources\UserInteractionResource\Pages;

use App\Filament\Resources\UserInteractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUserInteractions extends ListRecords
{
    protected static string $resource = UserInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Interactions'),
            'likes' => Tab::make('Likes')
                ->icon('heroicon-o-heart')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'like')),
            'bookmarks' => Tab::make('Bookmarks')
                ->icon('heroicon-o-bookmark')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'bookmark')),
            'views' => Tab::make('Views')
                ->icon('heroicon-o-eye')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'view')),
            'shares' => Tab::make('Shares')
                ->icon('heroicon-o-share')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'share')),
        ];
    }
}
