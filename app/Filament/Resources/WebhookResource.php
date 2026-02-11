<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Webhook Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Webhook URL')
                            ->url()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('user_id')
                            ->label('Owner')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\CheckboxList::make('events')
                            ->label('Events to Subscribe To')
                            ->options([
                                'post.created' => 'Post Created',
                                'post.updated' => 'Post Updated',
                                'post.published' => 'Post Published',
                                'comment.created' => 'Comment Created',
                                'user.registered' => 'User Registered',
                                'subscription.created' => 'Subscription Created',
                            ])
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
