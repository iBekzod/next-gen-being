<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostCurationResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostCurationResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Curated Posts';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Curation Details')
                    ->schema([
                        Forms\Components\TextInput::make('content_source_type')
                            ->label('Source Type')
                            ->disabled(),

                        Forms\Components\Select::make('content_aggregation_id')
                            ->label('Original Aggregation')
                            ->relationship('contentAggregation', 'topic')
                            ->disabled(),

                        Forms\Components\TextInput::make('paraphrase_confidence_score')
                            ->label('Paraphrase Confidence')
                            ->numeric()
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? (int) ($state * 100) : 0),

                        Forms\Components\Toggle::make('is_fact_verified')
                            ->label('Fact Verified')
                            ->reactive(),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => $get('is_fact_verified')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Language & Translation')
                    ->schema([
                        Forms\Components\TextInput::make('base_language')
                            ->disabled()
                            ->label('Base Language'),

                        Forms\Components\TextInput::make('base_post_id')
                            ->disabled()
                            ->label('Base Post ID')
                            ->visible(fn (Forms\Get $get) => (bool) $get('base_post_id')),

                        Forms\Components\TextInput::make('translation_count')
                            ->label('Translation Versions')
                            ->disabled()
                            ->getStateUsing(fn (Post $record) => $record->translatedVersions()->count()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Under Review',
                                'published' => 'Published',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date'),
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

                Tables\Columns\TextColumn::make('contentAggregation.topic')
                    ->label('Topic')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('paraphrase_confidence_score')
                    ->label('Confidence')
                    ->formatStateUsing(fn ($state) => $state ? (int) ($state * 100) . '%' : 'N/A')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_fact_verified')
                    ->boolean()
                    ->label('Verified'),

                Tables\Columns\TextColumn::make('base_language')
                    ->label('Language')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en' => 'blue',
                        'es' => 'green',
                        'fr' => 'purple',
                        'de' => 'orange',
                        'zh' => 'red',
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

                Tables\Columns\TextColumn::make('published_at')
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

                Tables\Filters\Filter::make('is_fact_verified')
                    ->label('Fact Verified Only')
                    ->query(fn (Builder $query) => $query->where('is_fact_verified', true)),

                Tables\Filters\Filter::make('high_confidence')
                    ->label('High Confidence (70%+)')
                    ->query(fn (Builder $query) => $query->where('paraphrase_confidence_score', '>=', 0.70)),

                Tables\Filters\SelectFilter::make('base_language')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'zh' => 'Chinese',
                        'pt' => 'Portuguese',
                        'it' => 'Italian',
                        'ja' => 'Japanese',
                        'ru' => 'Russian',
                        'ko' => 'Korean',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_sources')
                    ->label('View Sources')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(fn (Post $record) => route('posts.show', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('translate')
                    ->label('Create Translations')
                    ->icon('heroicon-o-language')
                    ->color('success')
                    ->form([
                        Forms\Components\CheckboxList::make('languages')
                            ->label('Target Languages')
                            ->options([
                                'es' => 'Spanish',
                                'fr' => 'French',
                                'de' => 'German',
                                'zh' => 'Chinese',
                                'pt' => 'Portuguese',
                                'it' => 'Italian',
                                'ja' => 'Japanese',
                                'ru' => 'Russian',
                                'ko' => 'Korean',
                            ])
                            ->required(),
                    ])
                    ->action(fn (Post $record, array $data) => self::createTranslations($record, $data['languages']))
                    ->visible(fn (Post $record) => !$record->isTranslation()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->action(fn (array $records) => self::publishBulk($records))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_curated', true));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostCurations::route('/'),
            'edit' => Pages\EditPostCuration::route('/{record}/edit'),
        ];
    }

    protected static function createTranslations(Post $record, array $languages): void
    {
        \App\Jobs\TranslatePostJob::dispatch($record->id, $languages);

        \Filament\Notifications\Notification::make()
            ->title('Translations Queued')
            ->body("Queued translations to: " . implode(', ', $languages))
            ->success()
            ->send();
    }

    protected static function publishBulk(array $records): void
    {
        foreach ($records as $record) {
            $record->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        \Filament\Notifications\Notification::make()
            ->title('Published')
            ->body("Published " . count($records) . " posts")
            ->success()
            ->send();
    }
}
