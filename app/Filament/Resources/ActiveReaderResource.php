<?php

namespace App\Filament\Resources;

use App\Models\ActiveReader;
use App\Filament\Resources\ActiveReaderResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ActiveReaderResource extends Resource
{
    protected static ?string $model = ActiveReader::class;
    protected static ?string $slug = 'active-readers';
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Analytics';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Active Reader Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('session_id')
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->disabled(),
                    Forms\Components\Textarea::make('user_agent')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('started_viewing_at')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('last_activity_at')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('left_at')
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
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_viewing_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('left_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_only')
                    ->query(fn ($query) => $query->whereNull('left_at')),
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
            'index' => Pages\ListActiveReaders::route('/'),
            'view' => Pages\ViewActiveReader::route('/{record}'),
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
