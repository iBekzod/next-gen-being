<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Message Details')
                    ->schema([
                        Forms\Components\Select::make('conversation_id')
                            ->label('Conversation')
                            ->relationship('conversation', 'subject')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('sender_id')
                            ->label('Sender')
                            ->relationship('sender', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('type')
                            ->label('Type')
                            ->disabled(),

                        Forms\Components\Textarea::make('body')
                            ->label('Body')
                            ->rows(5)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_edited')
                            ->label('Edited')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('edited_at')
                            ->label('Edited At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('read_at')
                            ->label('Read At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversation.subject')
                    ->label('Conversation')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('body')
                    ->label('Message')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_edited')
                    ->label('Edited')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean(fn ($state) => $state !== null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_edited')
                    ->label('Edited'),

                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('Read')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('read_at'),
                        false: fn ($query) => $query->whereNull('read_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListMessages::route('/'),
            'view' => Pages\ViewMessage::route('/{record}'),
        ];
    }
}
