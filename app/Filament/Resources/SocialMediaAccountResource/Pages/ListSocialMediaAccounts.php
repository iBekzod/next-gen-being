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
            Actions\Action::make('connect_account')
                ->label('Connect Platform')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->dropdown()
                ->dropdownActions([
                    Actions\Action::make('connect_youtube')
                        ->label('YouTube')
                        ->icon('heroicon-o-play')
                        ->url(fn () => route('social.auth.redirect', 'youtube'))
                        ->color('danger')
                        ->openUrlInNewTab(),

                    Actions\Action::make('connect_instagram')
                        ->label('Instagram')
                        ->icon('heroicon-o-camera')
                        ->url(fn () => route('social.auth.redirect', 'instagram'))
                        ->color('warning')
                        ->openUrlInNewTab(),

                    Actions\Action::make('connect_facebook')
                        ->label('Facebook')
                        ->icon('heroicon-o-users')
                        ->url(fn () => route('social.auth.redirect', 'facebook'))
                        ->color('info')
                        ->openUrlInNewTab(),

                    Actions\Action::make('connect_twitter')
                        ->label('Twitter / X')
                        ->icon('heroicon-o-chat-bubble-left')
                        ->url(fn () => route('social.auth.redirect', 'twitter'))
                        ->color('primary')
                        ->openUrlInNewTab(),

                    Actions\Action::make('connect_linkedin')
                        ->label('LinkedIn')
                        ->icon('heroicon-o-briefcase')
                        ->url(fn () => route('social.auth.redirect', 'linkedin'))
                        ->color('success')
                        ->openUrlInNewTab(),
                ]),
        ];
    }
}
