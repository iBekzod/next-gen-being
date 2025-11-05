<?php

namespace App\Filament\Blogger\Resources\SocialMediaAccountResource\Pages;

use App\Filament\Blogger\Resources\SocialMediaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialMediaAccount extends EditRecord
{
    protected static string $resource = SocialMediaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reconnect')
                ->label('Reconnect Account')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn ($record) => $record->isTokenExpired())
                ->url(fn ($record) => route('social.auth.redirect', $record->platform))
                ->tooltip('Your token has expired. Click to reconnect.'),

            Actions\DeleteAction::make()
                ->label('Disconnect Account')
                ->modalHeading('Disconnect Account')
                ->modalDescription('Are you sure you want to disconnect this account?'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
