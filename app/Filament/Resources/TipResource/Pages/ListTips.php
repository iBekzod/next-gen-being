<?php

namespace App\Filament\Resources\TipResource\Pages;

use App\Filament\Resources\TipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTips extends ListRecords
{
    protected static string $resource = TipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
