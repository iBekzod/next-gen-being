<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostAnalyticResource\Pages;
use App\Models\PostAnalytic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostAnalyticResource extends Resource
{
    protected static ?string $model = PostAnalytic::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Post Analytics';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Analytics')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('views_count')
                            ->label('Views')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('unique_visitors')
                            ->label('Unique Visitors')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('avg_time_on_page')
                            ->label('Avg Time on Page (seconds)')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('bounce_rate')
                            ->label('Bounce Rate (%)')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('shares_count')
                            ->label('Shares')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('comments_count')
                            ->label('Comments')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('impressions')
                            ->label('Impressions')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('clicks')
                            ->label('Clicks')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('ctr')
                            ->label('Click Through Rate (%)')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Visitors')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('avg_time_on_page')
                    ->label('Avg Time')
                    ->formatStateUsing(fn ($state) => $state ? round($state) . 's' : '-')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('bounce_rate')
                    ->label('Bounce Rate')
                    ->formatStateUsing(fn ($state) => $state ? round($state, 1) . '%' : '-')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('views_count', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('post'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostAnalytics::route('/'),
            'view' => Pages\ViewPostAnalytic::route('/{record}'),
        ];
    }
}
