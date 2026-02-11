<?php

namespace App\Filament\Resources\WebhookLogResource\Pages;

use App\Filament\Resources\WebhookLogResource;
use Filament\Resources\Pages\ListRecords;

class ListWebhookLogs extends ListRecords
{
    protected static string $resource = WebhookLogResource::class;
}
