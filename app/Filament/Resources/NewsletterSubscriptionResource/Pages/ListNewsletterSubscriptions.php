<?php

namespace App\Filament\Resources\NewsletterSubscriptionResource\Pages;

use App\Filament\Resources\NewsletterSubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscriptions extends ListRecords
{
    protected static string $resource = NewsletterSubscriptionResource::class;
}
