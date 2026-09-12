<?php

namespace App\Services;

use App\Models\Message;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Support\Facades\DB;

class PopularityScoreService
{
    public function calculateForUser(User $user): int
    {
        $views = $this->profileViews($user);
        $likesReceived = $this->likesReceived($user);
        $messagesReceived = $this->messagesReceived($user);
        $matches = $this->matches($user);
        $activity = $this->activityLevel($user);
        $eventParticipation = $this->eventParticipation($user);

        $score = 0;
        $score += $views * 1;
        $score += $likesReceived * 3;
        $score += $messagesReceived * 2;
        $score += $matches * 5;
        $score += $activity * 2;
        $score += $eventParticipation * 4;

        return max(0, (int) $score);
    }

    public function updateUserScore(User $user): int
    {
        $score = $this->calculateForUser($user);
        $user->forceFill([
            'popularity_score' => $score,
            'popularity_score_updated_at' => now(),
        ])->save();

        return $score;
    }

    public function bump(User $user, int $points): int
    {
        $points = max(0, (int) $points);
        if ($points <= 0) {
            return (int) ($user->popularity_score ?? 0);
        }

        User::query()->whereKey($user->id)->increment('popularity_score', $points, [
            'popularity_score_updated_at' => now(),
        ]);

        $user->refresh();
        return (int) ($user->popularity_score ?? 0);
    }

    public function scoreToLevel(int $score): string
    {
        if ($score < 50) return 'Low';
        if ($score < 200) return 'Medium';
        if ($score < 500) return 'High';
        return 'Very High';
    }

    private function profileViews(User $user): int
    {
        return (int) DB::table('user_profile_views')
            ->where('viewed_user_id', $user->id)
            ->count();
    }

    private function likesReceived(User $user): int
    {
        return (int) UserMatch::query()
            ->where('target_user_id', $user->id)
            ->whereIn('status', ['like', 'loveit'])
            ->count();
    }

    private function messagesReceived(User $user): int
    {
        return (int) Message::query()
            ->where('to_user_id', $user->id)
            ->count();
    }

    private function matches(User $user): int
    {
        $likedUserIds = UserMatch::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['like', 'loveit'])
            ->pluck('target_user_id');

        if ($likedUserIds->isEmpty()) {
            return 0;
        }

        return (int) UserMatch::query()
            ->whereIn('user_id', $likedUserIds)
            ->where('target_user_id', $user->id)
            ->whereIn('status', ['like', 'loveit'])
            ->count();
    }

    private function activityLevel(User $user): int
    {
        if (!$user->last_active) {
            return 0;
        }

        $days = now()->diffInDays($user->last_active);
        if ($days <= 1) return 10;
        if ($days <= 7) return 5;
        if ($days <= 30) return 2;
        return 0;
    }

    private function eventParticipation(User $user): int
    {
        $checkins = TicketCheckin::query()
            ->whereHas('ticketSale', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        $confirmedPurchases = TicketSale::query()
            ->where('user_id', $user->id)
            ->where('ticket_status', 'confirmed')
            ->count();

        return (int) ($checkins + $confirmedPurchases);
    }
}
