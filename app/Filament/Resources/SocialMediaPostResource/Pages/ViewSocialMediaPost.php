<?php

namespace App\Filament\Resources\SocialMediaPostResource\Pages;

use App\Filament\Resources\SocialMediaPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSocialMediaPost extends ViewRecord
{
    protected static string $resource = SocialMediaPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
