<?php

namespace App\Filament\Resources\PostCollaboratorResource\Pages;

use App\Filament\Resources\PostCollaboratorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostCollaborator extends EditRecord
{
    protected static string $resource = PostCollaboratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
