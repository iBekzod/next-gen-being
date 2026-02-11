<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoGenerationResource\Pages;
use App\Models\VideoGeneration;
use App\Models\Post;
use App\Models\SocialMediaAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class VideoGenerationResource extends Resource
{
    protected static ?string $model = VideoGeneration::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $pluralLabel = 'Video Generations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Video Details')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('Post')
                            ->relationship('post', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($record) => $record && $record->status !== 'queued'),

                        Forms\Components\Select::make('video_type')
                            ->label('Video Type')
                            ->options([
                                'youtube' => 'YouTube Video (10 min)',
                                'tiktok' => 'TikTok (60 sec)',
                                'reel' => 'Instagram Reel (90 sec)',
                                'short' => 'YouTube Short (60 sec)',
                            ])
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== 'queued'),

                        Forms\Components\Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->helperText('Higher priority videos will be processed first'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Scheduling')
                    ->schema([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Schedule For')
                            ->timezone('UTC')
                            ->minDate(now())
                            ->helperText('Leave empty to process immediately')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('status', 'scheduled');
                                } else {
                                    $set('status', 'queued');
                                }
                            }),

                        Forms\Components\Toggle::make('auto_publish')
                            ->label('Auto-Publish After Generation')
                            ->helperText('Automatically publish to selected platforms when video is ready')
                            ->reactive()
                            ->disabled(fn (Forms\Get $get) => !$get('scheduled_at')),

                        Forms\Components\CheckboxList::make('publish_platforms')
                            ->label('Publish To')
                            ->options(function () {
                                // Get user's connected social media accounts
                                $accounts = SocialMediaAccount::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(fn($account) => [$account->platform => $account->getPlatformDisplayName()])
                                    ->toArray();

                                return array_merge($accounts, [
                                    'youtube' => 'YouTube',
                                    'instagram' => 'Instagram',
                                    'twitter' => 'Twitter/X',
                                    'telegram' => 'Telegram',
                                ]);
                            })
                            ->columns(2)
                            ->visible(fn (Forms\Get $get) => $get('auto_publish')),

                        Forms\Components\Hidden::make('status')
                            ->default('queued'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Generation Status')
                    ->schema([
                        Forms\Components\Placeholder::make('status_display')
                            ->label('Status')
                            ->content(fn ($record) => $record ? ucfirst($record->status) : 'Not Started'),

                        Forms\Components\Placeholder::make('progress')
                            ->label('Progress')
                            ->content(function ($record) {
                                if (!$record) return 'Not Started';

                                return match($record->status) {
                                    'queued' => 'Waiting in queue...',
                                    'scheduled' => 'Scheduled for ' . $record->scheduled_at->format('Y-m-d H:i'),
                                    'processing' => 'Processing... (Started ' . $record->started_at->diffForHumans() . ')',
                                    'completed' => 'Completed in ' . $record->getFormattedDuration(),
                                    'failed' => 'Failed: ' . $record->error_message,
                                    default => 'Unknown'
                                };
                            }),

                        Forms\Components\Placeholder::make('video_url')
                            ->label('Generated Video')
                            ->content(fn ($record) => $record && $record->video_url
                                ? new HtmlString('<a href="' . $record->video_url . '" target="_blank" class="text-primary-600 hover:underline">View Video</a>')
                                : 'Not yet generated'
                            ),

                        Forms\Components\Placeholder::make('retry_info')
                            ->label('Retry Information')
                            ->content(fn ($record) => $record && $record->retry_count > 0
                                ? "Retried {$record->retry_count} times, last at {$record->last_retry_at->format('Y-m-d H:i')}"
                                : 'No retries'
                            )
                            ->visible(fn ($record) => $record && $record->retry_count > 0),
                    ])
                    ->visible(fn ($record) => $record !== null)
                    ->columns(2),

                Forms\Components\Section::make('Error Details')
                    ->schema([
                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record && $record->hasFailed()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->limit(30)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('video_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'youtube' => 'primary',
                        'tiktok' => 'success',
                        'reel' => 'warning',
                        'short' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'gray',
                        'scheduled' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal' => 'primary',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled For')
                    ->dateTime()
                    ->sortable()
                    ->description(fn ($record) => $record->scheduled_at?->diffForHumans()),

                Tables\Columns\IconColumn::make('auto_publish')
                    ->label('Auto-Publish')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('publish_platforms')
                    ->label('Platforms')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->tooltip(function ($record) {
                        if (!$record->publish_platforms) return null;
                        return implode(', ', $record->publish_platforms);
                    }),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', $state) : '-'),

                Tables\Columns\TextColumn::make('retry_count')
                    ->label('Retries')
                    ->badge()
                    ->color(fn ($state) => $state > 2 ? 'danger' : ($state > 0 ? 'warning' : 'secondary')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'scheduled' => 'Scheduled',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('video_type')
                    ->label('Video Type')
                    ->options([
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok',
                        'reel' => 'Instagram Reel',
                        'short' => 'YouTube Short',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\TernaryFilter::make('auto_publish')
                    ->label('Auto-Publish'),

                Tables\Filters\Filter::make('scheduled')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('scheduled_at')),

                Tables\Filters\Filter::make('ready_to_process')
                    ->label('Ready to Process')
                    ->query(fn (Builder $query): Builder => $query->readyToProcess()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['queued', 'scheduled'])),

                Tables\Actions\Action::make('process_now')
                    ->label('Process Now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'scheduled')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'queued',
                            'scheduled_at' => null,
                        ]);
                    }),

                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->hasFailed() && $record->shouldRetry())
                    ->action(function ($record) {
                        $record->update(['status' => 'queued']);
                        $record->incrementRetryCount();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['queued', 'scheduled', 'processing']))
                    ->action(function ($record) {
                        $record->markAsFailed('Cancelled by user');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('admin')),

                    Tables\Actions\BulkAction::make('process_selected')
                        ->label('Process Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'scheduled') {
                                    $record->update([
                                        'status' => 'queued',
                                        'scheduled_at' => null,
                                    ]);
                                }
                            });
                        }),

                    Tables\Actions\BulkAction::make('cancel_selected')
                        ->label('Cancel Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->status, ['queued', 'scheduled'])) {
                                    $record->markAsFailed('Bulk cancelled by user');
                                }
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoGenerations::route('/'),
            'create' => Pages\CreateVideoGeneration::route('/create'),
            'view' => Pages\ViewVideoGeneration::route('/{record}'),
            'edit' => Pages\EditVideoGeneration::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'queued')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}