<?php

namespace App\Filament\Resources;

use App\Models\ReaderLocation;
use App\Filament\Resources\ReaderLocationResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ReaderLocationResource extends Resource
{
    protected static ?string $model = ReaderLocation::class;
    protected static ?string $slug = 'reader-locations';
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Reader Location Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->disabled(),
                    Forms\Components\TextInput::make('country_code')
                        ->disabled(),
                    Forms\Components\TextInput::make('country_name')
                        ->disabled(),
                    Forms\Components\TextInput::make('state_province')
                        ->disabled(),
                    Forms\Components\TextInput::make('city')
                        ->disabled(),
                    Forms\Components\TextInput::make('latitude')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('longitude')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('timezone')
                        ->disabled(),
                    Forms\Components\TextInput::make('isp')
                        ->disabled(),
                    Forms\Components\TextInput::make('reader_count')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('last_seen_at')
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
                Tables\Columns\TextColumn::make('country_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state_province')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reader_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('isp')
                    ->limit(30),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country_name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('reader_count', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReaderLocations::route('/'),
            'view' => Pages\ViewReaderLocation::route('/{record}'),
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
