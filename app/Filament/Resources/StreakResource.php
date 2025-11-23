<?php

namespace App\Filament\Resources;

use App\Models\Streak;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class StreakResource extends Resource
{
    protected static ?string $model = Streak::class;
    protected static ?string $slug = 'streaks';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Streak Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('type')
                        ->options([
                            'reading' => 'Reading',
                            'writing' => 'Writing',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('current_streak')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('longest_streak')
                        ->numeric(),
                    Forms\Components\DatePicker::make('last_activity_date'),
                    Forms\Components\DatePicker::make('started_at')->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('current_streak')->label('Current'),
                Tables\Columns\TextColumn::make('longest_streak')->label('Longest'),
                Tables\Columns\TextColumn::make('last_activity_date')->date(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
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
}
