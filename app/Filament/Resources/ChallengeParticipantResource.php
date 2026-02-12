<?php

namespace App\Filament\Resources;

use App\Models\ChallengeParticipant;
use App\Filament\Resources\ChallengeParticipantResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ChallengeParticipantResource extends Resource
{
    protected static ?string $model = ChallengeParticipant::class;
    protected static ?string $slug = 'challenge-participants';
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Engagement';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Challenge Participant Details')
                ->schema([
                    Forms\Components\Select::make('challenge_id')
                        ->relationship('challenge', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('progress')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_completed')
                        ->default(false),
                    Forms\Components\DateTimePicker::make('completed_at'),
                    Forms\Components\Toggle::make('reward_claimed')
                        ->default(false),
                    Forms\Components\DateTimePicker::make('claimed_at'),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Participant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('progress')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_completed')
                    ->label('Completed'),
                Tables\Columns\BooleanColumn::make('reward_claimed')
                    ->label('Reward Claimed'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_completed'),
                Tables\Filters\TernaryFilter::make('reward_claimed'),
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
            'index' => Pages\ListChallengeParticipants::route('/'),
            'create' => Pages\CreateChallengeParticipant::route('/create'),
            'edit' => Pages\EditChallengeParticipant::route('/{record}/edit'),
        ];
    }
}
