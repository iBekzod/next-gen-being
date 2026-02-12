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

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Webhook Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

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

                        Forms\Components\TextInput::make('event_type')
                            ->label('Event Type')
                            ->maxLength(255),

                        Forms\Components\CheckboxList::make('events')
                            ->label('Events to Subscribe To')
                            ->options([
                                'post.created' => 'Post Created',
                                'post.updated' => 'Post Updated',
                                'post.published' => 'Post Published',
                                'comment.created' => 'Comment Created',
                                'user.registered' => 'User Registered',
                                'subscription.created' => 'Subscription Created',
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'failed' => 'Failed',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\Toggle::make('verify_ssl')
                            ->label('Verify SSL')
                            ->default(true),

                        Forms\Components\TextInput::make('max_retries')
                            ->label('Max Retries')
                            ->numeric()
                            ->default(3)
                            ->minValue(0),

                        Forms\Components\KeyValue::make('headers')
                            ->label('Custom Headers')
                            ->keyLabel('Header')
                            ->valueLabel('Value'),
                    ]),

                Forms\Components\Section::make('Status Information')
                    ->schema([
                        Forms\Components\TextInput::make('retry_count')
                            ->label('Current Retry Count')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('last_triggered_at')
                            ->label('Last Triggered')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('last_failed_at')
                            ->label('Last Failed')
                            ->disabled(),

                        Forms\Components\Textarea::make('last_error')
                            ->label('Last Error')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_triggered_at')
                    ->label('Last Triggered')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'failed' => 'Failed',
                    ]),
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
