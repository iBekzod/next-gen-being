<?php

namespace App\Filament\Resources;

use App\Models\AffiliateClick;
use App\Filament\Resources\AffiliateClickResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AffiliateClickResource extends Resource
{
    protected static ?string $model = AffiliateClick::class;
    protected static ?string $slug = 'affiliate-clicks';
    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';
    protected static ?string $navigationGroup = 'Affiliate';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affiliate Click Details')
                ->schema([
                    Forms\Components\Select::make('affiliate_link_id')
                        ->relationship('link', 'url')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->disabled(),
                    Forms\Components\Textarea::make('user_agent')
                        ->disabled(),
                    Forms\Components\TextInput::make('referrer')
                        ->disabled(),
                    Forms\Components\Toggle::make('converted')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('link.url')
                    ->label('Link')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referrer')
                    ->limit(50),
                Tables\Columns\BooleanColumn::make('converted'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('converted'),
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
            'index' => Pages\ListAffiliateClicks::route('/'),
            'view' => Pages\ViewAffiliateClick::route('/{record}'),
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
