<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    protected static ?string $recordTitleAttribute = 'content';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Comment Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Author')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'spam' => 'Spam',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Admin Notes')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Internal notes visible only to administrators'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->description(fn (Comment $record): string => $record->user->email ?? ''),

                Tables\Columns\TextColumn::make('content')
                    ->label('Comment')
                    ->limit(80)
                    ->searchable()
                    ->wrap()
                    ->description(fn (Comment $record): ?string =>
                        $record->parent_id ? "Reply to comment #{$record->parent_id}" : null
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => fn ($state) => in_array($state, ['rejected', 'spam']),
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-exclamation-triangle' => 'spam',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-heart')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('replies_count')
                    ->label('Replies')
                    ->counts('replies')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-chat-bubble-left')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->since()
                    ->sortable()
                    ->description(fn (Comment $record): string =>
                        $record->created_at->format('M d, Y h:i A')
                    ),

                Tables\Columns\TextColumn::make('moderatedBy.name')
                    ->label('Moderated By')
                    ->placeholder('Not moderated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'spam' => 'Spam',
                    ])
                    ->default('pending'),

                Tables\Filters\Filter::make('replies_only')
                    ->label('Replies Only')
                    ->query(fn ($query) => $query->whereNotNull('parent_id')),

                Tables\Filters\Filter::make('top_level')
                    ->label('Top Level Only')
                    ->query(fn ($query) => $query->whereNull('parent_id')),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Author')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('flagged')
                    ->label('Flagged by Users')
                    ->query(fn ($query) => $query->where('is_flagged', true)),
            ])
            ->headerActions([
                Tables\Actions\Action::make('approve_all_pending')
                    ->label('Approve All Pending')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->getOwnerRecord()->comments()->where('status', 'pending')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Approve All Pending Comments')
                    ->modalDescription('This will approve all pending comments on this post. Are you sure?')
                    ->action(function () {
                        $count = $this->getOwnerRecord()->comments()
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'approved',
                                'moderated_by' => Auth::id(),
                                'moderated_at' => now(),
                            ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Comments Approved')
                            ->body("{$count} pending comments have been approved.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Add Comment (Admin)')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['status'] = 'approved';
                        $data['moderated_by'] = Auth::id();
                        $data['moderated_at'] = now();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Comment $record) {
                        $record->update([
                            'status' => 'approved',
                            'moderated_by' => Auth::id(),
                            'moderated_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Comment Approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Comment $record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Comment')
                    ->modalDescription('Provide a reason for rejection (optional)')
                    ->form([
                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->helperText('This note is internal and won\'t be shown to the user'),
                    ])
                    ->action(function (Comment $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'moderated_by' => Auth::id(),
                            'moderated_at' => now(),
                            'moderation_notes' => $data['moderation_notes'] ?? null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Comment Rejected')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_spam')
                    ->label('Spam')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn (Comment $record) => $record->status !== 'spam')
                    ->requiresConfirmation()
                    ->action(function (Comment $record) {
                        $record->update([
                            'status' => 'spam',
                            'moderated_by' => Auth::id(),
                            'moderated_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Marked as Spam')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                $comment->update([
                                    'status' => 'approved',
                                    'moderated_by' => Auth::id(),
                                    'moderated_at' => now(),
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Comments Approved')
                                ->body("{$records->count()} comments have been approved.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                $comment->update([
                                    'status' => 'rejected',
                                    'moderated_by' => Auth::id(),
                                    'moderated_at' => now(),
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Comments Rejected')
                                ->body("{$records->count()} comments have been rejected.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('mark_as_spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                $comment->update([
                                    'status' => 'spam',
                                    'moderated_by' => Auth::id(),
                                    'moderated_at' => now(),
                                ]);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Marked as Spam')
                                ->body("{$records->count()} comments have been marked as spam.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('When users comment on this post, they will appear here.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
