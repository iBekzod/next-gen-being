<?php

namespace App\Filament\Resources;

use App\Models\ScheduledPost;
use App\Filament\Resources\ScheduledPostResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ScheduledPostResource extends Resource
{
    protected static ?string $model = ScheduledPost::class;
    protected static ?string $slug = 'scheduled-posts';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('content')
                        ->required(),
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'scheduled' => 'Scheduled',
                            'published' => 'Published',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Creator'),
                Tables\Columns\TextColumn::make('scheduled_at')->dateTime(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledPosts::route('/'),
            'create' => Pages\CreateScheduledPost::route('/create'),
            'edit' => Pages\EditScheduledPost::route('/{record}/edit'),
        ];
    }
}
