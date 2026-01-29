<?php

namespace App\Filament\Resources;

use App\Models\ContentIdea;
use App\Filament\Resources\ContentIdeaResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ContentIdeaResource extends Resource
{
    protected static ?string $model = ContentIdea::class;
    protected static ?string $slug = 'content-ideas';
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Content Idea')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description'),
                    Forms\Components\Select::make('content_type')
                        ->options([
                            'article' => 'Article',
                            'tutorial' => 'Tutorial',
                            'story' => 'Story',
                            'news' => 'News',
                            'opinion' => 'Opinion',
                        ]),
                    Forms\Components\Textarea::make('tags'),
                    Forms\Components\TextInput::make('difficulty_score')
                        ->numeric()
                        ->min(1)
                        ->max(10),
                    Forms\Components\Select::make('status')
                        ->options([
                            'brainstorm' => 'Brainstorm',
                            'draft' => 'Draft',
                            'ready' => 'Ready',
                            'published' => 'Published',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Creator'),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('content_type')->badge(),
                Tables\Columns\TextColumn::make('difficulty_score'),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type'),
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
            'index' => Pages\ListContentIdeas::route('/'),
            'create' => Pages\CreateContentIdea::route('/create'),
            'edit' => Pages\EditContentIdea::route('/{record}/edit'),
        ];
    }
}
