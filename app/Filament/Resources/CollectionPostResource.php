<?php

namespace App\Filament\Resources;

use App\Models\CollectionPost;
use App\Filament\Resources\CollectionPostResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CollectionPostResource extends Resource
{
    protected static ?string $model = CollectionPost::class;
    protected static ?string $slug = 'collection-posts';
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collection Post Details')
                ->schema([
                    Forms\Components\Select::make('collection_id')
                        ->relationship('collection', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->required(),
                    Forms\Components\Textarea::make('note')
                        ->maxLength(1000),
                    Forms\Components\DateTimePicker::make('added_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('collection.name')
                    ->label('Collection')
                    ->searchable(),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(40),
                Tables\Columns\TextColumn::make('added_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionPosts::route('/'),
            'create' => Pages\CreateCollectionPost::route('/create'),
            'edit' => Pages\EditCollectionPost::route('/{record}/edit'),
        ];
    }
}
