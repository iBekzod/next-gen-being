<?php

namespace App\Filament\Resources\WebhookResource\Pages;

use App\Filament\Resources\WebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWebhook extends CreateRecord
{
    protected static string $resource = WebhookResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
