<?php

namespace App\Filament\Resources\LearningPathResource\Pages;

use App\Filament\Resources\LearningPathResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningPath extends EditRecord
{
    protected static string $resource = LearningPathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
