<?php

namespace App\Filament\Resources;

use App\Models\AuthorStat;
use App\Filament\Resources\AuthorStatResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class AuthorStatResource extends Resource
{
    protected static ?string $model = AuthorStat::class;
    protected static ?string $slug = 'author-stats';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Author Statistics')
                ->schema([
                    Forms\Components\Select::make('author_id')
                        ->relationship('author', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_posts')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_views')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_likes')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_comments')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_followers')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('total_earnings')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('avg_post_views')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('engagement_rate')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\DatePicker::make('last_post_date')
                        ->disabled(),
                    Forms\Components\Textarea::make('top_topics')
                        ->disabled(),
                    Forms\Components\KeyValue::make('monthly_growth')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_posts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_likes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_comments')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_followers')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('engagement_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_post_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('last_post_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('total_views', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthorStats::route('/'),
            'view' => Pages\ViewAuthorStat::route('/{record}'),
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
