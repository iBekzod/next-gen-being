<?php

namespace App\Filament\Resources;

use App\Models\ContentView;
use App\Filament\Resources\ContentViewResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ContentViewResource extends Resource
{
    protected static ?string $model = ContentView::class;
    protected static ?string $slug = 'content-views';
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Content View Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('session_id')
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->disabled(),
                    Forms\Components\Textarea::make('user_agent')
                        ->disabled(),
                    Forms\Components\Toggle::make('is_premium_content')
                        ->disabled(),
                    Forms\Components\Toggle::make('viewed_as_trial')
                        ->disabled(),
                    Forms\Components\Toggle::make('converted_to_paid')
                        ->disabled(),
                    Forms\Components\TextInput::make('time_on_page')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\TextInput::make('scroll_depth')
                        ->numeric()
                        ->disabled(),
                    Forms\Components\Toggle::make('clicked_upgrade')
                        ->disabled(),
                    Forms\Components\TextInput::make('referrer')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('viewed_at')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('session_id')
                    ->limit(20),
                Tables\Columns\BooleanColumn::make('is_premium_content')
                    ->label('Premium'),
                Tables\Columns\BooleanColumn::make('converted_to_paid')
                    ->label('Converted'),
                Tables\Columns\TextColumn::make('time_on_page')
                    ->label('Time (sec)'),
                Tables\Columns\TextColumn::make('scroll_depth')
                    ->label('Scroll %'),
                Tables\Columns\TextColumn::make('viewed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_premium_content'),
                Tables\Filters\TernaryFilter::make('converted_to_paid'),
                Tables\Filters\TernaryFilter::make('clicked_upgrade'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('viewed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentViews::route('/'),
            'view' => Pages\ViewContentView::route('/{record}'),
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
