<?php

namespace App\Filament\Resources\TutorialCollectionResource\Pages;

use App\Filament\Resources\TutorialCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTutorialCollection extends EditRecord
{
    protected static string $resource = TutorialCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
