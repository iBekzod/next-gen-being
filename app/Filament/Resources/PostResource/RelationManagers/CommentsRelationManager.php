<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Author')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('parent_id')
                            ->relationship(
                                'parent',
                                'content',
                                fn (Builder $query) => $query
                                    ->where('post_id', $this->getOwnerRecord()->id)
                                    ->whereNull('parent_id')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => \Str::limit($record->content, 50))
                            ->label('Reply to')
                            ->searchable()
                            ->preload()
                            ->placeholder('—'),
                    ]),
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
                    ->required()
                    ->native(false),
            ]);
    }

    // CHANGED: Removed 'static' keyword
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user'))
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
                    ->limit(70)
                    ->wrap()
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
                    ->label('Status')
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
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('approve_pending')
                    ->label('Approve pending')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($livewire) => $livewire->getOwnerRecord()->comments()->where('status', 'pending')->exists())
                    ->action(function () {
                        $this->getOwnerRecord()
                            ->comments()
                            ->where('status', 'pending')
                            ->get()
                            ->each(function (Comment $comment) {
                                if ($comment->status !== 'approved') {
                                    $comment->update(['status' => 'approved']);
                                }
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record): bool => $record->status !== 'approved')
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'approved']);
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Comment $record): bool => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'rejected']);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                if ($comment->status !== 'approved') {
                                    $comment->update(['status' => 'approved']);
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_spam')
                        ->label('Mark as spam')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (Comment $comment) {
                                $comment->update(['status' => 'spam']);
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
