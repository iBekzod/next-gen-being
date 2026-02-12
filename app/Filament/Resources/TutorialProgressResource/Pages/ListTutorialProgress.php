<?php

namespace App\Filament\Resources\TutorialProgressResource\Pages;

use App\Filament\Resources\TutorialProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutorialProgress extends ListRecords
{
    protected static string $resource = TutorialProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
