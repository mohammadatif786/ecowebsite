<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsToLiveStreamSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class PollVoted implements ShouldBroadcast
{
    use BroadcastsToLiveStreamSession, Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastAs(): string
    {
        return 'PollVoted';
    }

}

