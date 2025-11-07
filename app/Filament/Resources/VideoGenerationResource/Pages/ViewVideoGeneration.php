<?php

namespace App\Filament\Resources\VideoGenerationResource\Pages;

use App\Filament\Resources\VideoGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVideoGeneration extends ViewRecord
{
    protected static string $resource = VideoGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['queued', 'scheduled'])),

            Actions\Action::make('process_now')
                ->label('Process Now')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'scheduled')
                ->action(function () {
                    $this->record->update([
                        'status' => 'queued',
                        'scheduled_at' => null,
                    ]);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('retry')
                ->label('Retry')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->hasFailed() && $this->record->shouldRetry())
                ->action(function () {
                    $this->record->update(['status' => 'queued']);
                    $this->record->incrementRetryCount();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}