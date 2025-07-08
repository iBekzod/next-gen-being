<?php

namespace App\Filament\Resources\AiContentSuggestionResource\Pages;

use App\Filament\Resources\AiContentSuggestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAiContentSuggestion extends EditRecord
{
    protected static string $resource = AiContentSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
