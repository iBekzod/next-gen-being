<?php

namespace App\Filament\Resources\ContentSourceResource\Pages;

use App\Filament\Resources\ContentSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentSource extends EditRecord
{
    protected static string $resource = ContentSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
