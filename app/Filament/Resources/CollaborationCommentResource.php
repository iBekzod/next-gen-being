<?php

namespace App\Filament\Resources;

use App\Models\CollaborationComment;
use App\Filament\Resources\CollaborationCommentResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CollaborationCommentResource extends Resource
{
    protected static ?string $model = CollaborationComment::class;
    protected static ?string $slug = 'collaboration-comments';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static ?string $navigationGroup = 'Collaboration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collaboration Comment Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Textarea::make('content')
                        ->required()
                        ->maxLength(2000),
                    Forms\Components\TextInput::make('section'),
                    Forms\Components\TextInput::make('line_number')
                        ->numeric(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'open' => 'Open',
                            'resolved' => 'Resolved',
                            'needs_discussion' => 'Needs Discussion',
                        ])
                        ->default('open'),
                    Forms\Components\Select::make('parent_comment_id')
                        ->relationship('parentComment', 'content')
                        ->searchable(),
                    Forms\Components\DateTimePicker::make('resolved_at'),
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
                    ->label('Author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'open' => 'warning',
                        'resolved' => 'success',
                        'needs_discussion' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('section')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollaborationComments::route('/'),
            'create' => Pages\CreateCollaborationComment::route('/create'),
            'edit' => Pages\EditCollaborationComment::route('/{record}/edit'),
        ];
    }
}
