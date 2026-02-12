<?php

namespace App\Filament\Resources\CollaborationCommentResource\Pages;

use App\Filament\Resources\CollaborationCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationComment extends EditRecord
{
    protected static string $resource = CollaborationCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
