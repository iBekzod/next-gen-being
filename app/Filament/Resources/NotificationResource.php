<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('actor_id')
                            ->label('Triggered By')
                            ->relationship('actor', 'name')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('type')
                            ->label('Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->disabled(),

                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('action_url')
                            ->label('Action URL')
                            ->url()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('read_at')
                            ->label('Read At')
                            ->disabled(),

                        Forms\Components\Textarea::make('data')
                            ->label('Additional Data (JSON)')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('read_at')
                    ->label('Read')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Unread'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('Read')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('read_at'),
                        false: fn (Builder $query) => $query->whereNull('read_at'),
                    ),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'comment' => 'Comment',
                        'like' => 'Like',
                        'follow' => 'Follow',
                        'mention' => 'Mention',
                        'system' => 'System',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListNotifications::route('/'),
            'view' => Pages\ViewNotification::route('/{record}'),
        ];
    }
}
