<?php

namespace App\Filament\Resources\UserReputationResource\Pages;

use App\Filament\Resources\UserReputationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserReputations extends ListRecords
{
    protected static string $resource = UserReputationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
