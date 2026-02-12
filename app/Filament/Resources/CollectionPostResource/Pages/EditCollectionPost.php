<?php

namespace App\Filament\Resources\CollectionPostResource\Pages;

use App\Filament\Resources\CollectionPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionPost extends EditRecord
{
    protected static string $resource = CollectionPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
