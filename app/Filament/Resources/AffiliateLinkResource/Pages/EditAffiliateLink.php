<?php

namespace App\Filament\Resources\AffiliateLinkResource\Pages;

use App\Filament\Resources\AffiliateLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateLink extends EditRecord
{
    protected static string $resource = AffiliateLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
