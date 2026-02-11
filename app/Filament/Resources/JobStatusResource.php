<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobStatusResource\Pages;
use App\Models\JobStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobStatusResource extends Resource
{
    protected static ?string $model = JobStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Job Queue';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'job_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('job_id')
                    ->label('Job ID')
                    ->disabled(),

                Forms\Components\TextInput::make('type')
                    ->label('Job Type')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->disabled(),

                Forms\Components\TextInput::make('queue')
                    ->disabled(),

                Forms\Components\TextInput::make('progress')
                    ->label('Progress (%)')
                    ->disabled(),

                Forms\Components\Textarea::make('progress_message')
                    ->disabled()
                    ->rows(2),

                Forms\Components\Textarea::make('error_message')
                    ->disabled()
                    ->rows(3),

                Forms\Components\Textarea::make('metadata')
                    ->label('Metadata (JSON)')
                    ->rows(3)
                    ->disabled(),

                Forms\Components\TextInput::make('attempts')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('started_at')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_id')
                    ->label('Job ID')
                    ->searchable()
                    ->sortable()
                    ->limit(16)
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'video-generation' => 'primary',
                        'social-media-publish' => 'success',
                        'engagement-metrics' => 'info',
                        'publish-platform' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('trackable_type')
                    ->label('Resource')
                    ->formatStateUsing(fn ($state) => class_basename($state))
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state): string => $state ? "{$state}%" : '0%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attempts')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('queue')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Queued')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'video-generation' => 'Video Generation',
                        'social-media-publish' => 'Social Media Publish',
                        'publish-platform' => 'Platform Publish',
                        'telegram' => 'Telegram',
                        'engagement-metrics' => 'Engagement Metrics',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('queue')
                    ->options([
                        'default' => 'Default',
                        'video' => 'Video',
                        'social' => 'Social',
                        'low-priority' => 'Low Priority',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('failed_only')
                    ->label('Failed Jobs')
                    ->query(fn (Builder $query) => $query->where('status', 'failed'))
                    ->toggle(),

                Tables\Filters\Filter::make('processing_only')
                    ->label('Currently Processing')
                    ->query(fn (Builder $query) => $query->where('status', 'processing'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (JobStatus $record) => $record->isFailed())
                    ->action(function (JobStatus $record) {
                        // Reset status to allow retry
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'completed_at' => null,
                        ]);

                        // Here you would re-dispatch the job
                        // Implementation depends on job type
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->poll('5s') // Auto-refresh every 5 seconds
            ->emptyStateHeading('No jobs in queue')
            ->emptyStateDescription('Jobs will appear here when video generation or social media publishing is in progress');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobStatuses::route('/'),
            'view' => Pages\ViewJobStatus::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin panel shows all job statuses with eager loaded relationships
        return parent::getEloquentQuery()
            ->with(['user', 'trackable']);
    }
}
