<?php

namespace App\Jobs;

use App\Mail\LiveStreamCreatedMail;
use App\Models\EmailLog;
use App\Models\LiveFollower;
use App\Models\LiveStreamGumlet;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendLiveStreamEmailChunkJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $streamId;

    /** @var int[] */
    protected array $userIds;

    /**
     * @param int[] $userIds
     */
    public function __construct(int $streamId, array $userIds)
    {
        $this->streamId = $streamId;
        $this->userIds = $userIds;
    }

    public function handle(): void
    {
        $stream = LiveStreamGumlet::with('user')->find($this->streamId);
        if (! $stream || ! $stream->user) {
            return;
        }

        $host = $stream->user;

        $followers = LiveFollower::with('user')->whereIn('user_id', $this->userIds)->get();

        foreach ($followers as $follower) {
            $emailLog = null;

            $user = $follower->user;
            if (! $user || ! $user->email) {
                continue;
            }

            try {
                $emailLog = EmailLog::create([
                    'token' => (string) Str::uuid(),
                    'email_type' => 'live_stream_created',
                    'to_email' => (string) $user->email,
                    'to_user_id' => (int) $user->id,
                    'from_user_id' => (int) $host->id,
                    'subject' => ($host->name ? ($host->name . ' is live now') : 'Live stream started'),
                    'status' => 'sending',
                    'meta' => [
                        'stream_id' => (int) $stream->id,
                        'visibility' => (string) ($stream->visibility ?? 'public'),
                    ],
                ]);

                Mail::to($user->email)->send(new LiveStreamCreatedMail($stream, $host, $user, (string) $emailLog->token));

                $emailLog->status = 'sent';
                $emailLog->sent_at = now();
                $emailLog->save();

                // Create notification for the follower
                Notification::create([
                    'user_id' => $user->id,
                    'send_by' => $host->id,
                    'title' => 'New Live Stream',
                    'message' => $host->name . ' has started a new live stream: ' . $stream->title,
                    'type' => 'live_stream',
                    'context' => 'live_stream',
                    'unread' => true,
                    'priority' => true,
                    'icon' => '🔴',
                    'avatar' => $host->avatar,
                    'metadata' => [
                        'stream_id' => $stream->id,
                        'stream_title' => $stream->title,
                        'host_name' => $host->name,
                        'host_avatar' => $host->avatar,
                        'visibility' => $stream->visibility,
                    ]
                ]);
            } catch (\Throwable $e) {
                if ($emailLog) {
                    $emailLog->status = 'failed';
                    $emailLog->error = $e->getMessage();
                    $emailLog->save();
                }

                Log::warning('Failed to send live stream created email (chunk)', [
                    'stream_id' => $stream->id,
                    'host_user_id' => $host->id,
                    'to_user_id' => $user?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
