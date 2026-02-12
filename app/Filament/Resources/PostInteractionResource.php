<?php

namespace App\Filament\Resources;

use App\Models\PostInteraction;
use App\Filament\Resources\PostInteractionResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class PostInteractionResource extends Resource
{
    protected static ?string $model = PostInteraction::class;
    protected static ?string $slug = 'post-interactions';
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationGroup = 'Engagement';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post Interaction Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('type')
                        ->disabled(),
                    Forms\Components\KeyValue::make('metadata')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'like' => 'success',
                        'view' => 'info',
                        'comment' => 'warning',
                        'share' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'like' => 'Like',
                        'view' => 'View',
                        'comment' => 'Comment',
                        'share' => 'Share',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostInteractions::route('/'),
            'view' => Pages\ViewPostInteraction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
