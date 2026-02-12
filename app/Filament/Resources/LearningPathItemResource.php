<?php

namespace App\Filament\Resources;

use App\Models\LearningPathItem;
use App\Filament\Resources\LearningPathItemResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class LearningPathItemResource extends Resource
{
    protected static ?string $model = LearningPathItem::class;
    protected static ?string $slug = 'learning-path-items';
    protected static ?string $navigationIcon = 'heroicon-o-book-mark';
    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Learning Path Item Details')
                ->schema([
                    Forms\Components\Select::make('learning_path_id')
                        ->relationship('learningPath', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('order')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(1000),
                    Forms\Components\Textarea::make('reason_for_recommendation')
                        ->maxLength(1000),
                    Forms\Components\Select::make('difficulty_level')
                        ->options([
                            'beginner' => 'Beginner',
                            'intermediate' => 'Intermediate',
                            'advanced' => 'Advanced',
                            'expert' => 'Expert',
                        ]),
                    Forms\Components\TextInput::make('estimated_duration_minutes')
                        ->numeric(),
                    Forms\Components\Toggle::make('completed')
                        ->default(false),
                    Forms\Components\DateTimePicker::make('completed_at'),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('learningPath.name')
                    ->label('Learning Path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty_level')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'beginner' => 'success',
                        'intermediate' => 'info',
                        'advanced' => 'warning',
                        'expert' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('estimated_duration_minutes')
                    ->label('Duration (min)'),
                Tables\Columns\BooleanColumn::make('completed'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty_level'),
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
            ->defaultSort('learning_path_id', 'asc')
            ->defaultSort('order', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningPathItems::route('/'),
            'create' => Pages\CreateLearningPathItem::route('/create'),
            'edit' => Pages\EditLearningPathItem::route('/{record}/edit'),
        ];
    }
}
