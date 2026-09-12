<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsToLiveStreamSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LiveReactionSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct($data)
    {
        $this->payload = $data;
    }

    public function broadcastAs(): string
    {
        return 'LiveReactionSent';
    }
}
