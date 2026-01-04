<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceReferenceResource\Pages;
use App\Models\SourceReference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SourceReferenceResource extends Resource
{
    protected static ?string $model = SourceReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Source References';

    protected static ?string $navigationGroup = 'Content Curation';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reference Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('author')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('published_at')
                            ->label('Published Date'),

                        Forms\Components\TextInput::make('domain')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Citation Details')
                    ->schema([
                        Forms\Components\Select::make('citation_style')
                            ->options([
                                'inline' => 'Inline [1]',
                                'apa' => 'APA',
                                'chicago' => 'Chicago',
                                'harvard' => 'Harvard',
                            ])
                            ->required()
                            ->default('inline'),

                        Forms\Components\TextInput::make('position_in_post')
                            ->label('Position in Post')
                            ->numeric(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tracking')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('Referenced Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('accessed_at')
                            ->label('Last Accessed')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state?->diffForHumans()),
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

                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('citation_style')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inline' => 'blue',
                        'apa' => 'purple',
                        'chicago' => 'orange',
                        'harvard' => 'green',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('accessed_at')
                    ->label('Accessed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('citation_style')
                    ->options([
                        'inline' => 'Inline',
                        'apa' => 'APA',
                        'chicago' => 'Chicago',
                        'harvard' => 'Harvard',
                    ]),

                Tables\Filters\SelectFilter::make('post_id')
                    ->label('Post')
                    ->relationship('post', 'title'),

                Tables\Filters\Filter::make('domain')
                    ->form([
                        Forms\Components\TextInput::make('domain')
                            ->placeholder('e.g., github.com'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['domain'] ?? null, fn ($q) => $q->where('domain', 'like', '%' . $data['domain'] . '%'))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSourceReferences::route('/'),
            'view' => Pages\ViewSourceReference::route('/{record}'),
            'edit' => Pages\EditSourceReference::route('/{record}/edit'),
        ];
    }
}
