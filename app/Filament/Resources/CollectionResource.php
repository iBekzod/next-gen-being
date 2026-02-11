<?php

namespace App\Filament\Resources;

use App\Models\Collection;
use App\Filament\Resources\CollectionResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;
    protected static ?string $slug = 'collections';
    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collection Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Collection Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->maxLength(1000),
                    Forms\Components\Select::make('user_id')
                        ->label('Creator')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\FileUpload::make('cover_image_url')
                        ->label('Cover Image')
                        ->image()
                        ->nullable(),
                    Forms\Components\Toggle::make('is_public')
                        ->label('Public')
                        ->default(true),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Collection Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->getStateUsing(fn (Collection $record): int => $record->posts()->count())
                    ->sortable(query: fn ($query, string $direction): Builder =>
                        $query->withCount('posts')->orderBy('posts_count', $direction)
                    ),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_public'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
