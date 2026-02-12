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
            Forms\Components\Section::make('Earning Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Blogger')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required()
                        ->disabled(),

                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'follower_milestone' => 'Follower Milestone',
                            'premium_content' => 'Premium Content',
                            'engagement_bonus' => 'Engagement Bonus',
                        ])
                        ->disabled(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('currency')
                        ->label('Currency')
                        ->default('USD')
                        ->disabled(),

                    Forms\Components\TextInput::make('milestone_value')
                        ->label('Milestone Value')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'cancelled' => 'Cancelled',
                        ])
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Payout Details')
                ->schema([
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Paid At')
                        ->disabled(),

                    Forms\Components\TextInput::make('payout_method')
                        ->label('Payout Method')
                        ->disabled(),

                    Forms\Components\TextInput::make('payout_reference')
                        ->label('Payout Reference')
                        ->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Additional Info')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->disabled()
                        ->columnSpanFull(),

                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata')
                        ->disabled()
                        ->columnSpanFull(),
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
