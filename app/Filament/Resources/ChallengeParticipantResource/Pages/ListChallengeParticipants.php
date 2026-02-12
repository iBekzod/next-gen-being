<?php

namespace App\Filament\Resources\ChallengeParticipantResource\Pages;

use App\Filament\Resources\ChallengeParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChallengeParticipants extends ListRecords
{
    protected static string $resource = ChallengeParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
