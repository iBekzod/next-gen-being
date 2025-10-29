<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Str;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\PostResource\Widgets\PostStatsOverview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use FilamentTiptapEditor\TiptapEditor;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                        TiptapEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->profile('default')
                            ->tools([
                                'heading', 'bullet-list', 'ordered-list', 'checked-list',
                                'blockquote', 'hr', 'bold', 'italic', 'strike',
                                'underline', 'superscript', 'subscript', 'align-left',
                                'align-center', 'align-right', 'link', 'media', 'code-block'
                            ]),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->imageEditor()
                            ->directory('posts/featured')
                            ->visibility('public')
                            ->maxSize(2048),
                        Forms\Components\FileUpload::make('gallery')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->directory('posts/gallery')
                            ->visibility('public')
                            ->maxSize(2048),
                    ]),

                Forms\Components\Section::make('Classification')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\Textarea::make('description'),
                                        Forms\Components\ColorPicker::make('color')
                                            ->default('#6366f1'),
                                    ]),
                                Forms\Components\Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->required()
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\ColorPicker::make('color')
                                    ->default('#10b981'),
                            ]),
                    ]),

                Forms\Components\Section::make('Tutorial Series')
                    ->description('Configure if this post is part of a tutorial series')
                    ->schema([
                        Forms\Components\TextInput::make('series_title')
                            ->label('Series Title')
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('series_slug', Str::slug($state)))
                            ->helperText('Leave empty if this is not part of a series'),
                        Forms\Components\TextInput::make('series_slug')
                            ->label('Series Slug')
                            ->maxLength(255)
                            ->helperText('Auto-generated from series title'),
                        Forms\Components\Textarea::make('series_description')
                            ->label('Series Description')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Brief description of what the series covers'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('series_part')
                                    ->label('Part Number')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('e.g., 1 for Part 1'),
                                Forms\Components\TextInput::make('series_total_parts')
                                    ->label('Total Parts')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Total number of parts in series'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                        'archived' => 'Archived',
                                    ])
                                    ->required()
                                    ->live(),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->visible(fn ($get) => $get('status') === 'published'),
                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->visible(fn ($get) => $get('status') === 'scheduled'),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Post'),
                                Forms\Components\Toggle::make('is_premium')
                                    ->label('Premium Content'),
                                Forms\Components\Toggle::make('allow_comments')
                                    ->label('Allow Comments')
                                    ->default(true),
                            ]),
                    ]),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('moderation_status')
                                    ->label('Moderation Status')
                                    ->options([
                                        'pending' => 'Pending Review',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->disabled(fn ($record) => $record && $record->isApproved())
                                    ->helperText('Status can only be changed via Approve/Reject actions'),
                                Forms\Components\Select::make('moderated_by')
                                    ->relationship('moderator', 'name')
                                    ->disabled()
                                    ->helperText('Auto-set when moderation action is taken'),
                            ]),
                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Moderation Notes')
                            ->rows(2)
                            ->disabled()
                            ->helperText('Feedback from moderator or AI'),
                        Forms\Components\Placeholder::make('ai_moderation_info')
                            ->label('AI Moderation Check')
                            ->content(function ($record) {
                                if (!$record || !$record->ai_moderation_check) {
                                    return 'No AI check performed yet';
                                }
                                $check = $record->ai_moderation_check;
                                $score = $check['score'] ?? 'N/A';
                                $passed = $check['passed'] ?? false;
                                $flags = $check['flags'] ?? [];

                                return "Score: {$score}/100 | " .
                                       ($passed ? '✅ Passed' : '⚠️ Needs Review') .
                                       (!empty($flags) ? ' | Flags: ' . implode(', ', $flags) : '');
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record || $record->moderation_status === 'approved'),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\KeyValue::make('seo_meta')
                            ->keyLabel('Meta Key')
                            ->valueLabel('Meta Value')
                            ->reorderable(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No Author'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Uncategorized'),
                Tables\Columns\TextColumn::make('tags.name')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->placeholder('No Tags'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('moderation_status')
                    ->label('Moderation')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium'),
                Tables\Columns\TextColumn::make('series_title')
                    ->label('Series')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->placeholder('-')
                    ->tooltip(fn ($record) => $record->series_title ? "Part {$record->series_part}/{$record->series_total_parts}" : null),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload(),
                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('moderation_status')
                    ->label('Moderation')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->placeholder('All Moderation States'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_premium'),
                Tables\Filters\Filter::make('is_series')
                    ->label('Tutorial Series')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('series_slug'))
                    ->toggle(),
                Tables\Filters\SelectFilter::make('series_slug')
                    ->label('Specific Series')
                    ->options(fn () => Post::whereNotNull('series_slug')
                        ->pluck('series_title', 'series_slug')
                        ->unique()
                        ->sort()),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from'),
                        Forms\Components\DatePicker::make('published_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isPendingModeration() || $record->isRejected())
                    ->requiresConfirmation()
                    ->modalHeading('Approve Post')
                    ->modalDescription('This will approve and publish the post.')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Approval Notes (Optional)')
                            ->rows(2)
                            ->placeholder('Add any feedback or comments...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve(auth()->user(), $data['notes'] ?? null);
                        if ($record->status === 'draft') {
                            $record->update([
                                'status' => 'published',
                                'published_at' => now(),
                            ]);
                        }
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Post Approved')
                            ->body('The post has been approved and published.')
                    ),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->isPendingModeration())
                    ->requiresConfirmation()
                    ->modalHeading('Reject Post')
                    ->modalDescription('This will reject the post and move it back to draft.')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Explain why this post is being rejected...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Post Rejected')
                            ->body('The post has been rejected and moved to draft.')
                    ),
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('posts.show', $record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record) {
                        $newPost = $record->replicate();
                        $newPost->title = $record->title . ' (Copy)';
                        $newPost->slug = Str::slug($newPost->title);
                        $newPost->status = 'draft';
                        $newPost->published_at = null;
                        $newPost->moderation_status = 'pending';
                        $newPost->save();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Post duplicated')
                            ->body('The post has been duplicated successfully.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkApprove')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve Selected Posts')
                        ->modalDescription('This will approve all selected pending posts.')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isPendingModeration()) {
                                    $record->approve(auth()->user(), 'Bulk approved');
                                    if ($record->status === 'draft') {
                                        $record->update([
                                            'status' => 'published',
                                            'published_at' => now(),
                                        ]);
                                    }
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Posts Approved')
                                ->body('Selected posts have been approved.')
                        ),
                    Tables\Actions\BulkAction::make('bulkReject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Selected Posts')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if ($record->isPendingModeration()) {
                                    $record->reject(auth()->user(), $data['reason']);
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Posts Rejected')
                                ->body('Selected posts have been rejected.')
                        ),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'published',
                            'published_at' => now()
                        ])),
                    Tables\Actions\BulkAction::make('draft')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'draft'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PostStatsOverview::class,
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






