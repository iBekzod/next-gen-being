<?php

namespace App\Filament\Resources\DigitalProductResource\Pages;

use App\Filament\Resources\DigitalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDigitalProducts extends ListRecords
{
    protected static string $resource = DigitalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
