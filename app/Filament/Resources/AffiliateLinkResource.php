<?php

namespace App\Filament\Resources;

use App\Models\AffiliateLink;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AffiliateLinkResource extends Resource
{
    protected static ?string $model = AffiliateLink::class;
    protected static ?string $slug = 'affiliate-links';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Affiliate Link')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('url')
                        ->url()
                        ->required(),
                    Forms\Components\TextInput::make('description')
                        ->maxLength(500),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Creator'),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('clicks_count')->counts('clicks')->label('Clicks'),
                Tables\Columns\TextColumn::make('conversions_count')->counts('conversions')->label('Conversions'),
                Tables\Columns\BooleanColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
}
