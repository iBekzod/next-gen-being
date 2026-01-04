<?php

namespace App\Filament\Resources\ContentSourceResource\Pages;

use App\Filament\Resources\ContentSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentSources extends ListRecords
{
    protected static string $resource = ContentSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('initialize_defaults')
                ->label('Initialize Default Sources')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(fn () => $this->initializeDefaults())
                ->requiresConfirmation()
                ->modalHeading('Initialize Default Sources')
                ->modalDescription('This will create 10 pre-configured trusted sources (TechCrunch, Dev.to, etc.)')
                ->modalSubmitActionLabel('Initialize'),
        ];
    }

    protected function initializeDefaults(): void
    {
        $whitelist = new \App\Services\SourceWhitelistService();
        $count = $whitelist->initializeDefaultSources();

        \Filament\Notifications\Notification::make()
            ->title('Sources Initialized')
            ->body("Created $count trusted sources.")
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }
}
