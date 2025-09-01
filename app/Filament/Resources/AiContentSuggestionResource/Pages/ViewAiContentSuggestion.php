<?php

namespace App\Filament\Resources\AiContentSuggestionResource\Pages;

use App\Filament\Resources\AiContentSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAiContentSuggestion extends ViewRecord
{
    protected static string $resource = AiContentSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
