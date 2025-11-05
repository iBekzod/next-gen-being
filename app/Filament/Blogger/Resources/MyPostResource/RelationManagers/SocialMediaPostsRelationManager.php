<?php

namespace App\Filament\Blogger\Resources\MyPostResource\RelationManagers;

use App\Models\SocialMediaPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SocialMediaPostsRelationManager extends RelationManager
{
    protected static string $relationship = 'socialMediaPosts';

    protected static ?string $title = 'Social Media Posts';

    protected static ?string $recordTitleAttribute = 'platform_post_id';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('platform_post_id')
            ->columns([
                Tables\Columns\TextColumn::make('socialMediaAccount.platform')
                    ->label('Platform')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'youtube' => '▶️ YouTube',
                        'instagram' => '📷 Instagram',
                        'facebook' => '📘 Facebook',
                        'twitter' => '🐦 Twitter',
                        'linkedin' => '💼 LinkedIn',
                        'telegram' => '✈️ Telegram',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'youtube' => 'danger',
                        'instagram' => 'warning',
                        'facebook' => 'info',
                        'twitter' => 'primary',
                        'linkedin' => 'success',
                        'telegram' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'publishing' => 'warning',
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'pending' => 'heroicon-o-clock',
                        'publishing' => 'heroicon-o-arrow-path',
                        'published' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-exclamation-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('platform_url')
                    ->label('Post Link')
                    ->formatStateUsing(fn ($state) => $state ? 'View Post' : '-')
                    ->url(fn ($record) => $record->platform_url)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->icon('heroicon-o-arrow-top-right-on-square'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-eye')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-heart')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments')
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-chat-bubble-left')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shares_count')
                    ->label('Shares')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-share')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'publishing' => 'Publishing',
                        'published' => 'Published',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('platform')
                    ->label('Platform')
                    ->relationship('socialMediaAccount', 'platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter',
                        'linkedin' => 'LinkedIn',
                        'telegram' => 'Telegram',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('sync_engagement')
                    ->label('Sync Engagement')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn () => $this->getOwnerRecord()->socialMediaPosts()->where('status', 'published')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Sync Engagement Metrics')
                    ->modalDescription('Fetch the latest views, likes, comments, and shares from all platforms.')
                    ->action(function () {
                        $this->getOwnerRecord()->socialMediaPosts()
                            ->where('status', 'published')
                            ->each(function ($socialPost) {
                                \App\Jobs\UpdateEngagementMetricsJob::dispatch($socialPost)
                                    ->onQueue('low-priority');
                            });

                        \Filament\Notifications\Notification::make()
                            ->title('Engagement Sync Started')
                            ->body('Metrics will be updated in the background.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_on_platform')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (SocialMediaPost $record) => $record->platform_url)
                    ->url(fn (SocialMediaPost $record) => $record->platform_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('refresh_metrics')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SocialMediaPost $record) => $record->status === 'published')
                    ->action(function (SocialMediaPost $record) {
                        \App\Jobs\UpdateEngagementMetricsJob::dispatch($record)
                            ->onQueue('low-priority');

                        \Filament\Notifications\Notification::make()
                            ->title('Refreshing Metrics')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SocialMediaPost $record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (SocialMediaPost $record) {
                        \App\Jobs\PublishToPlatformJob::dispatch(
                            $this->getOwnerRecord(),
                            $record->socialMediaAccount
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Retrying Publication')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (SocialMediaPost $record): \Illuminate\Contracts\View\View => view(
                        'filament.blogger.social-media-post-details',
                        ['record' => $record],
                    )),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (SocialMediaPost $record) => $record->status !== 'publishing'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('sync_metrics')
                        ->label('Sync Metrics')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (SocialMediaPost $post) {
                                if ($post->status === 'published') {
                                    \App\Jobs\UpdateEngagementMetricsJob::dispatch($post)
                                        ->onQueue('low-priority');
                                }
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Metrics Sync Started')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc')
            ->emptyStateHeading('Not published to social media yet')
            ->emptyStateDescription('Publish this post to your connected social media accounts')
            ->emptyStateIcon('heroicon-o-share');
    }
}
