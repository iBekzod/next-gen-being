<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialMediaPostResource\Pages;
use App\Models\SocialMediaPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialMediaPostResource extends Resource
{
    protected static ?string $model = SocialMediaPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Social Posts';

    protected static ?string $navigationGroup = 'Distribution';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\Select::make('social_media_account_id')
                            ->label('Account')
                            ->relationship('account', 'platform_username')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('post_id')
                            ->label('Original Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('platform_post_id')
                            ->label('Platform Post ID')
                            ->disabled(),

                        Forms\Components\Textarea::make('content')
                            ->label('Content')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('platform_url')
                            ->label('Platform URL')
                            ->url()
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'published' => 'Published',
                                'failed' => 'Failed',
                            ])
                            ->disabled(),

                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.platform_username')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'scheduled',
                        'success' => 'published',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'failed' => 'Failed',
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
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['account', 'post']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialMediaPosts::route('/'),
            'view' => Pages\ViewSocialMediaPost::route('/{record}'),
        ];
    }
}
