<?php

namespace App\Mail;

use App\Models\LiveStreamGumlet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class LiveStreamCreatedMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected string $appUrl;

    public function __construct(
        public LiveStreamGumlet $stream,
        public User $host,
        public User $recipient,
        public ?string $trackingToken = null,
    ) {
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    public function envelope(): Envelope
    {
        $hostName = $this->host->name ?? 'A creator';

        return new Envelope(
            subject: $hostName . ' is live now 🔴',
        );
    }

    public function content(): Content
    {
        $ad = $this->getSponsorAd('livestreaming', $this->recipient->country_code);

        $streamUrl = $this->buildStreamUrl();

        $startedLabel = $this->formatStartTime($this->stream->start_time);

        $viewerCount = $this->stream->viewer_count ?? 0;
        $viewerLabel = $viewerCount >= 1000
            ? round($viewerCount / 1000, 1) . 'K'
            : $viewerCount;

        $visibilityLabel = match (strtolower($this->stream->visibility ?? 'public')) {
            'private'   => '🔒 Private',
            'followers' => '👥 Followers',
            default     => '🌐 Public',
        };

        $categoryLabel = $this->formatCategory($this->stream->broadcast_type);

        $hostAvatarUrl   = $this->host->avatar ?? null;
        $hostAvatarLetter = strtoupper(substr($this->host->first_name ?? $this->host->name ?? 'U', 0, 1));

        $hostCountryFlag = $this->countryCodeToFlag($this->host->country_code ?? '');
        $hostCountryName = $this->host->country ?? '';

        $recipientFirstName = $this->recipient->first_name
            ?? explode(' ', $this->recipient->name ?? 'there')[0];

        return new Content(
            view: 'emails.liveStreamCreated',
            with: [
                'stream'             => $this->stream,
                'host'               => $this->host,
                'recipient'          => $this->recipient,
                'logoUrl'            => $this->appUrl . 'assets/images/logo.png',
                'ad'                 => $ad,
                'trackingToken'      => $this->trackingToken,
                'streamUrl'          => $streamUrl,
                'coverImageUrl'      => $this->stream->cover_image,
                'streamTitle'        => $this->stream->title ?? 'Live Stream',
                'viewerLabel'        => $viewerLabel,
                'startedLabel'       => $startedLabel,
                'visibilityLabel'    => $visibilityLabel,
                'categoryLabel'      => $categoryLabel,
                'hostName'           => $this->host->name ?? 'Unknown',
                'hostAvatarUrl'      => $hostAvatarUrl,
                'hostAvatarLetter'   => $hostAvatarLetter,
                'hostCountryFlag'    => $hostCountryFlag,
                'hostCountryName'    => $hostCountryName,
                'recipientFirstName' => $recipientFirstName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Build the deep-link URL to open the live stream in the app / web.
     */
    private function buildStreamUrl(): string
    {
        $base = rtrim($this->appUrl, '/');
        $id   = $this->stream->id;

        $url = "{$base}/join/stream//{$id}";

        if ($this->trackingToken) {
            $url .= '?t=' . urlencode($this->trackingToken);
        }

        return $url;
    }

    /**
     * Human-readable "started X ago / Just Now" label.
     */
    private function formatStartTime(?string $startTime): string
    {
        if (! $startTime) {
            return 'Just Now';
        }

        try {
            $diff = Carbon::parse($startTime)->diffInMinutes(now());

            if ($diff < 1)  return 'Just Now';
            if ($diff < 60) return $diff . 'm ago';

            $hours = floor($diff / 60);
            return $hours . 'h ago';
        } catch (\Throwable) {
            return 'Just Now';
        }
    }

    /**
     * Map broadcast_type to a display category label.
     * Adjust the cases to match your actual enum/string values.
     */
    private function formatCategory(?string $broadcastType): string
    {
        return match (strtolower($broadcastType ?? '')) {
            'music'       => '🎵 Music',
            'gaming'      => '🎮 Gaming',
            'sports'      => '⚽ Sports',
            'cooking'     => '🍳 Cooking',
            'talk'        => '🎙️ Talk',
            'fitness'     => '💪 Fitness',
            'travel'      => '✈️ Travel',
            'education'   => '📚 Education',
            default       => '📡 Live',
        };
    }

    /**
     * Convert an ISO 3166-1 alpha-2 country code to a flag emoji.
     * Works for any two-letter code (US → 🇺🇸, BB → 🇧🇧, etc.)
     */
    private function countryCodeToFlag(string $code): string
    {
        $code = strtoupper(trim($code));

        if (strlen($code) !== 2) {
            return '';
        }

        $chars = array_map(
            fn(string $char) => mb_chr(ord($char) - ord('A') + 0x1F1E6, 'UTF-8'),
            str_split($code)
        );

        return implode('', $chars);
    }
}
