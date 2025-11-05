<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('excerpt')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('content_json')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('featured_image')
                    ->image(),
                Forms\Components\TextInput::make('gallery'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('draft'),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\DateTimePicker::make('scheduled_at'),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\Toggle::make('allow_comments')
                    ->required(),
                Forms\Components\Toggle::make('is_premium')
                    ->required(),
                Forms\Components\TextInput::make('read_time')
                    ->numeric(),
                Forms\Components\TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('likes_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('comments_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bookmarks_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('seo_meta'),
                Forms\Components\TextInput::make('author_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                Forms\Components\FileUpload::make('image_attribution')
                    ->image(),
                Forms\Components\TextInput::make('total_shares')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('twitter_shares')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('linkedin_shares')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('facebook_shares')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('whatsapp_shares')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('telegram_shares')
                    ->tel()
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('premium_tier')
                    ->maxLength(20),
                Forms\Components\TextInput::make('preview_percentage')
                    ->required()
                    ->numeric()
                    ->default(30),
                Forms\Components\Textarea::make('paywall_message')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('series_title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('series_slug')
                    ->maxLength(255),
                Forms\Components\TextInput::make('series_part')
                    ->numeric(),
                Forms\Components\TextInput::make('series_total_parts')
                    ->numeric(),
                Forms\Components\Textarea::make('series_description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('moderation_status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\TextInput::make('moderated_by')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('moderated_at'),
                Forms\Components\Textarea::make('moderation_notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ai_moderation_check'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('featured_image'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('allow_comments')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean(),
                Tables\Columns\TextColumn::make('read_time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comments_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bookmarks_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('twitter_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('linkedin_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('facebook_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('whatsapp_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telegram_shares')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('premium_tier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('preview_percentage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('series_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('series_slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('series_part')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('series_total_parts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('moderation_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('moderated_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('moderated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
