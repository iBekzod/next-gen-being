<?php

namespace App\Filament\Resources\SocialAccountResource\Pages;

use App\Filament\Resources\SocialAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialAccounts extends ListRecords
{
    protected static string $resource = SocialAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
