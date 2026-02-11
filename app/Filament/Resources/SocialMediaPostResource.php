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
                            ->relationship('socialMediaAccount', 'platform_username')
                            ->searchable()
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('post_id')
                            ->label('Original Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->disabled(),

                        Forms\Components\TextInput::make('platform')
                            ->label('Platform')
                            ->disabled(),

                        Forms\Components\TextInput::make('platform_post_id')
                            ->label('Platform Post ID')
                            ->disabled(),

                        Forms\Components\TextInput::make('content_type')
                            ->label('Content Type')
                            ->disabled(),

                        Forms\Components\Textarea::make('content_text')
                            ->label('Content')
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('caption')
                            ->label('Caption')
                            ->rows(2)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('content_media_url')
                            ->label('Media URL')
                            ->url()
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('platform_post_url')
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

                Forms\Components\Section::make('Engagement')
                    ->schema([
                        Forms\Components\TextInput::make('likes_count')
                            ->label('Likes')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('comments_count')
                            ->label('Comments')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('shares_count')
                            ->label('Shares')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('views_count')
                            ->label('Views')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('socialMediaAccount.platform_username')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
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

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->sortable()
                    ->alignRight(),

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

                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'twitter' => 'Twitter',
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['socialMediaAccount', 'post']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialMediaPosts::route('/'),
            'view' => Pages\ViewSocialMediaPost::route('/{record}'),
        ];
    }
}
