<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorialCollectionResource\Pages;
use App\Models\TutorialCollection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TutorialCollectionResource extends Resource
{
    protected static ?string $model = TutorialCollection::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Tutorial Collections';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tutorial Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('topic')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('skill_level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('language')
                            ->required()
                            ->default('en'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Content Details')
                    ->schema([
                        Forms\Components\Repeater::make('steps')
                            ->label('Tutorial Steps')
                            ->schema([
                                Forms\Components\TextInput::make('step_num')
                                    ->label('Step Number')
                                    ->numeric(),
                                Forms\Components\TextInput::make('title')
                                    ->label('Step Title'),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2),
                            ])
                            ->collapsed(),

                        Forms\Components\Repeater::make('code_examples')
                            ->label('Code Examples')
                            ->schema([
                                Forms\Components\TextInput::make('language')
                                    ->label('Language'),
                                Forms\Components\Textarea::make('code')
                                    ->label('Code')
                                    ->rows(4),
                            ])
                            ->collapsed(),

                        Forms\Components\Repeater::make('best_practices')
                            ->label('Best Practices')
                            ->simple(
                                Forms\Components\TextInput::make('practice')
                                    ->required()
                            )
                            ->collapsed(),

                        Forms\Components\Repeater::make('common_pitfalls')
                            ->label('Common Pitfalls')
                            ->simple(
                                Forms\Components\TextInput::make('pitfall')
                                    ->required()
                            )
                            ->collapsed(),
                    ]),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Under Review',
                                'published' => 'Published',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('estimated_hours')
                            ->label('Estimated Duration (hours)')
                            ->numeric(),

                        Forms\Components\TextInput::make('reading_time_minutes')
                            ->label('Reading Time (minutes)')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('topic')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('skill_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'green',
                        'intermediate' => 'blue',
                        'advanced' => 'orange',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estimated_hours')
                    ->label('Duration')
                    ->suffix(' hrs'),

                Tables\Columns\TextColumn::make('source_count')
                    ->label('Sources')
                    ->getStateUsing(fn (TutorialCollection $record) => count($record->source_ids ?? []))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Under Review',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('skill_level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(fn (TutorialCollection $record) => self::publishTutorial($record))
                    ->visible(fn (TutorialCollection $record) => $record->status !== 'published')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish_bulk')
                        ->label('Publish Selected')
                        ->action(fn (array $records) => self::publishBulk($records))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListTutorialCollections::route('/'),
            'edit' => Pages\EditTutorialCollection::route('/{record}/edit'),
        ];
    }

    protected static function publishTutorial(TutorialCollection $record): void
    {
        // Get authenticated admin user
        $reviewer = auth()->user();

        if (!$reviewer) {
            \Filament\Notifications\Notification::make()
                ->title('Error')
                ->body('Must be authenticated to publish.')
                ->danger()
                ->send();
            return;
        }

        $record->publish($reviewer, 'Published via Filament admin');

        \Filament\Notifications\Notification::make()
            ->title('Published')
            ->body("Published: {$record->title}")
            ->success()
            ->send();
    }

    protected static function publishBulk(array $records): void
    {
        $reviewer = auth()->user();

        if (!$reviewer) {
            return;
        }

        foreach ($records as $record) {
            $record->publish($reviewer, 'Bulk published via Filament admin');
        }

        \Filament\Notifications\Notification::make()
            ->title('Published')
            ->body("Published " . count($records) . " tutorials")
            ->success()
            ->send();
    }
}
