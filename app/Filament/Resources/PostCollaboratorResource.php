<?php

namespace App\Filament\Resources;

use App\Models\PostCollaborator;
use App\Filament\Resources\PostCollaboratorResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class PostCollaboratorResource extends Resource
{
    protected static ?string $model = PostCollaborator::class;
    protected static ?string $slug = 'post-collaborators';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Collaboration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Post Collaborator Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('role')
                        ->options([
                            'owner' => 'Owner',
                            'editor' => 'Editor',
                            'reviewer' => 'Reviewer',
                            'viewer' => 'Viewer',
                        ])
                        ->required(),
                    Forms\Components\TagsInput::make('permissions')
                        ->separator(','),
                    Forms\Components\DateTimePicker::make('joined_at'),
                    Forms\Components\DateTimePicker::make('left_at'),
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
                    ->label('Collaborator')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'owner' => 'danger',
                        'editor' => 'warning',
                        'reviewer' => 'info',
                        'viewer' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('joined_at')
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
                Tables\Filters\SelectFilter::make('role'),
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->whereNull('left_at')),
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
            'index' => Pages\ListPostCollaborators::route('/'),
            'create' => Pages\CreatePostCollaborator::route('/create'),
            'edit' => Pages\EditPostCollaborator::route('/{record}/edit'),
        ];
    }
}
