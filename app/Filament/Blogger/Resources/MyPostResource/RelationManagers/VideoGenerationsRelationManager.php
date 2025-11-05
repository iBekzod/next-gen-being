<?php

namespace App\Filament\Blogger\Resources\MyPostResource\RelationManagers;

use App\Models\VideoGeneration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VideoGenerationsRelationManager extends RelationManager
{
    protected static string $relationship = 'videoGenerations';

    protected static ?string $title = 'Video Generations';

    protected static ?string $recordTitleAttribute = 'video_type';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('video_type')
            ->columns([
                Tables\Columns\TextColumn::make('video_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'short' => 'Short (60s)',
                        'medium' => 'Medium (3-5m)',
                        'long' => 'Long (10m+)',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'short' => 'warning',
                        'medium' => 'info',
                        'long' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
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

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate("i:s", $state) : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('video_url')
                    ->label('Video')
                    ->formatStateUsing(fn ($state) => $state ? '✓ Available' : '-')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('processing_time')
                    ->label('Processing Time')
                    ->formatStateUsing(function ($record) {
                        if (!$record->started_at || !$record->completed_at) {
                            return '-';
                        }
                        $seconds = $record->completed_at->diffInSeconds($record->started_at);
                        if ($seconds < 60) return $seconds . 's';
                        $minutes = floor($seconds / 60);
                        $remainingSeconds = $seconds % 60;
                        return $minutes . 'm ' . $remainingSeconds . 's';
                    })
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('video_type')
                    ->label('Type')
                    ->options([
                        'short' => 'Short',
                        'medium' => 'Medium',
                        'long' => 'Long',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_new')
                    ->label('Generate Video')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->visible(fn () => $this->getOwnerRecord()->status === 'published')
                    ->form([
                        Forms\Components\Select::make('video_type')
                            ->label('Video Type')
                            ->options([
                                'short' => 'Short (60 seconds) - Quick overview',
                                'medium' => 'Medium (3-5 minutes) - Standard format',
                                'long' => 'Long (10+ minutes) - In-depth coverage',
                            ])
                            ->default('medium')
                            ->required()
                            ->helperText('Choose the length and depth of your video'),
                    ])
                    ->action(function (array $data) {
                        \App\Jobs\GenerateVideoJob::dispatch(
                            $this->getOwnerRecord(),
                            $data['video_type'],
                            Auth::id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Video Generation Started!')
                            ->body('Your video is being generated in the background.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_video')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (VideoGeneration $record) => $record->status === 'completed' && $record->video_url)
                    ->url(fn (VideoGeneration $record) => $record->video_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (VideoGeneration $record) => $record->status === 'completed' && $record->video_url)
                    ->url(fn (VideoGeneration $record) => $record->video_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (VideoGeneration $record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (VideoGeneration $record) {
                        \App\Jobs\GenerateVideoJob::dispatch(
                            $record->post,
                            $record->video_type,
                            Auth::id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Regeneration Started!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (VideoGeneration $record) => $record->status !== 'processing'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No videos generated yet')
            ->emptyStateDescription('Generate your first video from this post')
            ->emptyStateIcon('heroicon-o-video-camera')
            ->emptyStateActions([
                Tables\Actions\Action::make('generate_first')
                    ->label('Generate First Video')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->visible(fn () => $this->getOwnerRecord()->status === 'published')
                    ->form([
                        Forms\Components\Select::make('video_type')
                            ->label('Video Type')
                            ->options([
                                'short' => 'Short (60 seconds)',
                                'medium' => 'Medium (3-5 minutes)',
                                'long' => 'Long (10+ minutes)',
                            ])
                            ->default('medium')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        \App\Jobs\GenerateVideoJob::dispatch(
                            $this->getOwnerRecord(),
                            $data['video_type'],
                            Auth::id()
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Video Generation Started!')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
