<?php

namespace App\Filament\Resources\VideoGenerationResource\Pages;

use App\Filament\Resources\VideoGenerationResource;
use Filament\Resources\Pages\CreateRecord;
use App\Jobs\GenerateVideoJob;

class CreateVideoGeneration extends CreateRecord
{
    protected static string $resource = VideoGenerationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Set status based on scheduling
        if (!empty($data['scheduled_at'])) {
            $data['status'] = 'scheduled';
        } else {
            $data['status'] = 'queued';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // If not scheduled, dispatch job immediately
        if ($this->record->status === 'queued') {
            GenerateVideoJob::dispatch($this->record->post, $this->record->video_type);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->record->isScheduled()
            ? 'Video generation scheduled successfully'
            : 'Video generation queued successfully';
    }
}