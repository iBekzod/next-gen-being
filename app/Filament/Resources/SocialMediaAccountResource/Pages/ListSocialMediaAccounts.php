<?php

namespace App\Filament\Resources\SocialMediaAccountResource\Pages;

use App\Filament\Resources\SocialMediaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialMediaAccounts extends ListRecords
{
    protected static string $resource = SocialMediaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('connect_youtube')
                ->label('Connect YouTube')
                ->icon('heroicon-o-play')
                ->color('danger')
                ->url(fn () => route('social.auth.redirect', 'youtube'))
                ->openUrlInNewTab(),

            Actions\Action::make('connect_instagram')
                ->label('Connect Instagram')
                ->icon('heroicon-o-camera')
                ->color('warning')
                ->url(fn () => route('social.auth.redirect', 'instagram'))
                ->openUrlInNewTab(),

            Actions\Action::make('connect_facebook')
                ->label('Connect Facebook')
                ->icon('heroicon-o-users')
                ->color('info')
                ->url(fn () => route('social.auth.redirect', 'facebook'))
                ->openUrlInNewTab(),

            Actions\Action::make('connect_twitter')
                ->label('Connect Twitter / X')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('primary')
                ->url(fn () => route('social.auth.redirect', 'twitter'))
                ->openUrlInNewTab(),

            Actions\Action::make('connect_linkedin')
                ->label('Connect LinkedIn')
                ->icon('heroicon-o-briefcase')
                ->color('success')
                ->url(fn () => route('social.auth.redirect', 'linkedin'))
                ->openUrlInNewTab(),
        ];
    }
}
