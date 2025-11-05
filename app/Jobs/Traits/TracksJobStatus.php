<?php

namespace App\Jobs\Traits;

use App\Models\JobStatus;
use Illuminate\Database\Eloquent\Model;

trait TracksJobStatus
{
    protected ?JobStatus $jobStatus = null;

    /**
     * Create job status record
     */
    protected function createJobStatus(
        string $type,
        Model $trackable,
        ?int $userId = null,
        array $metadata = []
    ): JobStatus {
        $this->jobStatus = JobStatus::create([
            'job_id' => $this->job->uuid() ?? uniqid('job_'),
            'type' => $type,
            'queue' => $this->queue ?? 'default',
            'status' => 'pending',
            'user_id' => $userId,
            'trackable_type' => get_class($trackable),
            'trackable_id' => $trackable->id,
            'metadata' => $metadata,
        ]);

        return $this->jobStatus;
    }

    /**
     * Get existing job status or create new one
     */
    protected function getOrCreateJobStatus(
        string $type,
        Model $trackable,
        ?int $userId = null,
        array $metadata = []
    ): JobStatus {
        if ($this->jobStatus) {
            return $this->jobStatus;
        }

        $jobId = $this->job->uuid() ?? uniqid('job_');

        $this->jobStatus = JobStatus::firstOrCreate(
            ['job_id' => $jobId],
            [
                'type' => $type,
                'queue' => $this->queue ?? 'default',
                'status' => 'pending',
                'user_id' => $userId,
                'trackable_type' => get_class($trackable),
                'trackable_id' => $trackable->id,
                'metadata' => $metadata,
            ]
        );

        return $this->jobStatus;
    }

    /**
     * Mark job as started
     */
    protected function markJobStarted(): void
    {
        if ($this->jobStatus) {
            $this->jobStatus->markAsStarted();
        }
    }

    /**
     * Update job progress
     */
    protected function updateJobProgress(int $progress, ?string $message = null): void
    {
        if ($this->jobStatus) {
            $this->jobStatus->updateProgress($progress, $message);
        }
    }

    /**
     * Mark job as completed
     */
    protected function markJobCompleted(array $metadata = []): void
    {
        if ($this->jobStatus) {
            $this->jobStatus->markAsCompleted($metadata);
        }
    }

    /**
     * Mark job as failed
     */
    protected function markJobFailed(string $errorMessage): void
    {
        if ($this->jobStatus) {
            $this->jobStatus->markAsFailed($errorMessage, $this->attempts());
        }
    }
}
