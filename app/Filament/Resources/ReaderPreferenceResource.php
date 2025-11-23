<?php

namespace App\Filament\Resources;

use App\Models\ReaderPreference;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ReaderPreferenceResource extends Resource
{
    protected static ?string $model = ReaderPreference::class;
    protected static ?string $slug = 'reader-preferences';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Preference Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Textarea::make('preferred_categories'),
                    Forms\Components\Textarea::make('preferred_authors'),
                    Forms\Components\Textarea::make('preferred_tags'),
                    Forms\Components\Textarea::make('disliked_content'),
                    Forms\Components\KeyValue::make('content_type_scores')
                        ->label('Content Type Scores'),
                    Forms\Components\Textarea::make('reading_patterns'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([])
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
