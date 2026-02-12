<?php

namespace App\Filament\Resources;

use App\Models\CreatorAnalytic;
use App\Filament\Resources\CreatorAnalyticResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CreatorAnalyticResource extends Resource
{
    protected static ?string $model = CreatorAnalytic::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Creator Analytics';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Creator Information')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Creator')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required()
                        ->disabled(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Post Metrics')
                ->schema([
                    Forms\Components\TextInput::make('posts_published')
                        ->label('Posts Published')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('posts_views')
                        ->label('Total Views')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('posts_likes')
                        ->label('Total Likes')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('posts_comments')
                        ->label('Total Comments')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('posts_shares')
                        ->label('Total Shares')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('average_read_time')
                        ->label('Average Read Time')
                        ->numeric()
                        ->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Follower Metrics')
                ->schema([
                    Forms\Components\TextInput::make('followers_gained')
                        ->label('Followers Gained')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('followers_lost')
                        ->label('Followers Lost')
                        ->numeric()
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Revenue Metrics')
                ->schema([
                    Forms\Components\TextInput::make('tips_received')
                        ->label('Tips Received')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('tips_amount')
                        ->label('Tips Amount')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('subscription_revenue')
                        ->label('Subscription Revenue')
                        ->numeric()
                        ->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Engagement Metrics')
                ->schema([
                    Forms\Components\TextInput::make('total_engagement')
                        ->label('Total Engagement')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('engagement_rate')
                        ->label('Engagement Rate')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('bounce_rate')
                        ->label('Bounce Rate')
                        ->numeric()
                        ->disabled(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('posts_published')
                    ->label('Posts')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('posts_views')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('posts_likes')
                    ->label('Likes')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('posts_comments')
                    ->label('Comments')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('followers_gained')
                    ->label('Followers Gained')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('engagement_rate')
                    ->label('Engagement Rate')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('subscription_revenue')
                    ->label('Revenue')
                    ->money('usd')
                    ->sortable()
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($q) => $q->whereDate('date', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn ($q) => $q->whereDate('date', '<=', $data['until'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreatorAnalytics::route('/'),
            'view' => Pages\ViewCreatorAnalytic::route('/{record}'),
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
