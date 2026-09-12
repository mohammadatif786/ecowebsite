<?php

namespace App\Jobs;

use App\Mail\ChatMessageReceivedMail;
use App\Models\EmailLog;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendChatMessageEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    protected int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $emailLog = null;
        try {
            $message = Message::with(['sender', 'receiver'])->find($this->messageId);
            if (! $message) {
                return;
            }

            $receiver = $message->receiver;
            $sender = $message->sender;

            if (! $receiver || ! $sender) {
                return;
            }

            if ((int) $receiver->id === (int) $sender->id) {
                return;
            }

            if (! $receiver->email) {
                return;
            }

            $emailLog = EmailLog::create([
                'token' => (string) Str::uuid(),
                'email_type' => 'chat_message_received',
                'to_email' => (string) $receiver->email,
                'to_user_id' => (int) $receiver->id,
                'from_user_id' => (int) $sender->id,
                'subject' => $sender?->name ? ('New message from ' . $sender->name) : 'New message',
                'status' => 'sending',
                'meta' => [
                    'message_id' => (int) $message->id,
                    'message_type' => (string) ($message->type ?? 'text'),
                ],
            ]);

            Mail::to($receiver->email)->send(new ChatMessageReceivedMail($message, (string) $emailLog->token));

            $emailLog->status = 'sent';
            $emailLog->sent_at = now();
            $emailLog->save();
        } catch (\Throwable $e) {
            if ($emailLog) {
                $emailLog->status = 'failed';
                $emailLog->error = $e->getMessage();
                $emailLog->save();
            }
            Log::warning('Failed to send chat message email', [
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
