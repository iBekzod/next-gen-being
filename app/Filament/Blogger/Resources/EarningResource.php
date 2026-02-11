<?php

namespace App\Filament\Blogger\Resources;

use App\Filament\Blogger\Resources\EarningResource\Pages;
use App\Models\BloggerEarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EarningResource extends Resource
{
    protected static ?string $model = BloggerEarning::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'My Earnings';

    protected static ?string $modelLabel = 'Earning';

    protected static ?string $pluralModelLabel = 'My Earnings';

    protected static ?string $navigationGroup = 'Earnings';

    // Only show earnings for the current blogger
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function canCreate(): bool
    {
        return false; // Earnings are system-generated
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'follower_milestone' => 'Follower Milestone',
                        'premium_content' => 'Premium Content',
                        'engagement_bonus' => 'Engagement Bonus',
                        'manual_adjustment' => 'Manual Adjustment',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'follower_milestone' => 'success',
                        'premium_content' => 'warning',
                        'engagement_bonus' => 'info',
                        'manual_adjustment' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('milestone_value')
                    ->label('Milestone')
                    ->formatStateUsing(fn ($state, BloggerEarning $record): string =>
                        $record->type === 'follower_milestone' && $state
                            ? "{$state} followers"
                            : '-'
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not paid yet'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Earned On')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'follower_milestone' => 'Follower Milestone',
                        'premium_content' => 'Premium Content',
                        'engagement_bonus' => 'Engagement Bonus',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (BloggerEarning $record): \Illuminate\Contracts\View\View => view(
                        'filament.blogger.earning-details',
                        ['record' => $record],
                    )),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEarnings::route('/'),
        ];
    }
}
