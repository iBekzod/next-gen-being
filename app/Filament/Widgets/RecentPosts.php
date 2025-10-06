<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class RecentPosts extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = ['md' => 2];

    protected function getTableQuery(): Builder|Relation
    {
        return Post::query()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('Post')
                ->limit(40)
                ->searchable(),
            Tables\Columns\TextColumn::make('category.name')
                ->label('Category')
                ->badge(),
            Tables\Columns\TextColumn::make('author.name')
                ->label('Author')
                ->badge()
                ->toggleable(),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'published',
                    'warning' => 'scheduled',
                    'secondary' => 'draft',
                    'danger' => 'archived',
                ]),
            Tables\Columns\TextColumn::make('published_at')
                ->label('Published')
                ->since()
                ->placeholder('�'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('open')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->tooltip('Open public view')
                ->url(fn (Post $record) => route('posts.show', $record->slug))
                ->openUrlInNewTab(),
            Tables\Actions\EditAction::make()
                ->url(fn (Post $record) => route('filament.admin.resources.posts.edit', $record)),
        ];
    }
}

