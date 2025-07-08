<?php

namespace App\Filament\Resources\HelpReportResource\Pages;

use App\Filament\Resources\HelpReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpReport extends EditRecord
{
    protected static string $resource = HelpReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
