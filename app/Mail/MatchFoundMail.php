<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchFoundMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected User $authUser;
    protected User $targetUser;
    protected string $appUrl;

    public function __construct(User $authUser, User $targetUser)
    {
        $this->authUser = $authUser;
        $this->targetUser = $targetUser;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "💜 It's a Match! You and {$this->targetUser->name} connected on LinkUp",
        );
    }

    public function content(): Content
    {
        $ad = $this->getSponsorAd('like_received', $this->targetUser->country_code);

        return new Content(
            view: 'emails.matchFound',
            with: [
                'current_user_name' => $this->authUser,
                'matched_user_name' => $this->targetUser,
                'logoUrl' => $this->appUrl . 'assets/images/logo.png',
                'country_flag' => $this->getCountryFlag($this->targetUser->country ?? null),
                'ad' => $ad,
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
}
