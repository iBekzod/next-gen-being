<?php

namespace App\Filament\Resources\DigitalProductResource\Pages;

use App\Filament\Resources\DigitalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalProduct extends EditRecord
{
    protected static string $resource = DigitalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
