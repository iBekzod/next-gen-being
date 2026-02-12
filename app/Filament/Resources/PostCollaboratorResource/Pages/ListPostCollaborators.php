<?php

namespace App\Filament\Resources\PostCollaboratorResource\Pages;

use App\Filament\Resources\PostCollaboratorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostCollaborators extends ListRecords
{
    protected static string $resource = PostCollaboratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
