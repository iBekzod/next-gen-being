<?php

namespace App\Filament\Resources\ChallengeParticipantResource\Pages;

use App\Filament\Resources\ChallengeParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChallengeParticipant extends EditRecord
{
    protected static string $resource = ChallengeParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
