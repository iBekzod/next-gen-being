<?php

namespace App\Filament\Resources\StreakResource\Pages;

use App\Filament\Resources\StreakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStreaks extends ListRecords
{
    protected static string $resource = StreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
