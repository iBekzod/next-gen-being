<?php

namespace App\Filament\Blogger\Resources\MyPostResource\RelationManagers;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    protected static ?string $recordTitleAttribute = 'content';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('user.name')
                    ->label('Author')
                    ->content(fn ($record) => $record?->user?->name ?? 'Unknown'),

                Forms\Components\Textarea::make('content')
                    ->label('Comment')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'spam' => 'Spam',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Comment')
                    ->limit(60)
                    ->wrap()
                    ->searchable()
                    ->tooltip(fn (Comment $record): string => $record->content),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'spam' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-heart')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Posted')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'spam' => 'Spam',
                    ]),

                Tables\Filters\Filter::make('replies')
                    ->label('Replies only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('parent_id')),

                Tables\Filters\Filter::make('top_level')
                    ->label('Top level comments')
                    ->query(fn (Builder $query): Builder => $query->whereNull('parent_id')),
            ])
            ->headerActions([
                Tables\Actions\Action::make('approve_pending')
                    ->label('Approve All Pending')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn () => $this->getOwnerRecord()->comments()->where('status', 'pending')->exists())
                    ->action(function () {
                        $this->getOwnerRecord()
                            ->comments()
                            ->where('status', 'pending')
                            ->update(['status' => 'approved']);

                        \Filament\Notifications\Notification::make()
                            ->title('Comments Approved')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record): bool => $record->status !== 'approved')
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'approved']);

                        \Filament\Notifications\Notification::make()
                            ->title('Comment Approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Comment $record): bool => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'rejected']);

                        \Filament\Notifications\Notification::make()
                            ->title('Comment Rejected')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
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
                                $comment->update(['status' => 'approved']);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Comments Approved')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('mark_spam')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                $comment->update(['status' => 'spam']);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Comments Marked as Spam')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Comments from readers will appear here')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
