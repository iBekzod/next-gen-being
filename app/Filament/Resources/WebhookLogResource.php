<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookLogResource\Pages;
use App\Models\WebhookLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebhookLogResource extends Resource
{
    protected static ?string $model = WebhookLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Webhook Logs';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Webhook Log Details')
                    ->schema([
                        Forms\Components\Select::make('webhook_id')
                            ->label('Webhook')
                            ->relationship('webhook', 'url')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('event')
                            ->label('Event')
                            ->disabled(),

                        Forms\Components\TextInput::make('status_code')
                            ->label('HTTP Status Code')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\Textarea::make('request_body')
                            ->label('Request Body')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('response_body')
                            ->label('Response Body')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('error_message')
                            ->label('Error Message')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('headers')
                            ->label('Response Headers (JSON)')
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
                Tables\Columns\TextColumn::make('webhook.url')
                    ->label('Webhook')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 200,
                        'warning' => [201, 202],
                        'danger' => fn ($state) => (int)$state >= 400,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Attempted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_code')
                    ->label('Status')
                    ->options([
                        '200' => '200 Success',
                        '201' => '201 Created',
                        '400' => '400 Bad Request',
                        '401' => '401 Unauthorized',
                        '404' => '404 Not Found',
                        '500' => '500 Server Error',
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
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->latest());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhookLogs::route('/'),
            'view' => Pages\ViewWebhookLog::route('/{record}'),
        ];
    }
}
