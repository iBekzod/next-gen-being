<?php

namespace App\Filament\Resources\DigitalProductResource\Pages;

use App\Filament\Resources\DigitalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalProduct extends CreateRecord
{
    protected static string $resource = DigitalProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
