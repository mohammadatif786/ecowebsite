<?php

namespace App\Jobs;

use App\Mail\SendMessageToAttendeeMail;
use App\Models\Notification;
use App\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TicketSale;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendMessageToAttendeeJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected TicketSale $attendee;
    protected string $subject;
    protected string $body;
    protected array $channels;
    protected int $organizerId;

    /**
     * Create a new job instance.
     */
    public function __construct(TicketSale $attendee, string $subject, string $body, array $channels, int $organizerId = 1)
    {
        $this->attendee = $attendee;
        $this->subject = $subject;
        $this->body = $body;
        $this->channels = $channels;
        $this->organizerId = $organizerId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (($this->channels['chEmail'] ?? false) && $this->attendee->user?->email) {
            Mail::to($this->attendee->user->email)->send(
                new SendMessageToAttendeeMail($this->attendee, $this->subject, $this->body)
            );
        }

        if (($this->channels['chSMS'] ?? false) && $this->attendee->user?->phone_number) {
            try {
                $phone = (string) $this->attendee->user->phone_number;
                $phone = trim($phone);
                if ($phone !== '' && !str_starts_with($phone, '+')) {
                    $phone = '+' . ltrim($phone, '0');
                }

                if ($phone !== '+') {
                    app(TwilioService::class)->sendSMS($phone, $this->body);
                }
            } catch (\Throwable $e) {
                Log::warning('Broadcast SMS send failed', [
                    'ticket_sale_id' => $this->attendee->id ?? null,
                    'user_id' => $this->attendee->user_id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (($this->channels['chLinkUp'] ?? false)) {
            $organizer = \App\Models\User::find($this->organizerId);
            $organizerPhoto = $organizer?->organizerProfile?->media?->profile_photo
                ? asset($organizer->organizerProfile->media->profile_photo)
                : null;

            Notification::create([
                'title'    => $this->subject,
                'message'  => $this->body,
                'send_by'  => $this->organizerId,
                'user_id'  => $this->attendee->user->id,
                'type'     => 'message',
                'context'  => 'event_message',
                'unread'   => true,
                'priority' => true,
                'icon'     => 'message',
                'avatar'   => $organizerPhoto ?? ($organizer?->avatar ?? null),
            ]);
        }
    }
}
