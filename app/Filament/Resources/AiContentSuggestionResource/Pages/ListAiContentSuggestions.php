<?php

namespace App\Filament\Resources\AiContentSuggestionResource\Pages;

use App\Filament\Resources\AiContentSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAiContentSuggestions extends ListRecords
{
    protected static string $resource = AiContentSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
