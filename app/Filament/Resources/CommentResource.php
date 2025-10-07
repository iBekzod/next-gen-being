<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Comment Details')
                    ->schema([
                        Forms\Components\Select::make('post_id')
                            ->relationship('post', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'content')
                            ->label('Reply To')
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty if this is a top-level comment'),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Moderation')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'spam' => 'Marked as Spam',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('likes_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        Forms\Components\TextInput::make('replies_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('content')
                    ->limit(100)
                    ->searchable()
                    ->tooltip(function ($record) {
                        return $record->content;
                    }),

                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->limit(50)
                    ->searchable()
                    ->url(fn ($record) => route('posts.show', $record->post->slug), true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'spam',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-check-circle' => 'approved',
                        'heroicon-m-x-circle' => 'rejected',
                        'heroicon-m-shield-exclamation' => 'spam',
                    ]),

                Tables\Columns\IconColumn::make('parent_id')
                    ->label('Reply')
                    ->boolean()
                    ->trueIcon('heroicon-m-arrow-turn-down-right')
                    ->falseIcon('')
                    ->tooltip('This is a reply to another comment'),

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-heart'),

                Tables\Columns\TextColumn::make('replies_count')
                    ->label('Replies')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-arrow-turn-down-right'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Posted'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'spam' => 'Spam',
                    ]),

                Tables\Filters\SelectFilter::make('post')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('replies_only')
                    ->label('Replies Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),

                Tables\Filters\Filter::make('top_level')
                    ->label('Top Level Comments')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('popular')
                    ->label('Popular (5+ likes)')
                    ->query(fn (Builder $query): Builder => $query->where('likes_count', '>=', 5)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($record) => $record->update(['status' => 'approved']))
                        ->visible(fn ($record) => $record->status !== 'approved'),

                    Tables\Actions\Action::make('reject')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($record) => $record->update(['status' => 'rejected']))
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status !== 'rejected'),

                    Tables\Actions\Action::make('mark_spam')
                        ->icon('heroicon-m-shield-exclamation')
                        ->color('gray')
                        ->action(fn ($record) => $record->update(['status' => 'spam']))
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status !== 'spam'),

                    Tables\Actions\Action::make('view_post')
                        ->icon('heroicon-m-eye')
                        ->url(fn ($record) => route('posts.show', $record->post->slug))
                        ->openUrlInNewTab(),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'approved']))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-m-shield-exclamation')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->update(['status' => 'spam']))
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'view' => Pages\ViewComment::route('/{record}'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
