<?php

namespace App\Filament\Resources;

use App\Models\Tip;
use App\Filament\Resources\TipResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class TipResource extends Resource
{
    protected static ?string $model = Tip::class;
    protected static ?string $slug = 'tips';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Community';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tip Details')
                ->schema([
                    Forms\Components\Select::make('from_user_id')
                        ->relationship('fromUser', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('to_user_id')
                        ->relationship('toUser', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->minValue(1),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('message')
                        ->maxLength(500),
                    Forms\Components\Toggle::make('is_anonymous'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('fromUser.name')->label('From User'),
                Tables\Columns\TextColumn::make('toUser.name')->label('To User'),
                Tables\Columns\TextColumn::make('amount')->sortable(),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTips::route('/'),
            'create' => Pages\CreateTip::route('/create'),
            'edit' => Pages\EditTip::route('/{record}/edit'),
        ];
    }
}
