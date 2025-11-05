<?php

namespace App\Filament\Resources\SocialMediaAccountResource\Pages;

use App\Filament\Resources\SocialMediaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialMediaAccount extends EditRecord
{
    protected static string $resource = SocialMediaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reconnect')
                ->label('Reconnect')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->url(fn ($record) => route('social.auth.redirect', $record->platform))
                ->visible(fn ($record) => $record->isTokenExpired()),

            Actions\DeleteAction::make()
                ->label('Disconnect'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
