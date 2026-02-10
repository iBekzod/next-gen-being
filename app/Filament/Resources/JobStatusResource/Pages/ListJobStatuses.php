<?php

namespace App\Filament\Resources\JobStatusResource\Pages;

use App\Filament\Resources\JobStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListJobStatuses extends ListRecords
{
    protected static string $resource = JobStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->redirect(request()->header('Referer'))),

            Actions\Action::make('clean_old')
                ->label('Clean Old Jobs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    // Delete completed jobs older than 7 days
                    $deleted = \App\Models\JobStatus::where('status', 'completed')
                        ->where('created_at', '<', now()->subDays(7))
                        ->delete();

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title("Deleted {$deleted} old job records")
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Jobs'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing'))
                ->badge(fn () => \App\Models\JobStatus::where('status', 'processing')->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\JobStatus::where('status', 'completed')->count())
                ->badgeColor('success'),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(fn () => \App\Models\JobStatus::where('status', 'failed')->count())
                ->badgeColor('danger'),

            'video' => Tab::make('Video Generation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'video-generation')),

            'social' => Tab::make('Social Media')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', [
                    'social-media-publish',
                    'publish-platform',
                    'telegram',
                ])),
        ];
    }
}
