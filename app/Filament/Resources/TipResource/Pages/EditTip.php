<?php

namespace App\Filament\Resources\TipResource\Pages;

use App\Filament\Resources\TipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTip extends EditRecord
{
    protected static string $resource = TipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
