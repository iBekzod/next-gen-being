<?php

namespace App\Filament\Resources;

use App\Models\LearningPath;
use App\Filament\Resources\LearningPathResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class LearningPathResource extends Resource
{
    protected static ?string $model = LearningPath::class;
    protected static ?string $slug = 'learning-paths';
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Learning';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Learning Path Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(1000),
                    Forms\Components\TextInput::make('goal')
                        ->maxLength(255),
                    Forms\Components\Select::make('skill_level')
                        ->options([
                            'beginner' => 'Beginner',
                            'intermediate' => 'Intermediate',
                            'advanced' => 'Advanced',
                            'expert' => 'Expert',
                        ]),
                    Forms\Components\TextInput::make('estimated_duration_hours')
                        ->numeric(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'paused' => 'Paused',
                            'archived' => 'Archived',
                        ])
                        ->default('active'),
                    Forms\Components\Toggle::make('ai_generated')
                        ->default(false),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
                    Forms\Components\DateTimePicker::make('generated_at'),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('skill_level')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'beginner' => 'success',
                        'intermediate' => 'info',
                        'advanced' => 'warning',
                        'expert' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'paused' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('ai_generated')
                    ->boolean()
                    ->label('AI Generated'),
                Tables\Columns\TextColumn::make('estimated_duration_hours')
                    ->label('Duration (hrs)'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('skill_level'),
                Tables\Filters\TernaryFilter::make('ai_generated'),
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
            'index' => Pages\ListLearningPaths::route('/'),
            'create' => Pages\CreateLearningPath::route('/create'),
            'edit' => Pages\EditLearningPath::route('/{record}/edit'),
        ];
    }
}
