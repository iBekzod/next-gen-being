<?php

namespace App\Filament\Resources\UserInteractionResource\Pages;

use App\Filament\Resources\UserInteractionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserInteraction extends EditRecord
{
    protected static string $resource = UserInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
