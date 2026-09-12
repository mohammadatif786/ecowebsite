<?php

namespace App\Actions;

use App\DTOs\OrganizerStatsResult;
use App\Models\LinkUpEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuildOrganizerStatsAction
{
    public function handle(LinkUpEvent $event): OrganizerStatsResult
    {
        $organizerProfile = null;
        $organizerId = null;
        $organizerName = $event->organizer_name ?? 'Unknown Organizer';
        $organizerAvatar = null;
        $organizerCurrency = 'US';

        if ($event->organizer_id && $event->organizer) {
            $organizerProfile = $event->organizer;
            $organizerId = $event->organizer_id;
            $organizerName = $organizerProfile->organizer_name ?? $organizerName;
            $organizerCurrency = $organizerProfile->contacts?->country;

            if ($organizerProfile->media && $organizerProfile->media->logo) {
                $organizerAvatar = asset('storage/' . $organizerProfile->media->logo);
            } elseif ($organizerProfile->user) {
                $organizerAvatar = $organizerProfile->user->avatar;
            }
        }

        if (! $organizerId && $event->organizer_name) {
            Log::warning('Legacy event without organizer_id', [
                'event_id' => $event->id,
                'organizer_name' => $event->organizer_name,
            ]);
        }

        $stats = [
            'organizer_id' => $organizerId,
            'organizer_name' => $organizerName,
            'organizer_avatar' => $organizerAvatar,
            'followers_count' => $organizerProfile ? $organizerProfile->followers()->count() : 0,
            'events_count' => $organizerProfile ? $organizerProfile->events()->count() : 0,
            'hosting_since' => $organizerProfile && $organizerProfile->events()->exists()
                ? $organizerProfile->events()->oldest()->first()->created_at->format('Y')
                : now()->format('Y'),
            'is_following' => $organizerProfile && Auth::check()
                ? Auth::user()->isFollowingOrganizer($organizerProfile->id)
                : false,
        ];

        return new OrganizerStatsResult($stats, $organizerCurrency);
    }
}
