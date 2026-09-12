<?php

namespace App\Policies;

use App\Domain\LiveStreams\Services\LiveStreamAccessService;
use App\Models\LiveStreamGumlet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LiveStreamPolicy
{
    use HandlesAuthorization;

    protected $module = 'live_streams';

    public function view(User $user, LiveStreamGumlet $stream): bool
    {
        return $stream->visibility !== 'private' || $this->owns($user, $stream);
    }

    public function joinLiveSession(User $user, LiveStreamGumlet $stream): bool
    {
        return app(LiveStreamAccessService::class)->mayJoinSession($user, $stream);
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('banned');
    }

    public function viewAnalytics(User $user): bool
    {
        return ! $user->hasRole('banned');
    }

    public function transferEarnings(User $user): bool
    {
        return ! $user->hasRole('banned');
    }

    public function update(User $user, LiveStreamGumlet $stream): bool
    {
        return $this->owns($user, $stream);
    }

    public function start(User $user, LiveStreamGumlet $stream): bool
    {
        return $this->owns($user, $stream);
    }

    public function end(User $user, LiveStreamGumlet $stream): bool
    {
        return $this->owns($user, $stream);
    }

    public function delete(User $user, LiveStreamGumlet $stream): bool
    {
        return $this->owns($user, $stream);
    }

    public function broadcast(User $user, LiveStreamGumlet $stream): bool
    {
        if ($this->owns($user, $stream)) {
            return true;
        }

        if ($stream->status !== 'live') {
            return false;
        }

        return collect($stream->guests ?? [])->contains(
            fn (array $guest): bool => (string) ($guest['id'] ?? '') === (string) $user->id
                && ($guest['status'] ?? 'joined') === 'joined'
        );
    }

    private function owns(User $user, LiveStreamGumlet $stream): bool
    {
        return (string) $stream->user_id === (string) $user->id || $user->hasRole('admin');
    }
}
