<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
class ChatMessageReceivedMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected Message $message;
    protected ?string $trackingToken;
    protected string $appUrl;

    public function __construct(Message $message, ?string $trackingToken = null)
    {
        $this->message = $message;
        $this->trackingToken = $trackingToken;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    public function envelope(): Envelope
    {
        $senderName = $this->message->relationLoaded('sender') ? $this->message->sender?->name : null;

        return new Envelope(
            subject: $senderName ? ('New message from ' . $senderName) : 'New message',
        );
    }

    public function content(): Content
    {
        $message = $this->message;

        if (!$message->relationLoaded('sender')) {
            $message->load('sender');
        }
        if (!$message->relationLoaded('receiver')) {
            $message->load('receiver');
        }

        $sender = $message->sender;
        $receiver = $message->receiver;

        $ad = $this->getSponsorAd('new_message', $receiver->country_code);


        return new Content(
            view: 'emails.chatMessageReceived',
            with: [
                'recipient_name' => $receiver->name ?? 'User',
                'sender_name' => $sender->name ?? 'Sender',
                'sender_avatar' => $sender->avatar ?? null,
                'sender_country' => $sender->country ?? null,
                'sender_country_flag' => $this->getCountryFlag($sender->country ?? null),
                'sender_is_online' => $this->isUserOnline($sender),
                'message_content' => $message->content,
                'message_type' => $message->type,
                'message_meta' => $message->meta,
                'message_time' => $message->created_at?->diffForHumans() ?? 'Just now',
                'chat_url' => route('frontend.user.start.chat', ['slug' => Str::slug($sender->name ?? 'user'), 'user' => $sender->uid]),
                'country_name' => $receiver->country ?? null,
                'country_flag' => $this->getCountryFlag($receiver->country ?? null),
                'support_email' => 'support@linkup.com',
                'ad' => $ad,
                'logoURL' => $this->appUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    /**
     * Get country flag emoji from country name
     */
    private function getCountryFlag(?string $country): ?string
    {
        if (!$country) {
            return null;
        }

        $flags = [
            'United States' => '🇺🇸',
            'United Kingdom' => '🇬🇧',
            'Canada' => '🇨🇦',
            'Australia' => '🇦🇺',
            'Jamaica' => '🇯🇲',
            'Trinidad and Tobago' => '🇹🇹',
            'Barbados' => '🇧🇧',
            'Bahamas' => '🇧🇸',
            'Cuba' => '🇨🇺',
            'Dominican Republic' => '🇩🇴',
            'Puerto Rico' => '🇵🇷',
            'Mexico' => '🇲🇽',
            'Brazil' => '🇧🇷',
            'Argentina' => '🇦🇷',
            'Colombia' => '🇨🇴',
            'Peru' => '🇵🇪',
            'Venezuela' => '🇻🇪',
            'Chile' => '🇨🇱',
            'Spain' => '🇪🇸',
            'France' => '🇫🇷',
            'Germany' => '🇩🇪',
            'Italy' => '🇮🇹',
            'India' => '🇮🇳',
            'China' => '🇨🇳',
            'Japan' => '🇯🇵',
            'South Korea' => '🇰🇷',
            'Nigeria' => '🇳🇬',
            'South Africa' => '🇿🇦',
            'Kenya' => '🇰🇪',
            'Ghana' => '🇬🇭',
        ];

        return $flags[$country] ?? null;
    }

    /**
     * Check if user is online (considered online if active in last 5 minutes)
     */
    private function isUserOnline($user): bool
    {
        if (!$user || !$user->last_active) {
            return false;
        }

        return $user->last_active->gt(now()->subMinutes(5));
    }
}
