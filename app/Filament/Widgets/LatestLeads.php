<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\LandingLeadResource;
use App\Models\LandingLead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLeads extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LandingLeadResource::getEloquentQuery()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i')),
            ])
            ->actions([
                Tables\Actions\Action::make('send_email')
                    ->label('Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->url(fn ($record) => "mailto:{$record->email}")
                    ->openUrlInNewTab(),
            ]);
    }
}
