<?php

namespace App\Console\Commands;

use App\Services\Content\TopicQueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RankTopicsCommand extends Command
{
    protected $signature = 'content:rank-topics {--limit=10} {--window=7}';
    protected $description = 'Yig\'ilgan kontentdan trend mavzular navbatini chiqaradi';

    public function __construct(private readonly TopicQueueService $queue)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $candidates = $this->queue->topCandidates(
            (int) $this->option('limit'),
            (int) $this->option('window')
        );

        if ($candidates->isEmpty()) {
            $this->info('Hozircha nomzod mavzu yo\'q — yig\'ilgan kontent yetarli emas.');

            return self::SUCCESS;
        }

        $this->table(
            ['#', 'Ball', 'Manba', 'Mavzu'],
            $candidates->values()->map(fn ($c, $i) => [
                $i + 1,
                $c['score'],
                $c['cluster_size'],
                \Illuminate\Support\Str::limit($c['title'], 70),
            ])->all()
        );

        Log::info('content:rank-topics produced candidates', ['count' => $candidates->count()]);

        return self::SUCCESS;
    }
}
