<?php

namespace App\Filament\Resources;

use App\Models\ReaderAnalytics;
use App\Filament\Resources\ReaderAnalyticsResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ReaderAnalyticsResource extends Resource
{
    protected static ?string $model = ReaderAnalytics::class;
    protected static ?string $slug = 'reader-analytics';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Reader Analytics Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_readers_today')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('authenticated_readers_today')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('anonymous_readers_today')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('peak_concurrent_readers')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('peak_time')
                        ->disabled(),
                    Forms\Components\DatePicker::make('date')
                        ->disabled(),
                    Forms\Components\KeyValue::make('top_countries')
                        ->disabled(),
                    Forms\Components\KeyValue::make('hourly_breakdown')
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
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_readers_today')
                    ->label('Total Readers')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('authenticated_readers_today')
                    ->label('Authenticated')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anonymous_readers_today')
                    ->label('Anonymous')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('peak_concurrent_readers')
                    ->label('Peak Concurrent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('peak_time')
                    ->label('Peak Time'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ]),
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
            'index' => Pages\ListReaderAnalytics::route('/'),
            'view' => Pages\ViewReaderAnalytics::route('/{record}'),
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
