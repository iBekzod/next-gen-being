<?php

namespace App\Filament\Resources;

use App\Models\AffiliateConversion;
use App\Filament\Resources\AffiliateConversionResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AffiliateConversionResource extends Resource
{
    protected static ?string $model = AffiliateConversion::class;
    protected static ?string $slug = 'affiliate-conversions';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Affiliate';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affiliate Conversion Details')
                ->schema([
                    Forms\Components\Select::make('affiliate_link_id')
                        ->relationship('link', 'url')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('click_id')
                        ->relationship('click', 'id')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('conversion_type')
                        ->disabled(),
                    Forms\Components\TextInput::make('conversion_value')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('commission_rate')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('commission_amount')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'refunded' => 'Refunded',
                        ])
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
                Tables\Columns\TextColumn::make('link.url')
                    ->label('Link')
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('conversion_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('conversion_value')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('conversion_type'),
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
            'index' => Pages\ListAffiliateConversions::route('/'),
            'view' => Pages\ViewAffiliateConversion::route('/{record}'),
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
