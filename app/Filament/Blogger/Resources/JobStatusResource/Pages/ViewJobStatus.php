<?php

namespace App\Filament\Blogger\Resources\JobStatusResource\Pages;

use App\Filament\Blogger\Resources\JobStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;

class ViewJobStatus extends ViewRecord
{
    protected static string $resource = JobStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn ($record) => $record->isFailed())
                ->requiresConfirmation()
                ->modalHeading('Retry Job')
                ->modalDescription('This will reset the job and attempt to run it again.')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'started_at' => null,
                        'completed_at' => null,
                        'attempts' => 0,
                    ]);

                    // Re-dispatch the job based on type
                    if ($record->type === 'video-generation' && $record->trackable) {
                        \App\Jobs\GenerateVideoJob::dispatch(
                            $record->trackable,
                            $record->metadata['video_type'] ?? 'medium',
                            Auth::id()
                        );
                    } elseif ($record->type === 'social-media-publish' && $record->trackable) {
                        \App\Jobs\PublishToSocialMediaJob::dispatch($record->trackable);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Job Restarted')
                        ->body('The job has been reset and queued for retry.')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->label('Remove Job'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('type')
                            ->label('Job Type')
                            ->formatStateUsing(fn (string $state): string => match($state) {
                                'video-generation' => 'Video Generation',
                                'social-media-publish' => 'Social Media Publishing',
                                'publish-platform' => 'Platform Publishing',
                                'telegram' => 'Telegram Publishing',
                                'engagement-metrics' => 'Engagement Metrics Update',
                                default => ucwords(str_replace('-', ' ', $state)),
                            })
                            ->badge(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'info',
                                'completed' => 'success',
                                'failed' => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('queue')
                            ->badge(),

                        Infolists\Components\TextEntry::make('progress')
                            ->label('Progress')
                            ->suffix('%')
                            ->color(fn ($state) => $state == 100 ? 'success' : 'info'),

                        Infolists\Components\TextEntry::make('attempts')
                            ->label('Attempts'),

                        Infolists\Components\TextEntry::make('trackable.title')
                            ->label('Related Post')
                            ->visible(fn ($record) => $record->trackable_type === 'App\Models\Post'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Progress Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('progress_message')
                            ->label('Current Status')
                            ->placeholder('No status message')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->progress_message)),

                Infolists\Components\Section::make('Error Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('Error Message')
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->isFailed()),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->label('Additional Information')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->metadata)),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Queued At')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime()
                            ->placeholder('Not started yet'),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed yet'),

                        Infolists\Components\TextEntry::make('duration')
                            ->label('Duration')
                            ->state(fn ($record) => $record->getFormattedDuration() ?? 'In progress'),
                    ])
                    ->columns(4),
            ]);
    }
}
