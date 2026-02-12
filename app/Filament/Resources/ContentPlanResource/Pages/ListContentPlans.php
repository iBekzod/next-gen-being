<?php

namespace App\Filament\Resources\ContentPlanResource\Pages;

use App\Filament\Resources\ContentPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentPlans extends ListRecords
{
    protected static string $resource = ContentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
