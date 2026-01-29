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
    protected static ?string $slug = 'creator-analytics';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Analytics Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('date')
                        ->required(),
                    Forms\Components\TextInput::make('views')
                        ->numeric(),
                    Forms\Components\TextInput::make('likes')
                        ->numeric(),
                    Forms\Components\TextInput::make('comments')
                        ->numeric(),
                    Forms\Components\TextInput::make('shares')
                        ->numeric(),
                    Forms\Components\TextInput::make('reads')
                        ->numeric(),
                    Forms\Components\TextInput::make('revenue')
                        ->numeric()
                        ->step(0.01),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Creator'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('views'),
                Tables\Columns\TextColumn::make('likes'),
                Tables\Columns\TextColumn::make('comments'),
                Tables\Columns\TextColumn::make('revenue'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreatorAnalytics::route('/'),
            'create' => Pages\CreateCreatorAnalytic::route('/create'),
            'edit' => Pages\EditCreatorAnalytic::route('/{record}/edit'),
        ];
    }
}
