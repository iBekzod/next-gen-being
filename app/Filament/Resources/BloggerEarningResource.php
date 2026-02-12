<?php

namespace App\Filament\Resources;

use App\Models\BloggerEarning;
use App\Filament\Resources\BloggerEarningResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class BloggerEarningResource extends Resource
{
    protected static ?string $model = BloggerEarning::class;
    protected static ?string $slug = 'blogger-earnings';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Blogger Earning Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('type')
                        ->disabled(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('currency')
                        ->default('USD')
                        ->disabled(),
                    Forms\Components\TextInput::make('milestone_value')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'cancelled' => 'Cancelled',
                        ])
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->disabled(),
                    Forms\Components\TextInput::make('payout_method')
                        ->disabled(),
                    Forms\Components\TextInput::make('payout_reference')
                        ->disabled(),
                    Forms\Components\Textarea::make('notes')
                        ->disabled(),
                    Forms\Components\KeyValue::make('metadata')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Blogger')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('type'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBloggerEarnings::route('/'),
            'view' => Pages\ViewBloggerEarning::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
