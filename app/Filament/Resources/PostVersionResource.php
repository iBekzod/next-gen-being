<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostVersionResource\Pages;
use App\Models\PostVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostVersionResource extends Resource
{
    protected static ?string $model = PostVersion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Post Versions';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Version Details')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->label('Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('edited_by')
                            ->label('Edited By')
                            ->relationship('editor', 'name')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content')
                            ->label('Content')
                            ->rows(6)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('change_type')
                            ->label('Change Type')
                            ->options([
                                'auto_save' => 'Auto Save',
                                'manual_save' => 'Manual Save',
                                'published' => 'Published',
                                'scheduled' => 'Scheduled',
                            ])
                            ->disabled(),

                        Forms\Components\Textarea::make('change_summary')
                            ->label('Change Summary')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('changes_metadata')
                            ->label('Changes Metadata')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('editor.name')
                    ->label('Edited By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_type')
                    ->label('Change Type')
                    ->badge()
                    ->colors([
                        'gray' => 'auto_save',
                        'info' => 'manual_save',
                        'success' => 'published',
                        'warning' => 'scheduled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_summary')
                    ->label('Summary')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('change_type')
                    ->options([
                        'auto_save' => 'Auto Save',
                        'manual_save' => 'Manual Save',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
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
            'index' => Pages\ListPostVersions::route('/'),
            'view' => Pages\ViewPostVersion::route('/{record}'),
        ];
    }
}
