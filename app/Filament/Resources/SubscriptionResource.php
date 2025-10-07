<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Commerce';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('paddle_id')
                                    ->label('Paddle Customer ID')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('paddle_subscription_id')
                                    ->label('Paddle Subscription ID')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('paddle_price_id')
                                    ->label('Paddle Price ID')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('variant_id')
                                    ->maxLength(255),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'paused' => 'Paused',
                                        'cancelled' => 'Cancelled',
                                        'expired' => 'Expired',
                                        'past_due' => 'Past Due',
                                    ])
                                    ->required()
                                    ->default('active'),
                            ]),
                    ]),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('card_brand')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('card_last_four')
                                    ->maxLength(4)
                                    ->length(4),
                            ]),
                    ]),

                Forms\Components\Section::make('Important Dates')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DateTimePicker::make('trial_ends_at')
                                    ->label('Trial Ends At'),
                                Forms\Components\DateTimePicker::make('renews_at')
                                    ->label('Renews At'),
                                Forms\Components\DateTimePicker::make('ends_at')
                                    ->label('Ends At'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        'past_due' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->label('Product')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('card_brand')
                    ->badge()
                    ->icon(fn ($state) => match($state) {
                        'visa' => 'heroicon-o-credit-card',
                        'mastercard' => 'heroicon-o-credit-card',
                        'amex' => 'heroicon-o-credit-card',
                        default => 'heroicon-o-credit-card',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('card_last_four')
                    ->label('Card')
                    ->formatStateUsing(fn ($state) => $state ? "•••• {$state}" : null)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('onTrial')
                    ->label('Trial')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->onTrial())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('renews_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'past_due' => 'Past Due',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('trial')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereNotNull('trial_ends_at')
                              ->where('trial_ends_at', '>', now())
                    )
                    ->label('On Trial'),
                Tables\Filters\Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereBetween('renews_at', [now(), now()->addDays(7)])
                    )
                    ->label('Expiring in 7 Days'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_paddle')
                    ->label('View in Paddle')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->paddle_subscription_id
                        ? "https://vendors.paddle.com/subscriptions/{$record->paddle_subscription_id}"
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->paddle_subscription_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Subscription Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Customer'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Plan Name'),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'active' => 'success',
                                        'paused' => 'warning',
                                        'cancelled' => 'danger',
                                        'expired' => 'secondary',
                                        default => 'primary',
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Payment Details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('card_brand')
                                    ->label('Card Brand'),
                                Infolists\Components\TextEntry::make('card_last_four')
                                    ->label('Last 4 Digits')
                                    ->formatStateUsing(fn ($state) => $state ? "•••• {$state}" : 'N/A'),
                                Infolists\Components\IconEntry::make('onTrial')
                                    ->label('On Trial')
                                    ->boolean()
                                    ->getStateUsing(fn ($record) => $record->onTrial()),
                            ]),
                    ]),

                Infolists\Components\Section::make('Paddle Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('paddle_id')
                                    ->label('Paddle Customer ID')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('paddle_subscription_id')
                                    ->label('Paddle Subscription ID')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('paddle_price_id')
                                    ->label('Paddle Price ID')
                                    ->copyable(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Important Dates')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('trial_ends_at')
                                    ->label('Trial Ends')
                                    ->dateTime()
                                    ->since(),
                                Infolists\Components\TextEntry::make('renews_at')
                                    ->label('Renews At')
                                    ->dateTime()
                                    ->since(),
                                Infolists\Components\TextEntry::make('ends_at')
                                    ->label('Ends At')
                                    ->dateTime()
                                    ->since(),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::where('status', 'active')->count() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
