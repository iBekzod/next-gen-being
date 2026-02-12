<?php

namespace App\Filament\Resources\CollaborationCommentResource\Pages;

use App\Filament\Resources\CollaborationCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollaborationComments extends ListRecords
{
    protected static string $resource = CollaborationCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
