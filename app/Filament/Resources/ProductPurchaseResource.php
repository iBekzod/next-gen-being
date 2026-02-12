<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPurchaseResource\Pages;
use App\Models\ProductPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductPurchaseResource extends Resource
{
    protected static ?string $model = ProductPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Product Purchases';

    protected static ?string $navigationGroup = 'E-Commerce';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Purchase Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('digital_product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->maxLength(3)
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'refunded' => 'Refunded',
                                'failed' => 'Failed',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('lemonsqueezy_order_id')
                            ->label('LemonSqueezy Order ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('lemonsqueezy_receipt_url')
                            ->label('LemonSqueezy Receipt URL')
                            ->url()
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('License & Download')
                    ->schema([
                        Forms\Components\TextInput::make('license_key')
                            ->label('License Key')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('download_count')
                            ->label('Downloads')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('download_limit')
                            ->label('Download Limit')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Revenue')
                    ->schema([
                        Forms\Components\TextInput::make('creator_revenue')
                            ->label('Creator Revenue')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('platform_revenue')
                            ->label('Platform Revenue')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\Toggle::make('creator_paid')
                            ->label('Creator Paid')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Buyer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.title')
                    ->label('Product')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('usd')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'completed',
                        'info' => 'refunded',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('creator_paid')
                    ->label('Paid')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Purchased')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'refunded' => 'Refunded',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\TernaryFilter::make('creator_paid')
                    ->label('Creator Paid'),
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
            'index' => Pages\ListProductPurchases::route('/'),
            'view' => Pages\ViewProductPurchase::route('/{record}'),
        ];
    }
}
