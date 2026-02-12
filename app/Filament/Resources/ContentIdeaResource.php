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
            Forms\Components\Section::make('Idea Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Creator')
                        ->relationship('creator', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('topic')
                        ->label('Topic')
                        ->maxLength(255),

                    Forms\Components\Select::make('content_type')
                        ->label('Content Type')
                        ->options([
                            'short_post' => 'Short Post',
                            'medium_post' => 'Medium Post',
                            'long_form' => 'Long Form',
                            'tutorial' => 'Tutorial',
                            'case_study' => 'Case Study',
                            'news' => 'News',
                        ]),

                    Forms\Components\TextInput::make('target_audience')
                        ->label('Target Audience')
                        ->maxLength(255),
                ])->columns(2),

            Forms\Components\Section::make('Content Details')
                ->schema([
                    Forms\Components\TagsInput::make('keywords')
                        ->label('Keywords')
                        ->separator(','),

                    Forms\Components\Textarea::make('outline')
                        ->label('Outline')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('source')
                        ->label('Source/Reference'),
                ])->columns(2),

            Forms\Components\Section::make('Scoring & Status')
                ->schema([
                    Forms\Components\TextInput::make('trending_score')
                        ->label('Trending Score (0-100)')
                        ->numeric(),

                    Forms\Components\TextInput::make('difficulty_score')
                        ->label('Difficulty Score (0-100)')
                        ->numeric(),

                    Forms\Components\TextInput::make('estimated_read_time')
                        ->label('Estimated Read Time (mins)')
                        ->numeric(),

                    Forms\Components\Select::make('priority')
                        ->label('Priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ]),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'in_progress' => 'In Progress',
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'archived' => 'Archived',
                        ])
                        ->required(),
                ])->columns(3),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\KeyValue::make('metadata')
                        ->label('Metadata')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('topic')
                    ->label('Topic')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('content_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'gray' => 'short_post',
                        'info' => 'medium_post',
                        'warning' => 'long_form',
                        'success' => 'tutorial',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'in_progress',
                        'success' => 'active',
                        'warning' => 'completed',
                        'secondary' => 'archived',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('trending_score')
                    ->label('Trending')
                    ->numeric()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'short_post' => 'Short Post',
                        'medium_post' => 'Medium Post',
                        'long_form' => 'Long Form',
                        'tutorial' => 'Tutorial',
                        'case_study' => 'Case Study',
                        'news' => 'News',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'in_progress' => 'In Progress',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
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
            'index' => Pages\ListContentIdeas::route('/'),
            'create' => Pages\CreateContentIdea::route('/create'),
            'edit' => Pages\EditContentIdea::route('/{record}/edit'),
        ];
    }
}
