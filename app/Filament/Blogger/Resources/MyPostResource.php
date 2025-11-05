<?php

namespace App\Filament\Blogger\Resources;

use App\Filament\Blogger\Resources\MyPostResource\Pages;
use App\Models\Post;
use App\Models\Category;
use App\Services\EnhancedAIGenerationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MyPostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'My Posts';

    protected static ?string $modelLabel = 'Post';

    protected static ?string $pluralModelLabel = 'My Posts';

    protected static ?string $navigationGroup = 'Content';

    // Only show posts from the current blogger
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('author_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $aiService = app(EnhancedAIGenerationService::class);
        $aiStats = $aiService->getUsageStats($user);

        return $form
            ->schema([
                // AI Assistant Section
                Forms\Components\Section::make('AI Assistant')
                    ->description('Use AI to generate content and images for your post')
                    ->schema([
                        Forms\Components\Placeholder::make('ai_quota')
                            ->label('AI Usage')
                            ->content(function () use ($user, $aiStats) {
                                $contentQuota = $aiStats['posts_limit'] === null ? 'unlimited' : "{$aiStats['posts_used']}/{$aiStats['posts_limit']}";
                                $imageQuota = $aiStats['images_limit'] === null ? 'unlimited' : "{$aiStats['images_used']}/{$aiStats['images_limit']}";

                                return "Content: {$contentQuota} | Images: {$imageQuota} | Tier: {$user->getAITierName()}";
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('ai_topic')
                                    ->label('Topic for AI Content')
                                    ->placeholder('e.g., Laravel 11 new features')
                                    ->helperText('What should the AI write about?')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('ai_keywords')
                                    ->label('Keywords (Optional)')
                                    ->placeholder('e.g., Laravel, PHP, framework')
                                    ->helperText('Comma-separated keywords')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generateContent')
                                ->label('Generate Content with AI')
                                ->icon('heroicon-o-sparkles')
                                ->color('primary')
                                ->disabled(fn () => !$user->canGenerateAIContent())
                                ->requiresConfirmation()
                                ->modalHeading('Generate AI Content')
                                ->modalDescription('This will use 1 AI content generation credit.')
                                ->action(function (Forms\Set $set, Forms\Get $get) use ($aiService, $user) {
                                    $topic = $get('ai_topic');
                                    if (!$topic) {
                                        Notification::make()
                                            ->title('Topic Required')
                                            ->body('Please enter a topic for AI content generation.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $keywords = $get('ai_keywords');
                                    $result = $aiService->generateContent($user, $topic, $keywords);

                                    if ($result['success']) {
                                        $set('content', $result['content']);
                                        $set('title', $topic); // Auto-fill title
                                        $set('slug', Str::slug($topic)); // Auto-fill slug

                                        Notification::make()
                                            ->title('Content Generated!')
                                            ->body("Remaining quota: {$result['remaining_quota']}")
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Generation Failed')
                                            ->body($result['error'])
                                            ->danger()
                                            ->send();
                                    }
                                }),

                            Forms\Components\Actions\Action::make('generateImage')
                                ->label('Generate Featured Image with AI')
                                ->icon('heroicon-o-photo')
                                ->color('success')
                                ->disabled(fn () => !$user->canGenerateAIImage())
                                ->requiresConfirmation()
                                ->modalHeading('Generate AI Image')
                                ->modalDescription('This will use 1 AI image generation credit.')
                                ->action(function (Forms\Set $set, Forms\Get $get) use ($aiService, $user) {
                                    $topic = $get('ai_topic') ?? $get('title');
                                    if (!$topic) {
                                        Notification::make()
                                            ->title('Topic or Title Required')
                                            ->body('Please enter a topic or title for AI image generation.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $result = $aiService->generateImage($user, $topic);

                                    if ($result['success']) {
                                        $set('featured_image', $result['image_url']);

                                        // Set attribution based on tier
                                        if (in_array($user->ai_tier, ['premium', 'enterprise'])) {
                                            $set('image_attribution', 'AI-generated image by DALL-E 3');
                                        } else {
                                            $set('image_attribution', 'Photo by Unsplash');
                                        }

                                        Notification::make()
                                            ->title('Image Generated!')
                                            ->body("Remaining quota: {$result['remaining_quota']}")
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Generation Failed')
                                            ->body($result['error'])
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ])->fullWidth(),

                        Forms\Components\Placeholder::make('ai_upgrade_notice')
                            ->label('')
                            ->content(function () use ($user) {
                                if ($user->ai_tier === 'free' && !$user->groq_api_key) {
                                    return '⚠️ Add your API keys in AI Settings or upgrade to Premium for unlimited AI generation.';
                                }
                                if ($user->ai_tier === 'basic') {
                                    return '💡 Upgrade to Premium for unlimited AI posts and images with GPT-4 + DALL-E 3.';
                                }
                                return '';
                            })
                            ->hidden(fn () => in_array($user->ai_tier, ['premium', 'enterprise'])),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Forms\Components\Section::make('Post Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                                'blockquote',
                            ]),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->directory('posts/featured')
                            ->maxSize(2048),

                        Forms\Components\Textarea::make('image_attribution')
                            ->rows(2)
                            ->placeholder('Image credit information (auto-filled if using AI generation)'),
                    ])->columns(1),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium Content')
                            ->helperText('Premium content is only accessible to subscribers'),

                        Forms\Components\Select::make('premium_tier')
                            ->options([
                                'basic' => 'Basic ($4.99/month)',
                                'pro' => 'Pro ($9.99/month)',
                                'team' => 'Team ($29.99/month)',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('is_premium')),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Series (Optional)')
                    ->schema([
                        Forms\Components\TextInput::make('series_id')
                            ->label('Series ID')
                            ->helperText('Use same ID for all posts in a series'),

                        Forms\Components\TextInput::make('series_part')
                            ->numeric()
                            ->label('Part Number'),

                        Forms\Components\TextInput::make('series_total_parts')
                            ->numeric()
                            ->label('Total Parts'),
                    ])->columns(3)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-post.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn (Post $record): string => Str::limit($record->excerpt, 100)),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium')
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('views')
                    ->sortable()
                    ->alignRight()
                    ->icon('heroicon-o-eye'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium Content'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Post $record): string => route('posts.show', $record->slug))
                    ->openUrlInNewTab(),

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyPosts::route('/'),
            'create' => Pages\CreateMyPost::route('/create'),
            'edit' => Pages\EditMyPost::route('/{record}/edit'),
        ];
    }
}
