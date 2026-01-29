<?php

namespace App\Filament\Resources\ScheduledPostResource\Pages;

use App\Filament\Resources\ScheduledPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledPost extends EditRecord
{
    protected static string $resource = ScheduledPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
