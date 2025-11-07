<?php

namespace App\Filament\Resources\VideoGenerationResource\Pages;

use App\Filament\Resources\VideoGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVideoGenerations extends ListRecords
{
    protected static string $resource = VideoGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Schedule New Video'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Videos')
                ->badge(static::$resource::getModel()::count()),

            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => $query->scheduled())
                ->badge(static::$resource::getModel()::scheduled()->count())
                ->badgeColor('info'),

            'queued' => Tab::make('Queued')
                ->modifyQueryUsing(fn (Builder $query) => $query->queued())
                ->badge(static::$resource::getModel()::queued()->count())
                ->badgeColor('warning'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->processing())
                ->badge(static::$resource::getModel()::processing()->count())
                ->badgeColor('primary'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed())
                ->badge(static::$resource::getModel()::completed()->count())
                ->badgeColor('success'),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->failed())
                ->badge(static::$resource::getModel()::failed()->count())
                ->badgeColor('danger'),

            'ready' => Tab::make('Ready to Process')
                ->modifyQueryUsing(fn (Builder $query) => $query->readyToProcess())
                ->badge(static::$resource::getModel()::readyToProcess()->count())
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VideoGenerationResource\Widgets\VideoGenerationStats::class,
        ];
    }
}