<?php

namespace App\Filament\Resources;

use App\Models\Streak;
use App\Filament\Resources\StreakResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class StreakResource extends Resource
{
    protected static ?string $model = Streak::class;
    protected static ?string $slug = 'streaks';
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationGroup = 'Engagement';

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
                    Forms\Components\TextInput::make('current_count')
                        ->numeric()
                        ->default(0),
                    Forms\Components\TextInput::make('longest_count')
                        ->numeric()
                        ->default(0),
                    Forms\Components\DatePicker::make('last_activity_date'),
                    Forms\Components\DateTimePicker::make('broken_at'),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'reading' => 'info',
                        'writing' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('current_count')->label('Current')->numeric(),
                Tables\Columns\TextColumn::make('longest_count')->label('Longest')->numeric(),
                Tables\Columns\TextColumn::make('last_activity_date')->date()->sortable(),
                Tables\Columns\BooleanColumn::make('broken_at')
                    ->label('Active')
                    ->getStateUsing(fn ($record) => $record->broken_at === null),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStreaks::route('/'),
            'create' => Pages\CreateStreak::route('/create'),
            'edit' => Pages\EditStreak::route('/{record}/edit'),
        ];
    }
}
