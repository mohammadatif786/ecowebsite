<?php

namespace App\Jobs;

use App\Mail\NewEventCreatedMail;
use App\Models\EmailLog;
use App\Models\LinkUpEvent;
use App\Models\OrganizerFollower;
use App\Models\OrganizerProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendNewEventToFollowersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function handle(): void
    {
        $event = LinkUpEvent::find($this->eventId);
        if (! $event) {
            return;
        }

        $organizer = OrganizerProfile::with('user')->find($event->organizer_id);
        if (! $organizer) {
            return;
        }

        OrganizerFollower::with('user')
            ->where('organizer_id', $organizer->id)
            ->whereNotNull('user_id')
            ->chunkById(200, function ($followers) use ($event, $organizer) {
                foreach ($followers as $follower) {
                    $user = $follower->user;
                    if (! $user || ! $user->email) {
                        continue;
                    }

                    $emailLog = null;

                    try {
                        $organizerName = $organizer->organizer_name
                            ?? $organizer->user?->name
                            ?? $event->organizer_name
                            ?? 'Organizer';

                        $emailLog = EmailLog::create([
                            'token' => (string) Str::uuid(),
                            'email_type' => 'organizer_new_event',
                            'to_email' => (string) $user->email,
                            'to_user_id' => (int) $user->id,
                            'from_user_id' => (int) ($organizer->user_id ?? 0) ?: null,
                            'subject' => 'New event from ' . $organizerName,
                            'status' => 'sending',
                            'meta' => [
                                'event_id' => (int) $event->id,
                                'organizer_id' => (int) $organizer->id,
                            ],
                        ]);

                        Mail::to($user->email)->send(new NewEventCreatedMail($event, $organizer, $user, (string) $emailLog->token));

                        $emailLog->status = 'sent';
                        $emailLog->sent_at = now();
                        $emailLog->save();
                    } catch (\Throwable $e) {
                        if ($emailLog) {
                            $emailLog->status = 'failed';
                            $emailLog->error = $e->getMessage();
                            $emailLog->save();
                        }

                        Log::warning('Failed to send organizer new event email', [
                            'event_id' => $event->id,
                            'organizer_id' => $organizer->id,
                            'to_user_id' => $user?->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
