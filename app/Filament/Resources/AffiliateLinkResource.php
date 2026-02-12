<?php

namespace App\Filament\Resources;

use App\Models\AffiliateLink;
use App\Filament\Resources\AffiliateLinkResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AffiliateLinkResource extends Resource
{
    protected static ?string $model = AffiliateLink::class;
    protected static ?string $slug = 'affiliate-links';
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affiliate Link Details')
                ->schema([
                    Forms\Components\Select::make('creator_id')
                        ->label('Creator')
                        ->relationship('creator', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('referral_code')
                        ->label('Referral Code')
                        ->required()
                        ->unique('affiliate_links', 'referral_code', ignoreRecord: true),

                    Forms\Components\TextInput::make('affiliate_url')
                        ->label('Affiliate URL')
                        ->url()
                        ->required(),

                    Forms\Components\TextInput::make('commission_rate')
                        ->label('Commission Rate (%)')
                        ->numeric()
                        ->step(0.01),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->maxLength(500)
                        ->rows(3),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('referral_code')
                    ->label('Referral Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateLinks::route('/'),
            'create' => Pages\CreateAffiliateLink::route('/create'),
            'edit' => Pages\EditAffiliateLink::route('/{record}/edit'),
        ];
    }
}
