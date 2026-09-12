<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class StreamStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $stream;

    public function __construct(array $stream)
    {
        $this->stream = $stream;
    }

    public function broadcastOn(): array
    {
        return [new Channel('live-streams')];
    }

    public function broadcastAs(): string
    {
        return 'StreamStarted';
    }

    public function broadcastWith(): array
    {
        return $this->stream;
    }
}
