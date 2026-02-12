<?php

namespace App\Filament\Resources;

use App\Models\NewsletterEngagement;
use App\Filament\Resources\NewsletterEngagementResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class NewsletterEngagementResource extends Resource
{
    protected static ?string $model = NewsletterEngagement::class;
    protected static ?string $slug = 'newsletter-engagement';
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Newsletter Engagement')
                ->schema([
                    Forms\Components\Select::make('campaign_id')
                        ->relationship('campaign', 'subject')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('subscription_id')
                        ->relationship('subscription', 'email')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Toggle::make('opened')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('opened_at')
                        ->disabled(),
                    Forms\Components\Toggle::make('clicked')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('clicked_at')
                        ->disabled(),
                    Forms\Components\TextInput::make('clicked_url')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign.subject')
                    ->label('Campaign')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subscription.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\BooleanColumn::make('opened'),
                Tables\Columns\TextColumn::make('opened_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('clicked'),
                Tables\Columns\TextColumn::make('clicked_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('opened'),
                Tables\Filters\TernaryFilter::make('clicked'),
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
            'index' => Pages\ListNewsletterEngagement::route('/'),
            'view' => Pages\ViewNewsletterEngagement::route('/{record}'),
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
