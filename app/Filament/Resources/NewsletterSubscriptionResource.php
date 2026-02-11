<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriptionResource\Pages;
use App\Models\NewsletterSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter Subscriptions';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Details')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled(),

                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('source')
                            ->label('Subscription Source')
                            ->disabled(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->disabled(),

                        Forms\Components\TextInput::make('verification_token')
                            ->label('Verification Token')
                            ->disabled()
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Subscribed At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Unsubscribed At')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Preferences')
                    ->schema([
                        Forms\Components\CheckboxList::make('frequency')
                            ->label('Email Frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->disabled(),

                        Forms\Components\CheckboxList::make('categories')
                            ->label('Interested Categories')
                            ->options([
                                'technology' => 'Technology',
                                'business' => 'Business',
                                'lifestyle' => 'Lifestyle',
                                'news' => 'News',
                            ])
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('verified_at')
                    ->label('Verified')
                    ->boolean(fn ($record) => $record->verified_at !== null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\TernaryFilter::make('verified_at')
                    ->label('Verified')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('verified_at'),
                        false: fn (Builder $query) => $query->whereNull('verified_at'),
                    ),

                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'landing_page' => 'Landing Page',
                        'blog' => 'Blog',
                        'email' => 'Email',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscriptions::route('/'),
            'view' => Pages\ViewNewsletterSubscription::route('/{record}'),
        ];
    }
}
