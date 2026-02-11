<?php

namespace App\Filament\Blogger\Resources;

use App\Filament\Blogger\Resources\JobStatusResource\Pages;
use App\Models\JobStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class JobStatusResource extends Resource
{
    protected static ?string $model = JobStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'My Jobs';

    protected static ?string $modelLabel = 'Job';

    protected static ?string $pluralModelLabel = 'My Jobs';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 10;

    // Only show jobs from the current user
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Job Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'video-generation' => 'Video Generation',
                        'social-media-publish' => 'Social Media Publish',
                        'publish-platform' => 'Platform Publish',
                        'telegram' => 'Telegram',
                        'engagement-metrics' => 'Engagement Metrics',
                        default => ucwords(str_replace('-', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'video-generation' => 'primary',
                        'social-media-publish' => 'success',
                        'engagement-metrics' => 'info',
                        'publish-platform' => 'warning',
                        'telegram' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('trackable.title')
                    ->label('Related Post')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'pending' => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-arrow-path',
                        'completed' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-exclamation-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state): string => $state ? "{$state}%" : '0%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress_message')
                    ->label('Current Status')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime()
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => $record->getFormattedDuration() ?? '—')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Job Type')
                    ->options([
                        'video-generation' => 'Video Generation',
                        'social-media-publish' => 'Social Media Publish',
                        'publish-platform' => 'Platform Publish',
                        'telegram' => 'Telegram',
                        'engagement-metrics' => 'Engagement Metrics',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (JobStatus $record) => $record->isFailed())
                    ->requiresConfirmation()
                    ->modalHeading('Retry Job')
                    ->modalDescription('This will reset the job and attempt to run it again.')
                    ->action(function (JobStatus $record) {
                        // Reset status to allow retry
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
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove Job Record')
                    ->modalDescription('This will only remove the job record, not any completed work.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ])
            ->poll('5s') // Auto-refresh every 5 seconds
            ->emptyStateHeading('No jobs in queue')
            ->emptyStateDescription('Video generation and social media publishing jobs will appear here')
            ->emptyStateIcon('heroicon-o-queue-list');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobStatuses::route('/'),
            'view' => Pages\ViewJobStatus::route('/{record}'),
        ];
    }
}
