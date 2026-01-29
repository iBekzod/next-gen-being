<?php

namespace App\Filament\Resources\CreatorAnalyticResource\Pages;

use App\Filament\Resources\CreatorAnalyticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreatorAnalytics extends ListRecords
{
    protected static string $resource = CreatorAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
