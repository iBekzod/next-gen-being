<?php

namespace App\Filament\Resources\ReaderPreferenceResource\Pages;

use App\Filament\Resources\ReaderPreferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReaderPreference extends EditRecord
{
    protected static string $resource = ReaderPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
