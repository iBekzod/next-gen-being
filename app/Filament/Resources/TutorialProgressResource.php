<?php

namespace App\Filament\Resources;

use App\Models\TutorialProgress;
use App\Filament\Resources\TutorialProgressResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class TutorialProgressResource extends Resource
{
    protected static ?string $model = TutorialProgress::class;
    protected static ?string $slug = 'tutorial-progress';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tutorial Progress Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('series_slug')
                        ->label('Series Slug'),
                    Forms\Components\TextInput::make('series_part')
                        ->label('Series Part'),
                    Forms\Components\TextInput::make('read_count')
                        ->numeric()
                        ->default(0),
                    Forms\Components\TextInput::make('time_spent_minutes')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('completed')
                        ->default(false),
                    Forms\Components\DateTimePicker::make('started_at'),
                    Forms\Components\DateTimePicker::make('completed_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('series_slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('read_count')
                    ->label('Reads'),
                Tables\Columns\TextColumn::make('time_spent_minutes')
                    ->label('Time Spent (min)'),
                Tables\Columns\BooleanColumn::make('completed'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('completed'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutorialProgress::route('/'),
            'create' => Pages\CreateTutorialProgress::route('/create'),
            'edit' => Pages\EditTutorialProgress::route('/{record}/edit'),
        ];
    }
}
