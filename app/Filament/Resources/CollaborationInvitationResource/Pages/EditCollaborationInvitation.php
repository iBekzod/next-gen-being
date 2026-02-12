<?php

namespace App\Filament\Resources\CollaborationInvitationResource\Pages;

use App\Filament\Resources\CollaborationInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationInvitation extends EditRecord
{
    protected static string $resource = CollaborationInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
