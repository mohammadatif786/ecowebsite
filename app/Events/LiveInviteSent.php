<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class LiveInviteSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn(): array
    {
        // Broadcast to the specific user who is invited
        return [new PrivateChannel('App.Models.User.' . $this->payload['invitee_id'])];
    }

    public function broadcastAs(): string
    {
        return 'LiveInviteSent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
