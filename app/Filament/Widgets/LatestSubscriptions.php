<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SubscriptionResource;
use LemonSqueezy\Laravel\Subscription;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSubscriptions extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SubscriptionResource::getEloquentQuery()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'paused',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('renews_at')
                    ->dateTime()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
