<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $context, $state, callable $set) =>
                        $context === 'create' ? $set('slug', \Str::slug($state)) : null),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Tag::class, 'slug', ignoreRecord: true),

                Forms\Components\Textarea::make('description')
                    ->maxLength(500)
                    ->rows(3),

                Forms\Components\ColorPicker::make('color')
                    ->hex(),

                Forms\Components\Section::make('SEO Settings')
                    ->description('Optional - Leave blank to auto-generate from tag data')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title (Optional)')
                            ->helperText('Auto-generated: "#{Tag Name} Articles - {Site Name}" | Max: 60 characters')
                            ->maxLength(60),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description (Optional)')
                            ->helperText('Auto-generated: "Discover X articles tagged with #{Tag Name}" | Max: 160 characters')
                            ->maxLength(160)
                            ->rows(3),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Keywords (Optional)')
                            ->helperText('Auto-generated from: tag name + related tags + generic keywords | Examples: "articles, blog, tech"')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->color ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\ColorColumn::make('color'),

                Tables\Columns\TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
