<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Achievements';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Achievement Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Achievement::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icon (Emoji)')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->maxLength(50),

                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'learning' => 'Learning',
                                'engagement' => 'Engagement',
                                'milestone' => 'Milestone',
                                'social' => 'Social',
                                'content' => 'Content',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->required(),

                        Forms\Components\KeyValue::make('conditions')
                            ->label('Conditions')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->colors([
                        'gray' => 'learning',
                        'info' => 'engagement',
                        'warning' => 'milestone',
                        'success' => 'social',
                        'danger' => 'content',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'learning' => 'Learning',
                        'engagement' => 'Engagement',
                        'milestone' => 'Milestone',
                        'social' => 'Social',
                        'content' => 'Content',
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
