<?php

namespace App\Filament\Blogger\Resources\JobStatusResource\Pages;

use App\Filament\Blogger\Resources\JobStatusResource;
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
                ->label('Clean Completed')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clean Completed Jobs')
                ->modalDescription('Delete completed jobs older than 7 days to keep your list clean.')
                ->action(function () {
                    // Delete completed jobs older than 7 days
                    $deleted = \App\Models\JobStatus::where('user_id', auth()->id())
                        ->where('status', 'completed')
                        ->where('created_at', '<', now()->subDays(7))
                        ->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('Cleanup Complete')
                        ->body("Deleted {$deleted} old job records")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        $userId = auth()->id();

        return [
            'all' => Tab::make('All Jobs')
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)->count()),

            'processing' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing'))
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)->where('status', 'processing')->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)->where('status', 'completed')->count())
                ->badgeColor('success'),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)->where('status', 'failed')->count())
                ->badgeColor('danger'),

            'video' => Tab::make('Video Generation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'video-generation'))
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)->where('type', 'video-generation')->count()),

            'social' => Tab::make('Social Media')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', [
                    'social-media-publish',
                    'publish-platform',
                    'telegram',
                ]))
                ->badge(fn () => \App\Models\JobStatus::where('user_id', $userId)
                    ->whereIn('type', ['social-media-publish', 'publish-platform', 'telegram'])
                    ->count()),
        ];
    }
}
