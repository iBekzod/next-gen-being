<?php

namespace App\Filament\Resources;

use App\Models\AIRecommendation;
use App\Filament\Resources\AIRecommendationResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AIRecommendationResource extends Resource
{
    protected static ?string $model = AIRecommendation::class;
    protected static ?string $slug = 'ai-recommendations';
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'AI';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('AI Recommendation Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('learning_path_id')
                        ->relationship('learningPath', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('recommendation_type')
                        ->disabled(),
                    Forms\Components\TextInput::make('title')
                        ->disabled(),
                    Forms\Components\Textarea::make('description')
                        ->disabled(),
                    Forms\Components\Textarea::make('reason')
                        ->disabled(),
                    Forms\Components\TextInput::make('confidence_score')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\KeyValue::make('metadata')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('dismissed_at')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('acted_on_at')
                        ->disabled(),
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
                Tables\Columns\TextColumn::make('recommendation_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('confidence_score')
                    ->formatStateUsing(fn ($state) => round($state * 100) . '%')
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('dismissed_at')
                    ->label('Dismissed')
                    ->getStateUsing(fn ($record) => $record->dismissed_at !== null),
                Tables\Columns\BooleanColumn::make('acted_on_at')
                    ->label('Acted On')
                    ->getStateUsing(fn ($record) => $record->acted_on_at !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('recommendation_type'),
                Tables\Filters\Filter::make('dismissed')
                    ->query(fn ($query) => $query->whereNotNull('dismissed_at')),
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->whereNull('dismissed_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAIRecommendations::route('/'),
            'view' => Pages\ViewAIRecommendation::route('/{record}'),
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
