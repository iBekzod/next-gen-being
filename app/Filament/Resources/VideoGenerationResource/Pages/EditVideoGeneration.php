<?php

namespace App\Filament\Resources\VideoGenerationResource\Pages;

use App\Filament\Resources\VideoGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoGeneration extends EditRecord
{
    protected static string $resource = VideoGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole('admin')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Video generation updated successfully';
    }
}