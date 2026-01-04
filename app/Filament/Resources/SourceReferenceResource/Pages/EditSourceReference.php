<?php

namespace App\Filament\Resources\SourceReferenceResource\Pages;

use App\Filament\Resources\SourceReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSourceReference extends EditRecord
{
    protected static string $resource = SourceReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
