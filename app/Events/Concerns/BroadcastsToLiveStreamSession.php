<?php

namespace App\Events\Concerns;

use Illuminate\Broadcasting\PrivateChannel;
use LogicException;

trait BroadcastsToLiveStreamSession
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('live-stream.' . $this->streamPublicId())];
    }

    public function broadcastWith(): array
    {
        $payload = $this->payload;
        unset($payload['stream_id']);
        $payload['public_id'] = $this->streamPublicId();

        return $payload;
    }

    private function streamPublicId(): string
    {
        $publicId = $this->payload['public_id'] ?? null;

        if (! is_string($publicId) || $publicId === '') {
            throw new LogicException('Live stream session broadcasts require a public_id.');
        }

        return $publicId;
    }
}
