<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsToLiveStreamSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveProductsUpdated implements ShouldBroadcast
{
    use BroadcastsToLiveStreamSession, Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $payload) {}

    public function broadcastAs(): string
    {
        return 'LiveProductsUpdated';
    }
}
