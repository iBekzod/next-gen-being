<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentComments extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 2];

    protected function getTableQuery(): Builder|Relation
    {
        return Comment::query()
            ->with(['post', 'user'])
            ->latest()
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name')
                ->label('Author')
                ->badge(),
            Tables\Columns\TextColumn::make('post.title')
                ->label('Post')
                ->limit(32)
                ->tooltip(fn (Comment $record) => $record->post?->title),
            Tables\Columns\TextColumn::make('content')
                ->label('Comment')
                ->limit(50)
                ->wrap(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'spam' => 'gray',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->since()
                ->label('Submitted'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Comment $record) => $record->status === 'pending')
                ->action(fn (Comment $record) => $record->approve()),
            Tables\Actions\Action::make('reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn (Comment $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(fn (Comment $record) => $record->reject()),
            Tables\Actions\EditAction::make()
                ->url(fn (Comment $record) => route('filament.admin.resources.comments.edit', $record)),
        ];
    }
}


