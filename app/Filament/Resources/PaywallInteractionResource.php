<?php

namespace App\Filament\Resources;

use App\Models\PaywallInteraction;
use App\Filament\Resources\PaywallInteractionResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class PaywallInteractionResource extends Resource
{
    protected static ?string $model = PaywallInteraction::class;
    protected static ?string $slug = 'paywall-interactions';
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Paywall Interaction Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('session_id')
                        ->disabled(),
                    Forms\Components\TextInput::make('interaction_type')
                        ->disabled(),
                    Forms\Components\TextInput::make('paywall_type')
                        ->disabled(),
                    Forms\Components\Toggle::make('converted')
                        ->disabled(),
                    Forms\Components\KeyValue::make('metadata')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('interacted_at')
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
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interaction_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('paywall_type')
                    ->badge(),
                Tables\Columns\BooleanColumn::make('converted'),
                Tables\Columns\TextColumn::make('interacted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('interaction_type'),
                Tables\Filters\TernaryFilter::make('converted'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('interacted_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaywallInteractions::route('/'),
            'view' => Pages\ViewPaywallInteraction::route('/{record}'),
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
