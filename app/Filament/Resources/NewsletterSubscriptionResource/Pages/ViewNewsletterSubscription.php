<?php

namespace App\Filament\Resources\NewsletterSubscriptionResource\Pages;

use App\Filament\Resources\NewsletterSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsletterSubscription extends ViewRecord
{
    protected static string $resource = NewsletterSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
