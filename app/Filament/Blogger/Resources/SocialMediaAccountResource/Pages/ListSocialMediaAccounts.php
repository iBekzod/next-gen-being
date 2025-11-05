<?php

namespace App\Filament\Blogger\Resources\SocialMediaAccountResource\Pages;

use App\Filament\Blogger\Resources\SocialMediaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListSocialMediaAccounts extends ListRecords
{
    protected static string $resource = SocialMediaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('connect_help')
                ->label('How to Connect')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->modalHeading('How to Connect Social Media Accounts')
                ->modalDescription('')
                ->modalContent(view('filament.blogger.modals.social-connect-help'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Actions\ActionGroup::make([
                Actions\Action::make('connect_youtube')
                    ->label('YouTube')
                    ->icon('heroicon-o-play')
                    ->url(fn () => route('social.auth.redirect', 'youtube'))
                    ->color('danger'),

                Actions\Action::make('connect_instagram')
                    ->label('Instagram')
                    ->icon('heroicon-o-camera')
                    ->url(fn () => route('social.auth.redirect', 'instagram'))
                    ->color('warning'),

                Actions\Action::make('connect_twitter')
                    ->label('Twitter / X')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->url(fn () => route('social.auth.redirect', 'twitter'))
                    ->color('primary'),

                Actions\Action::make('connect_facebook')
                    ->label('Facebook')
                    ->icon('heroicon-o-users')
                    ->url(fn () => route('social.auth.redirect', 'facebook'))
                    ->color('info'),

                Actions\Action::make('connect_linkedin')
                    ->label('LinkedIn')
                    ->icon('heroicon-o-briefcase')
                    ->url(fn () => route('social.auth.redirect', 'linkedin'))
                    ->color('success'),
            ])
            ->label('Connect Account')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->button(),
        ];
    }
}
