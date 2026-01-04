<?php

namespace App\Filament\Resources\PostCurationResource\Pages;

use App\Filament\Resources\PostCurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostCuration extends EditRecord
{
    protected static string $resource = PostCurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_in_admin')
                ->label('View in Full Admin')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn ($record) => \App\Filament\Resources\PostResource::getUrl('edit', ['record' => $record]))
                ->openUrlInNewTab(),
        ];
    }
}
