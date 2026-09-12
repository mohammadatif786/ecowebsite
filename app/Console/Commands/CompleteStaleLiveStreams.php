<?php

namespace App\Console\Commands;

use App\Models\LiveStreamGumlet;
use App\Services\LiveStreamLifecycleService;
use Illuminate\Console\Command;

class CompleteStaleLiveStreams extends Command
{
    protected $signature = 'live:complete-stale-host-streams {--minutes=2 : Minutes without host heartbeat before ending a live stream}';

    protected $description = 'Mark live streams completed when the host heartbeat is stale.';

    public function handle(LiveStreamLifecycleService $lifecycle): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $threshold = now()->subMinutes($minutes);
        $completed = 0;

        LiveStreamGumlet::query()
            ->where('status', 'live')
            ->where(function ($query) use ($threshold) {
                $query
                    ->where('host_heartbeat_at', '<', $threshold)
                    ->orWhere(function ($query) use ($threshold) {
                        $query
                            ->whereNull('host_heartbeat_at')
                            ->where('updated_at', '<', $threshold);
                    });
            })
            ->chunkById(50, function ($streams) use ($lifecycle, &$completed) {
                foreach ($streams as $stream) {
                    if ($lifecycle->complete($stream)) {
                        $completed++;
                    }
                }
            });

        $this->info("Completed {$completed} stale live stream(s).");

        return self::SUCCESS;
    }
}
