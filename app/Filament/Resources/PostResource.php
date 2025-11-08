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
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'All Posts';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->directory('posts/featured')
                            ->maxSize(2048),

                        Forms\Components\Textarea::make('image_attribution')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Post'),

                        Forms\Components\Toggle::make('allow_comments')
                            ->label('Allow Comments')
                            ->default(true),

                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium Content'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('SEO Settings')
                    ->description('Optional - Leave blank to auto-generate from post content')
                    ->schema([
                        Forms\Components\TextInput::make('seo_meta.meta_title')
                            ->label('Meta Title (Optional)')
                            ->helperText('Auto-generated from: post title | Recommended: 50-60 characters')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('seo_meta.description')
                            ->label('Meta Description (Optional)')
                            ->helperText('Auto-generated from: post excerpt | Recommended: 150-160 characters')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('seo_meta.keywords')
                            ->label('Keywords (Optional)')
                            ->helperText('Auto-generated from: post tags | Examples: "tech blog, AI, tutorials"')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('seo_meta.focus_keyword')
                            ->label('Focus Keyword (Optional)')
                            ->helperText('Main keyword you want this post to rank for (e.g., "tech blog", "AI articles"). Helps guide content optimization.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Select::make('moderation_status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Textarea::make('moderation_notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('moderated_info')
                            ->label('Moderation Info')
                            ->content(function ($record) {
                                if (!$record || !$record->moderated_at) {
                                    return 'Not yet moderated';
                                }
                                return "Moderated by {$record->moderator?->name} on {$record->moderated_at->format('M d, Y')}";
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn (Post $record): string => \Str::limit($record->excerpt, 60)),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('moderation_status')
                    ->label('Moderation')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('moderation_status')
                    ->label('Moderation Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name'),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (Post $record): string => route('posts.show', $record->slug))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Post $record) => $record->moderation_status !== 'approved')
                        ->requiresConfirmation()
                        ->action(function (Post $record) {
                            $record->update([
                                'moderation_status' => 'approved',
                                'moderated_by' => Auth::id(),
                                'moderated_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Post Approved')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Post $record) => $record->moderation_status !== 'rejected')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Post')
                        ->modalDescription('Please provide a reason for rejection.')
                        ->form([
                            Forms\Components\Textarea::make('moderation_notes')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Post $record, array $data) {
                            $record->update([
                                'moderation_status' => 'rejected',
                                'moderated_by' => Auth::id(),
                                'moderated_at' => now(),
                                'moderation_notes' => $data['moderation_notes'],
                            ]);

                            Notification::make()
                                ->title('Post Rejected')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make(),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Post $post) {
                                $post->update([
                                    'moderation_status' => 'approved',
                                    'moderated_by' => Auth::id(),
                                    'moderated_at' => now(),
                                ]);
                            });

                            Notification::make()
                                ->title('Posts Approved')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('feature_selected')
                        ->label('Feature Selected')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Post $post) {
                                $post->update(['is_featured' => true]);
                            });

                            Notification::make()
                                ->title('Posts Featured')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('moderation_status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
