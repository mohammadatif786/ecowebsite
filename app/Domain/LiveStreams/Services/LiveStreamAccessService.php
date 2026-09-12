<?php

namespace App\Domain\LiveStreams\Services;

use Carbon\Carbon;
use App\Models\LiveStreamGumlet;
use App\Models\PrivateLiveStreamSub;
use App\Models\User;

class LiveStreamAccessService
{
    public function mayJoinSession(User $user, LiveStreamGumlet $stream): bool
    {
        if ($stream->status !== 'live') return false;
        if ((string) $user->id === (string) $stream->user_id || $user->hasRole('admin')) return true;
        if (collect($stream->guests ?? [])->contains(
            fn (array $guest): bool => (string) ($guest['id'] ?? '') === (string) $user->id
                && ($guest['status'] ?? 'joined') === 'joined'
        )) return true;
        if (($stream->visibility ?? 'public') === 'public') return true;
        if (($stream->visibility ?? '') !== 'private') return false;

        $oneTime = PrivateLiveStreamSub::query()->where('pay_user_id', $user->id)
            ->whereIn('status', ['active', 'Active'])
            ->where('stream_id', $stream->id)
            ->exists();

        if ($oneTime) return true;

        $monthly = PrivateLiveStreamSub::query()->where('pay_user_id', $user->id)
            ->where('user_streamer_id', $stream->user_id)
            ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
            ->whereIn('status', ['active', 'Active'])
            ->latest()
            ->first();

        return $monthly && Carbon::parse($monthly->created_at)->addDays(30)->isFuture();
    }
}
