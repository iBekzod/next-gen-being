<?php

namespace App\Filament\Resources\LandingLeadResource\Pages;

use App\Filament\Resources\LandingLeadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLandingLead extends EditRecord
{
    protected static string $resource = LandingLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
