<?php

namespace App\Filament\Resources;

use App\Models\Challenge;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;
    protected static ?string $slug = 'challenges';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Challenge Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->maxLength(1000),
                    Forms\Components\Select::make('type')
                        ->options([
                            'reading' => 'Reading',
                            'writing' => 'Writing',
                            'engagement' => 'Engagement',
                            'milestone' => 'Milestone',
                        ])
                        ->required(),
                    Forms\Components\Select::make('difficulty')
                        ->options([
                            'easy' => 'Easy',
                            'medium' => 'Medium',
                            'hard' => 'Hard',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('goal')
                        ->numeric()
                        ->required(),
                    Forms\Components\DatePicker::make('start_date')->required(),
                    Forms\Components\DatePicker::make('end_date')->required(),
                    Forms\Components\TextInput::make('reward_points')
                        ->numeric(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('difficulty')->badge(),
                Tables\Columns\TextColumn::make('goal'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('status'),
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
