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

                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->disabled(),

                        Forms\Components\TextInput::make('views')
                            ->label('Views')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('unique_readers')
                            ->label('Unique Readers')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('likes')
                            ->label('Likes')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('comments')
                            ->label('Comments')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('shares')
                            ->label('Shares')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('avg_read_time')
                            ->label('Avg Read Time (seconds)')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('scroll_depth')
                            ->label('Scroll Depth (%)')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Traffic Data')
                    ->schema([
                        Forms\Components\KeyValue::make('traffic_sources')
                            ->label('Traffic Sources')
                            ->disabled(),

                        Forms\Components\KeyValue::make('device_breakdown')
                            ->label('Device Breakdown')
                            ->disabled(),
                    ])->collapsed(),
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

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('unique_readers')
                    ->label('Readers')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('likes')
                    ->label('Likes')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('comments')
                    ->label('Comments')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('shares')
                    ->label('Shares')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('avg_read_time')
                    ->label('Avg Read')
                    ->formatStateUsing(fn ($state) => $state ? round($state) . 's' : '-')
                    ->sortable()
                    ->alignRight(),
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
            ->defaultSort('date', 'desc')
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
