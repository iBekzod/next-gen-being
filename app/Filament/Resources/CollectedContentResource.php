<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectedContentResource\Pages;
use App\Models\CollectedContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectedContentResource extends Resource
{
    protected static ?string $model = CollectedContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Collected Content';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Article Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('external_url')
                            ->label('Source URL')
                            ->url()
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('content_source_id')
                            ->label('Source')
                            ->relationship('source', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('author')
                            ->disabled(),

                        Forms\Components\Select::make('content_type')
                            ->options([
                                'article' => 'Article',
                                'tutorial' => 'Tutorial',
                                'news' => 'News',
                                'documentation' => 'Documentation',
                            ])
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Content Preview')
                    ->schema([
                        Forms\Components\Textarea::make('excerpt')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('full_content')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Processing Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_processed')
                            ->disabled()
                            ->label('Processed'),

                        Forms\Components\Toggle::make('is_duplicate')
                            ->disabled()
                            ->label('Marked as Duplicate'),

                        Forms\Components\TextInput::make('duplicate_of')
                            ->label('Duplicate Of (ID)')
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

                Tables\Columns\TextColumn::make('source.name')
                    ->label('Source')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('content_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'article' => 'blue',
                        'tutorial' => 'green',
                        'news' => 'orange',
                        'documentation' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_processed')
                    ->boolean()
                    ->label('Processed'),

                Tables\Columns\IconColumn::make('is_duplicate')
                    ->boolean()
                    ->label('Duplicate'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Collected'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_source_id')
                    ->label('Source')
                    ->relationship('source', 'name'),

                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'article' => 'Article',
                        'tutorial' => 'Tutorial',
                        'news' => 'News',
                        'documentation' => 'Documentation',
                    ]),

                Tables\Filters\TernaryFilter::make('is_processed')
                    ->label('Processed'),

                Tables\Filters\TernaryFilter::make('is_duplicate')
                    ->label('Is Duplicate'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_duplicate')
                    ->label('Mark as Duplicate')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('duplicate_of')
                            ->label('Duplicate of Content ID')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(fn (CollectedContent $record, array $data) => self::markDuplicate($record, (int) $data['duplicate_of']))
                    ->visible(fn (CollectedContent $record) => !$record->is_duplicate),
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
            'index' => Pages\ListCollectedContent::route('/'),
        ];
    }

    protected static function markDuplicate(CollectedContent $record, int $duplicateOfId): void
    {
        $record->update([
            'is_duplicate' => true,
            'duplicate_of' => $duplicateOfId,
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Marked as Duplicate')
            ->body("Content #{$record->id} marked as duplicate of #{$duplicateOfId}")
            ->success()
            ->send();
    }
}
