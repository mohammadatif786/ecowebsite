<?php

namespace App\Events;

use App\Models\LiveStreamGumlet;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class StreamEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LiveStreamGumlet $stream;

    public function __construct(LiveStreamGumlet $stream)
    {
        $this->stream = $stream;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('live-stream.' . $this->stream->public_id),
            new Channel('live-streams'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StreamEnded';
    }

    public function broadcastWith(): array
    {
        return [
            'public_id' => $this->stream->public_id,
            'status' => $this->stream->status,
        ];
    }
}
