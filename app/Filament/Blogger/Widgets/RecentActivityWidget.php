<?php

namespace App\Filament\Blogger\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Jobs')
            ->description('Track your latest video generation and social media publishing jobs')
            ->query(
                \App\Models\JobStatus::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'video-generation' => '🎥 Video',
                        'social-media-publish' => '📱 Social',
                        'publish-platform' => '🚀 Platform',
                        default => ucfirst($state),
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('trackable.title')
                    ->label('Post')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state): string => $state ? "{$state}%" : '0%'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.blogger.resources.job-statuses.view', $record)),
            ])
            ->emptyStateHeading('No recent activity')
            ->emptyStateDescription('Jobs will appear here when you generate videos or publish to social media')
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}
