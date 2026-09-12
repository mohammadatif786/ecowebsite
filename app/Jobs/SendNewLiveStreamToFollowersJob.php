<?php

namespace App\Jobs;

use App\Models\LiveStreamGumlet;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Jobs\SendLiveStreamEmailChunkJob;

class SendNewLiveStreamToFollowersJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $streamId;

    public function __construct(int $streamId)
    {
        $this->streamId = $streamId;
    }

    public function handle(): void
    {
        $stream = LiveStreamGumlet::with('user')->find($this->streamId);
        if (! $stream) {
            return;
        }

        $host = $stream->user;
        if (! $host) {
            return;
        }

        User::whereNotNull('email')
            ->where('id', '!=', $host->id)
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($stream) {
                $ids = $users->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

                if (! empty($ids)) {
                    SendLiveStreamEmailChunkJob::dispatch((int) $stream->id, $ids)->delay(now()->addSeconds(10));
                }
            });
    }
}
