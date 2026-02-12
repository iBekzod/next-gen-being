<?php

namespace App\Filament\Resources;

use App\Models\Challenge;
use App\Filament\Resources\ChallengeResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;
    protected static ?string $slug = 'challenges';
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Engagement';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Challenge Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->maxLength(1000)
                        ->rows(4),

                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'reading' => 'Reading',
                            'writing' => 'Writing',
                            'engagement' => 'Engagement',
                            'community' => 'Community',
                        ])
                        ->required(),

                    Forms\Components\Select::make('difficulty')
                        ->label('Difficulty')
                        ->options([
                            'easy' => 'Easy',
                            'medium' => 'Medium',
                            'hard' => 'Hard',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('target_value')
                        ->label('Target Value')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('icon')
                        ->label('Icon (Emoji)')
                        ->maxLength(10),
                ])->columns(2),

            Forms\Components\Section::make('Dates')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Start Date')
                        ->required(),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('End Date')
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Rewards')
                ->schema([
                    Forms\Components\TextInput::make('reward_points')
                        ->label('Reward Points')
                        ->numeric(),

                    Forms\Components\Textarea::make('reward_description')
                        ->label('Reward Description')
                        ->maxLength(500)
                        ->rows(3),
                ])->columns(2),

            Forms\Components\Section::make('Settings')
                ->schema([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active'),

                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
                ]),
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

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'gray' => 'reading',
                        'info' => 'writing',
                        'warning' => 'engagement',
                        'success' => 'community',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('difficulty')
                    ->label('Difficulty')
                    ->badge()
                    ->colors([
                        'gray' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_value')
                    ->label('Target')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'reading' => 'Reading',
                        'writing' => 'Writing',
                        'engagement' => 'Engagement',
                        'community' => 'Community',
                    ]),

                Tables\Filters\SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}
