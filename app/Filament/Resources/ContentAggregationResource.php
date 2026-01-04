<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentAggregationResource\Pages;
use App\Models\ContentAggregation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentAggregationResource extends Resource
{
    protected static ?string $model = ContentAggregation::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Content Aggregations';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Aggregation Details')
                    ->schema([
                        Forms\Components\TextInput::make('topic')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('confidence_score')
                            ->label('Confidence Score')
                            ->disabled()
                            ->suffix('%')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Associated Content')
                    ->schema([
                        Forms\Components\Repeater::make('source_ids')
                            ->label('Sources')
                            ->disabled()
                            ->simple(
                                Forms\Components\TextInput::make('source_id')
                                    ->disabled()
                            ),

                        Forms\Components\Repeater::make('collected_content_ids')
                            ->label('Collected Articles')
                            ->disabled()
                            ->simple(
                                Forms\Components\TextInput::make('article_id')
                                    ->disabled()
                            ),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Processing Status')
                    ->schema([
                        Forms\Components\TextInput::make('primary_source_id')
                            ->label('Primary Source ID')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Curation',
                                'curating' => 'Currently Curating',
                                'curated' => 'Curated',
                                'published' => 'Published',
                            ])
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('topic')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Confidence')
                    ->formatStateUsing(fn ($state) => (int) ($state * 100) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('collected_content_count')
                    ->label('Articles')
                    ->counts('collectedContent')
                    ->sortable(),

                Tables\Columns\TextColumn::make('source_count')
                    ->label('Sources')
                    ->getStateUsing(fn (ContentAggregation $record) => count($record->source_ids ?? []))
                    ->sortable(),

                Tables\Columns\TextColumn::make('primary_source.name')
                    ->label('Primary Source')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
            ])
            ->filters([
                Tables\Filters\Filter::make('high_confidence')
                    ->label('High Confidence (75%+)')
                    ->query(fn (Builder $query) => $query->where('confidence_score', '>=', 0.75)),

                Tables\Filters\Filter::make('needs_curation')
                    ->label('Needs Curation')
                    ->query(fn (Builder $query) => $query->whereNull('curated_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('curate')
                    ->label('Create Curated Post')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->url(fn (ContentAggregation $record) => static::getResource()::getUrl('curate', ['record' => $record]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListContentAggregations::route('/'),
        ];
    }
}
