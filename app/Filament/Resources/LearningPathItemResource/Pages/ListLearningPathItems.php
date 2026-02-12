<?php

namespace App\Filament\Resources\LearningPathItemResource\Pages;

use App\Filament\Resources\LearningPathItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningPathItems extends ListRecords
{
    protected static string $resource = LearningPathItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
