<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentSourceResource\Pages;
use App\Models\ContentSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentSourceResource extends Resource
{
    protected static ?string $model = ContentSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Content Sources';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Source Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category')
                            ->options([
                                'news' => 'Technology News',
                                'blog' => 'Technical Blogs',
                                'tutorial' => 'Tutorials',
                                'research' => 'Research',
                                'documentation' => 'Documentation',
                                'forum' => 'Community Forums',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Trust & Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('trust_level')
                            ->label('Trust Level (0-100)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(75)
                            ->required()
                            ->hint('Sources with 70+ are automatically scraped'),

                        Forms\Components\Toggle::make('scraping_enabled')
                            ->label('Enable Scraping')
                            ->default(true)
                            ->reactive(),

                        Forms\Components\TextInput::make('rate_limit_per_sec')
                            ->label('Rate Limit (requests/sec)')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10),

                        Forms\Components\Textarea::make('css_selectors')
                            ->label('CSS Selectors (JSON)')
                            ->hint('Optional: Override default article extraction selectors')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Scraping Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('last_scraped_at')
                            ->label('Last Scraped')
                            ->disabled(),

                        Forms\Components\TextInput::make('articles_count')
                            ->label('Articles Collected')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'news' => 'blue',
                        'blog' => 'purple',
                        'tutorial' => 'green',
                        'research' => 'orange',
                        'documentation' => 'gray',
                        'forum' => 'pink',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('scraping_enabled')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('trust_level')
                    ->label('Trust')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_scraped_at')
                    ->dateTime()
                    ->label('Last Scraped')
                    ->sortable(),

                Tables\Columns\TextColumn::make('collected_content_count')
                    ->label('Articles')
                    ->counts('collectedContent'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'news' => 'Technology News',
                        'blog' => 'Technical Blogs',
                        'tutorial' => 'Tutorials',
                        'research' => 'Research',
                        'documentation' => 'Documentation',
                        'forum' => 'Community Forums',
                    ]),

                Tables\Filters\TernaryFilter::make('scraping_enabled')
                    ->label('Scraping Active'),

                Tables\Filters\Filter::make('high_trust')
                    ->label('High Trust (70+)')
                    ->query(fn (Builder $query) => $query->where('trust_level', '>=', 70)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('test_scraping')
                        ->label('Test Scraping')
                        ->icon('heroicon-o-bolt')
                        ->color('info')
                        ->action(fn (ContentSource $record) => self::testScraping($record))
                        ->requiresConfirmation()
                        ->modalHeading('Test Content Scraping')
                        ->modalDescription('This will attempt to scrape 5 articles from this source.')
                        ->modalSubmitActionLabel('Test Now'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListContentSources::route('/'),
            'create' => Pages\CreateContentSource::route('/create'),
            'edit' => Pages\EditContentSource::route('/{record}/edit'),
        ];
    }

    protected static function testScraping(ContentSource $source): void
    {
        \Filament\Notifications\Notification::make()
            ->title('Scraping Test Started')
            ->body("Testing {$source->name}...")
            ->info()
            ->send();

        // Queue a job to test scraping
        \App\Jobs\ScrapeSingleSourceJob::dispatch($source->id, 5);

        \Filament\Notifications\Notification::make()
            ->title('Test Queued')
            ->body('Scraping test has been queued. Check the database in a few seconds.')
            ->success()
            ->send();
    }
}
