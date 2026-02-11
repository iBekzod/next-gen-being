<?php

namespace App\Filament\Resources\WebhookLogResource\Pages;

use App\Filament\Resources\WebhookLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWebhookLog extends ViewRecord
{
    protected static string $resource = WebhookLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
