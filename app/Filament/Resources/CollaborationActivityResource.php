<?php

namespace App\Filament\Resources;

use App\Models\CollaborationActivity;
use App\Filament\Resources\CollaborationActivityResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CollaborationActivityResource extends Resource
{
    protected static ?string $model = CollaborationActivity::class;
    protected static ?string $slug = 'collaboration-activities';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Collaboration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collaboration Activity Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->disabled(),
                    Forms\Components\TextInput::make('action')
                        ->disabled(),
                    Forms\Components\Textarea::make('description')
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
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'invited' => 'info',
                        'joined' => 'success',
                        'left' => 'warning',
                        'role_changed' => 'primary',
                        'content_edited' => 'warning',
                        'comment_added' => 'info',
                        'comment_resolved' => 'success',
                        'version_created' => 'primary',
                        'published' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'invited' => 'Invited',
                        'joined' => 'Joined',
                        'left' => 'Left',
                        'role_changed' => 'Role Changed',
                        'content_edited' => 'Content Edited',
                        'comment_added' => 'Comment Added',
                        'comment_resolved' => 'Comment Resolved',
                        'version_created' => 'Version Created',
                        'published' => 'Published',
                    ]),
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
            'index' => Pages\ListCollaborationActivities::route('/'),
            'view' => Pages\ViewCollaborationActivity::route('/{record}'),
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
