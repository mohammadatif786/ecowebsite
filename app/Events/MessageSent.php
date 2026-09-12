<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->message->to_user_id),
            new PrivateChannel('App.Models.User.' . $this->message->from_user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'from_user_id' => $this->message->from_user_id,
            'to_user_id' => $this->message->to_user_id,
            'content' => $this->message->content,
            'replied_to' => $this->message->replied_to,
            'type' => $this->message->type,
            'meta' => $this->message->meta,
            'created_at' => $this->message->created_at->toDateTimeString(),
            'sender' => ['name' => $this->message->sender?->name],
            'receiver' => ['name' => $this->message->receiver?->name],
        ];
    }
}
