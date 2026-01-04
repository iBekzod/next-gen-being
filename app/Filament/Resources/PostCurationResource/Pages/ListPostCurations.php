<?php

namespace App\Filament\Resources\PostCurationResource\Pages;

use App\Filament\Resources\PostCurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostCurations extends ListRecords
{
    protected static string $resource = PostCurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('run_paraphrase_job')
                ->label('Run Paraphrasing Job')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(fn () => $this->runParaphraseJob())
                ->requiresConfirmation()
                ->modalHeading('Run Paraphrasing Job')
                ->modalDescription('This will queue a paraphrasing job for pending aggregations.'),
        ];
    }

    protected function runParaphraseJob(): void
    {
        $pending = \App\Models\ContentAggregation::whereNull('curated_at')->first();

        if (!$pending) {
            \Filament\Notifications\Notification::make()
                ->title('No Pending Aggregations')
                ->body('All content aggregations have been curated.')
                ->warning()
                ->send();
            return;
        }

        \App\Jobs\ParaphraseAggregationJob::dispatch($pending->id);

        \Filament\Notifications\Notification::make()
            ->title('Paraphrasing Queued')
            ->body("Queued paraphrasing for: {$pending->topic}")
            ->success()
            ->send();
    }
}
