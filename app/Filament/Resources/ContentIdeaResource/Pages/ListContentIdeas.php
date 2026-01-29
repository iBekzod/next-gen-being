<?php

namespace App\Filament\Resources\ContentIdeaResource\Pages;

use App\Filament\Resources\ContentIdeaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentIdeas extends ListRecords
{
    protected static string $resource = ContentIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
