<?php

namespace App\Filament\Resources\CollaborationInvitationResource\Pages;

use App\Filament\Resources\CollaborationInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollaborationInvitations extends ListRecords
{
    protected static string $resource = CollaborationInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
