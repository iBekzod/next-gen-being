<?php

namespace App\Filament\Blogger\Resources;

use App\Filament\Blogger\Resources\MyPostResource\Pages;
use App\Filament\Blogger\Resources\MyPostResource\RelationManagers;
use App\Models\Post;
use App\Models\Category;
use App\Services\EnhancedAIGenerationService;
use App\Services\AI\AdvancedAIContentService;
use App\Services\Content\ContentQualityChecker;
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

                Forms\Components\Section::make('Content Guidance & SEO')
                    ->description('AI-powered suggestions to improve your content quality and SEO')
                    ->schema([
                        Forms\Components\Placeholder::make('guidance_intro')
                            ->label('')
                            ->content('Get AI suggestions for better titles, content structure, and SEO optimization. Use the buttons below to generate ideas.'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('guidance_topic')
                                    ->label('Topic for Suggestions')
                                    ->placeholder('e.g., Laravel 11, Web Development')
                                    ->helperText('What is your content about?')
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generateTitleSuggestions')
                                ->label('Get Title Suggestions')
                                ->icon('heroicon-o-sparkles')
                                ->color('info')
                                ->modalHeading('Title Suggestions')
                                ->modalDescription('Select a title suggestion that best fits your content')
                                ->modalWidth('lg')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    // Modal just displays suggestions, user can manually copy
                                    Notification::make()
                                        ->title('Title Suggestions Generated')
                                        ->body('Select a title from the list and click the title field to use it.')
                                        ->info()
                                        ->send();
                                })
                                ->form([
                                    Forms\Components\Placeholder::make('title_list')
                                        ->label('Suggested Titles')
                                        ->content(function (Forms\Get $get) {
                                            $topic = $get('guidance_topic');
                                            if (!$topic) {
                                                return 'Please enter a topic above to generate title suggestions.';
                                            }

                                            try {
                                                $service = app(AdvancedAIContentService::class);
                                                $result = $service->generateTitles($topic, 5);

                                                if ($result['success']) {
                                                    $output = "<div class='space-y-3'>";
                                                    foreach ($result['titles'] as $index => $title) {
                                                        $output .= "<div class='p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500'>";
                                                        $output .= "<p class='font-semibold text-gray-800'>{$title}</p>";
                                                        $output .= "</div>";
                                                    }
                                                    $output .= "</div>";
                                                    $output .= "<div class='mt-4 p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500'>";
                                                    $output .= "<p class='font-semibold text-gray-800 mb-2'>💡 Tips for Better Titles:</p>";
                                                    $output .= "<ul class='list-disc list-inside text-sm text-gray-700'>";
                                                    foreach ($result['tips'] as $tip) {
                                                        $output .= "<li>{$tip}</li>";
                                                    }
                                                    $output .= "</ul>";
                                                    $output .= "</div>";
                                                    return $output;
                                                }
                                                return 'Failed to generate suggestions. Please try again.';
                                            } catch (\Exception $e) {
                                                return 'Error generating suggestions: ' . $e->getMessage();
                                            }
                                        }),
                                ]),

                            Forms\Components\Actions\Action::make('generateOutline')
                                ->label('Generate Content Outline')
                                ->icon('heroicon-o-document-text')
                                ->color('success')
                                ->modalHeading('Content Outline')
                                ->modalDescription('A suggested outline structure for your content')
                                ->modalWidth('lg')
                                ->action(function (Forms\Get $get, array $data) {
                                    $topic = $get('guidance_topic');
                                    $outlineType = $data['outline_type'] ?? 'comprehensive';

                                    if (!$topic) {
                                        Notification::make()
                                            ->title('Topic Required')
                                            ->body('Please enter a topic above to generate an outline.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $service = app(AdvancedAIContentService::class);
                                        $result = $service->generateOutline($topic, $outlineType);

                                        if ($result['success']) {
                                            Notification::make()
                                                ->title('Outline Generated!')
                                                ->body('Check the modal dialog for your content outline.')
                                                ->success()
                                                ->send();
                                        }
                                    } catch (\Exception $e) {
                                        Notification::make()
                                            ->title('Generation Failed')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                                ->form([
                                    Forms\Components\Select::make('outline_type')
                                        ->label('Outline Type')
                                        ->options([
                                            'comprehensive' => 'Comprehensive Guide',
                                            'how-to' => 'How-To Article',
                                            'analysis' => 'Analysis/Review',
                                            'listicle' => 'List Article (Top 10, etc)',
                                        ])
                                        ->required(),
                                ]),

                            Forms\Components\Actions\Action::make('checkQuality')
                                ->label('Check Content Quality & SEO')
                                ->icon('heroicon-o-check-circle')
                                ->color('warning')
                                ->disabled(fn (Forms\Get $get) => !$get('title') || !$get('content'))
                                ->modalHeading('Content Quality Analysis')
                                ->modalDescription('Get detailed feedback on your content quality and SEO')
                                ->modalWidth('2xl')
                                ->action(function () {
                                    Notification::make()
                                        ->title('Analysis Complete')
                                        ->body('Review the detailed feedback above to improve your content.')
                                        ->success()
                                        ->send();
                                })
                                ->form([
                                    Forms\Components\Placeholder::make('quality_results')
                                        ->label('')
                                        ->content(function (Forms\Get $get) {
                                            $title = $get('title');
                                            $excerpt = $get('excerpt');
                                            $content = $get('content');

                                            if (!$title || !$content) {
                                                return 'Please fill in the title and content fields first.';
                                            }

                                            try {
                                                $checker = app(ContentQualityChecker::class);
                                                $analysis = $checker->analyzePost($title, $excerpt, $content);

                                                $output = "<div class='space-y-4'>";

                                                // Overall Score
                                                $score = $analysis['overall_score'];
                                                $scoreColor = match($score['grade']) {
                                                    'A' => 'green',
                                                    'B' => 'blue',
                                                    'C' => 'yellow',
                                                    'D' => 'orange',
                                                    default => 'red'
                                                };
                                                $output .= "<div class='p-4 bg-{$scoreColor}-50 rounded-lg border-l-4 border-{$scoreColor}-500'>";
                                                $output .= "<div class='flex justify-between items-center'>";
                                                $output .= "<div>";
                                                $output .= "<p class='text-sm font-semibold text-gray-700'>Overall Quality Score</p>";
                                                $output .= "<p class='text-3xl font-bold text-{$scoreColor}-600'>{$score['score']}/100</p>";
                                                $output .= "</div>";
                                                $output .= "<div class='text-4xl font-bold text-{$scoreColor}-600'>{$score['grade']}</div>";
                                                $output .= "</div>";
                                                $output .= "<p class='text-xs text-gray-600 mt-2'>{$score['feedback']}</p>";
                                                $output .= "</div>";

                                                // SEO Analysis
                                                $seo = $analysis['seo_analysis'];
                                                $output .= "<div class='p-4 bg-indigo-50 rounded-lg'>";
                                                $output .= "<p class='font-semibold text-gray-800 mb-3'>📊 SEO Analysis</p>";
                                                $output .= "<div class='space-y-2'>";
                                                foreach ($seo['checks'] as $check) {
                                                    $icon = $check['passed'] ? '✅' : '⚠️';
                                                    $output .= "<div class='flex items-start gap-2'>";
                                                    $output .= "<span class='text-lg'>{$icon}</span>";
                                                    $output .= "<div class='flex-1'>";
                                                    $output .= "<p class='font-medium text-gray-800'>{$check['name']}</p>";
                                                    $output .= "<p class='text-xs text-gray-600'>{$check['feedback']}</p>";
                                                    $output .= "</div>";
                                                    $output .= "</div>";
                                                }
                                                $output .= "</div>";
                                                $output .= "</div>";

                                                // Readability
                                                $readability = $analysis['readability_analysis'];
                                                $output .= "<div class='p-4 bg-green-50 rounded-lg'>";
                                                $output .= "<p class='font-semibold text-gray-800 mb-2'>📖 Readability</p>";
                                                $output .= "<ul class='text-sm text-gray-700 space-y-1'>";
                                                $output .= "<li>🕐 Reading Time: {$readability['reading_time']} min</li>";
                                                $output .= "<li>📊 Avg Sentence Length: {$readability['avg_sentence_length']} words</li>";
                                                $output .= "<li>📈 Flesch Reading Ease: {$readability['flesch_reading_ease']}/100</li>";
                                                $output .= "</ul>";
                                                $output .= "</div>";

                                                // Recommendations
                                                $recommendations = $analysis['recommendations'];
                                                if (!empty($recommendations)) {
                                                    $output .= "<div class='p-4 bg-purple-50 rounded-lg'>";
                                                    $output .= "<p class='font-semibold text-gray-800 mb-3'>💡 Top Recommendations</p>";
                                                    $output .= "<ol class='list-decimal list-inside text-sm text-gray-700 space-y-2'>";
                                                    foreach (array_slice($recommendations, 0, 5) as $rec) {
                                                        $output .= "<li>{$rec}</li>";
                                                    }
                                                    $output .= "</ol>";
                                                    $output .= "</div>";
                                                }

                                                $output .= "</div>";
                                                return $output;
                                            } catch (\Exception $e) {
                                                return 'Error analyzing content: ' . $e->getMessage();
                                            }
                                        }),
                                ]),
                        ])->fullWidth(),
                    ])
                    ->collapsible()
                    ->collapsed(true),

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

                Tables\Columns\TextColumn::make('video_status')
                    ->label('Video')
                    ->badge()
                    ->getStateUsing(function (Post $record) {
                        $video = \App\Models\VideoGeneration::where('post_id', $record->id)
                            ->latest()
                            ->first();

                        if (!$video) return 'No video';
                        return ucfirst($video->status);
                    })
                    ->colors([
                        'gray' => 'No video',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ])
                    ->icon(fn (string $state): string => match($state) {
                        'No video' => 'heroicon-o-x-mark',
                        'processing' => 'heroicon-o-arrow-path',
                        'completed' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-exclamation-circle',
                        default => 'heroicon-o-video-camera',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('social_status')
                    ->label('Social Media')
                    ->badge()
                    ->getStateUsing(function (Post $record) {
                        $publishedCount = \App\Models\SocialMediaPost::where('post_id', $record->id)
                            ->where('status', 'published')
                            ->count();

                        if ($publishedCount === 0) return 'Not published';
                        return "{$publishedCount} platforms";
                    })
                    ->colors([
                        'gray' => 'Not published',
                        'success' => fn ($state) => $state !== 'Not published',
                    ])
                    ->icon(fn (string $state): string => $state === 'Not published' ? 'heroicon-o-x-mark' : 'heroicon-o-share')
                    ->sortable(),

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

                Tables\Actions\Action::make('generate_video')
                    ->label('Generate Video')
                    ->icon('heroicon-o-video-camera')
                    ->color('success')
                    ->visible(fn (Post $record) => $record->status === 'published')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Video from Post')
                    ->modalDescription(fn (Post $record) => "Generate a video blog from: {$record->title}")
                    ->form([
                        Forms\Components\Select::make('video_type')
                            ->label('Video Type')
                            ->options([
                                'short' => 'Short (60 seconds) - Quick overview',
                                'medium' => 'Medium (3-5 minutes) - Standard format',
                                'long' => 'Long (10+ minutes) - In-depth coverage',
                            ])
                            ->default('medium')
                            ->required()
                            ->helperText('Choose the length and depth of your video'),
                    ])
                    ->action(function (Post $record, array $data) {
                        \App\Jobs\GenerateVideoJob::dispatch(
                            $record,
                            $data['video_type'],
                            Auth::id()
                        );

                        Notification::make()
                            ->title('Video Generation Started!')
                            ->body('Your video is being generated in the background. Check Job Queue for progress.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('publish_social')
                    ->label('Publish to Social Media')
                    ->icon('heroicon-o-share')
                    ->color('info')
                    ->visible(function (Post $record) {
                        // Check if there's at least one completed video
                        $hasVideo = \App\Models\VideoGeneration::where('post_id', $record->id)
                            ->where('status', 'completed')
                            ->exists();

                        // Check if user has connected social media accounts
                        $hasAccounts = \App\Models\SocialMediaAccount::where('user_id', Auth::id())
                            ->exists();

                        return $hasVideo && $hasAccounts;
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Publish to Social Media')
                    ->modalDescription('This will publish your video to all connected social media accounts.')
                    ->action(function (Post $record) {
                        \App\Jobs\PublishToSocialMediaJob::dispatch($record);

                        Notification::make()
                            ->title('Publishing Started!')
                            ->body('Your video is being published to social media. Check Job Queue for progress.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),

                    Tables\Actions\Action::make('view_jobs')
                        ->label('View Jobs')
                        ->icon('heroicon-o-queue-list')
                        ->color('gray')
                        ->url(fn () => route('filament.blogger.resources.job-statuses.index'))
                        ->visible(fn (Post $record) => \App\Models\JobStatus::where('trackable_type', Post::class)
                            ->where('trackable_id', $record->id)
                            ->exists()),
                ]),
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
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\VideoGenerationsRelationManager::class,
            RelationManagers\SocialMediaPostsRelationManager::class,
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
