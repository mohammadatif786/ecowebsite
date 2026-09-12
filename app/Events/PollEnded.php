<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsToLiveStreamSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PollEnded implements ShouldBroadcast
{
    use BroadcastsToLiveStreamSession, Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastAs(): string
    {
        return 'PollEnded';
    }

}

