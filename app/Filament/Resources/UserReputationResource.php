<?php

namespace App\Filament\Resources;

use App\Models\UserReputation;
use App\Filament\Resources\UserReputationResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class UserReputationResource extends Resource
{
    protected static ?string $model = UserReputation::class;
    protected static ?string $slug = 'user-reputation';
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Social';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('User Reputation Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('points')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('posts_published')
                        ->numeric(),
                    Forms\Components\TextInput::make('posts_liked')
                        ->numeric(),
                    Forms\Components\TextInput::make('comments_received')
                        ->numeric(),
                    Forms\Components\TextInput::make('followers_count')
                        ->numeric(),
                    Forms\Components\TextInput::make('engagement_score')
                        ->numeric(),
                    Forms\Components\Select::make('level')
                        ->options([
                            'beginner' => 'Beginner',
                            'intermediate' => 'Intermediate',
                            'advanced' => 'Advanced',
                            'expert' => 'Expert',
                            'legend' => 'Legend',
                        ]),
                    Forms\Components\TextInput::make('level_progress')
                        ->numeric(),
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
                Tables\Columns\TextColumn::make('points')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'beginner' => 'gray',
                        'intermediate' => 'blue',
                        'advanced' => 'purple',
                        'expert' => 'orange',
                        'legend' => 'red',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('level_progress')
                    ->label('Level Progress %'),
                Tables\Columns\TextColumn::make('posts_published')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('followers_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('engagement_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level'),
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
            ->defaultSort('points', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserReputations::route('/'),
            'create' => Pages\CreateUserReputation::route('/create'),
            'edit' => Pages\EditUserReputation::route('/{record}/edit'),
        ];
    }
}
