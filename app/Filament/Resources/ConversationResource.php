<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $navigationGroup = 'Messaging';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversation Details')
                    ->schema([
                        Forms\Components\Select::make('user_1_id')
                            ->label('User 1')
                            ->relationship('user1', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('user_2_id')
                            ->label('User 2')
                            ->relationship('user2', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->disabled(),

                        Forms\Components\Toggle::make('is_archived')
                            ->label('Archived'),

                        Forms\Components\DateTimePicker::make('last_message_at')
                            ->label('Last Message At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user1.name')
                    ->label('User 1')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user2.name')
                    ->label('User 2')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_archived')
                    ->label('Archived')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Last Message')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archived'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }
}
