<?php

namespace App\Filament\Resources;

use App\Models\NotificationPreference;
use App\Filament\Resources\NotificationPreferenceResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class NotificationPreferenceResource extends Resource
{
    protected static ?string $model = NotificationPreference::class;
    protected static ?string $slug = 'notification-preferences';
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Email Notifications')
                ->schema([
                    Forms\Components\Toggle::make('email_comment_reply')
                        ->label('Comment Replies'),
                    Forms\Components\Toggle::make('email_post_liked')
                        ->label('Post Liked'),
                    Forms\Components\Toggle::make('email_post_commented')
                        ->label('Post Commented'),
                    Forms\Components\Toggle::make('email_user_followed')
                        ->label('User Followed'),
                    Forms\Components\Toggle::make('email_mention')
                        ->label('Mentions'),
                ]),
            Forms\Components\Section::make('In-App Notifications')
                ->schema([
                    Forms\Components\Toggle::make('app_comment_reply')
                        ->label('Comment Replies'),
                    Forms\Components\Toggle::make('app_post_liked')
                        ->label('Post Liked'),
                    Forms\Components\Toggle::make('app_post_commented')
                        ->label('Post Commented'),
                    Forms\Components\Toggle::make('app_user_followed')
                        ->label('User Followed'),
                    Forms\Components\Toggle::make('app_mention')
                        ->label('Mentions'),
                ]),
            Forms\Components\Section::make('Digest Settings')
                ->schema([
                    Forms\Components\Toggle::make('digest_enabled')
                        ->label('Enable Digest'),
                    Forms\Components\Select::make('digest_frequency')
                        ->options([
                            'daily' => 'Daily',
                            'weekly' => 'Weekly',
                            'monthly' => 'Monthly',
                        ])
                        ->label('Digest Frequency'),
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
                Tables\Columns\BooleanColumn::make('email_comment_reply')
                    ->label('Email Comments'),
                Tables\Columns\BooleanColumn::make('app_comment_reply')
                    ->label('App Comments'),
                Tables\Columns\BooleanColumn::make('digest_enabled')
                    ->label('Digest'),
                Tables\Columns\TextColumn::make('digest_frequency')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('digest_enabled'),
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
            'index' => Pages\ListNotificationPreferences::route('/'),
            'create' => Pages\CreateNotificationPreference::route('/create'),
            'edit' => Pages\EditNotificationPreference::route('/{record}/edit'),
        ];
    }
}
