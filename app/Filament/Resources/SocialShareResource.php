<?php

namespace App\Filament\Resources;

use App\Models\SocialShare;
use App\Filament\Resources\SocialShareResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class SocialShareResource extends Resource
{
    protected static ?string $model = SocialShare::class;
    protected static ?string $slug = 'social-shares';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Social Share Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('platform')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_source')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_medium')
                        ->disabled(),
                    Forms\Components\TextInput::make('utm_campaign')
                        ->disabled(),
                    Forms\Components\TextInput::make('referrer')
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->disabled(),
                    Forms\Components\Textarea::make('user_agent')
                        ->disabled(),
                    Forms\Components\KeyValue::make('metadata')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('shared_at')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Shared By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('utm_source')
                    ->limit(30),
                Tables\Columns\TextColumn::make('utm_campaign')
                    ->limit(30),
                Tables\Columns\TextColumn::make('shared_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('shared_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialShares::route('/'),
            'view' => Pages\ViewSocialShare::route('/{record}'),
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
