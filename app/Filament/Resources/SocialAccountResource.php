<?php

namespace App\Filament\Resources;

use App\Models\SocialAccount;
use App\Filament\Resources\SocialAccountResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class SocialAccountResource extends Resource
{
    protected static ?string $model = SocialAccount::class;
    protected static ?string $slug = 'social-accounts';
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Social';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Social Account Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('provider')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('provider_id')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('provider_email')
                        ->email(),
                    Forms\Components\TextInput::make('provider_name')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('avatar_url')
                        ->url(),
                    Forms\Components\TextInput::make('access_token')
                        ->password()
                        ->revealable(),
                    Forms\Components\TextInput::make('refresh_token')
                        ->password()
                        ->revealable(),
                    Forms\Components\DateTimePicker::make('expires_at'),
                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata'),
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
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider'),
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
            'index' => Pages\ListSocialAccounts::route('/'),
            'create' => Pages\CreateSocialAccount::route('/create'),
            'edit' => Pages\EditSocialAccount::route('/{record}/edit'),
        ];
    }
}
