<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MatchingService
{
    /**
     * Calculate a compatibility score between two users based on:
     * - Interest overlap (40%)
     * - Activity level (20%)
     * - Popularity proximity (20%)
     * - Behavior patterns (20%)
     */
    public function calculateCompatibilityScore(User $currentUser, User $targetUser): float
    {
        $interestScore = $this->calculateInterestScore($currentUser, $targetUser);
        $activityScore = $this->calculateActivityScore($targetUser);
        $popularityScore = $this->calculatePopularityProximityScore($currentUser, $targetUser);
        $behaviorScore = $this->calculateBehaviorScore($currentUser, $targetUser);

        // Weighted total: 40% interests, 20% activity, 20% popularity, 20% behavior
        $totalScore = ($interestScore * 0.40) + ($activityScore * 0.20) + ($popularityScore * 0.20) + ($behaviorScore * 0.20);

        return round($totalScore, 2);
    }

    /**
     * Calculate interest overlap score (0-100)
     * Uses Jaccard similarity for interest arrays
     */
    protected function calculateInterestScore(User $currentUser, User $targetUser): float
    {
        $currentInterests = $currentUser->interests ?? [];
        $targetInterests = $targetUser->interests ?? [];

        if (empty($currentInterests) || empty($targetInterests)) {
            return 30.0; // Base score for users with no interests
        }

        $intersection = array_intersect($currentInterests, $targetInterests);
        $union = array_unique(array_merge($currentInterests, $targetInterests));

        if (empty($union)) {
            return 0.0;
        }

        $jaccardSimilarity = count($intersection) / count($union);

        // Scale to 0-100, with bonus for multiple shared interests
        $score = $jaccardSimilarity * 100;
        $bonus = min(count($intersection) * 5, 20); // Up to 20 bonus points for shared interests

        return min($score + $bonus, 100.0);
    }

    /**
     * Calculate activity score based on last_active timestamp (0-100)
     * More recent activity = higher score
     */
    protected function calculateActivityScore(User $user): float
    {
        if (!$user->last_active) {
            return 20.0; // Base score for users with no activity data
        }

        $daysSinceActive = now()->diffInDays($user->last_active);

        // Activity decay curve
        if ($daysSinceActive <= 1) return 100.0;       // Very active
        if ($daysSinceActive <= 3) return 90.0;        // Active
        if ($daysSinceActive <= 7) return 70.0;        // Recently active
        if ($daysSinceActive <= 14) return 50.0;       // Moderately active
        if ($daysSinceActive <= 30) return 30.0;       // Less active
        if ($daysSinceActive <= 60) return 15.0;       // Inactive
        return 5.0;                                    // Very inactive
    }

    /**
     * Calculate popularity proximity score (0-100)
     * Users with similar popularity levels get higher scores
     */
    protected function calculatePopularityProximityScore(User $currentUser, User $targetUser): float
    {
        $currentPopularity = $currentUser->popularity_score ?? 0;
        $targetPopularity = $targetUser->popularity_score ?? 0;

        // Calculate the absolute difference
        $difference = abs($currentPopularity - $targetPopularity);

        // Smaller difference = higher score
        // Max reasonable difference is around 500 points
        $score = max(0, 100 - ($difference / 5));

        // Bonus for target users with high popularity (shows social proof)
        if ($targetPopularity >= 500) {
            $score = min($score + 15, 100);
        } elseif ($targetPopularity >= 200) {
            $score = min($score + 10, 100);
        }

        return $score;
    }

    /**
     * Calculate behavior pattern score (0-100)
     * Based on:
     * - Response rate to messages
     * - Average response time
     * - Profile view reciprocity
     * - Event participation
     */
    protected function calculateBehaviorScore(User $currentUser, User $targetUser): float
    {
        $score = 50.0; // Base score

        // Message response rate (if they've interacted before)
        $responseRate = $this->calculateResponseRate($currentUser, $targetUser);
        $score += $responseRate * 10; // Up to 10 points

        // Profile view reciprocity
        $hasViewedCurrentUser = DB::table('user_profile_views')
            ->where('viewer_id', $targetUser->id)
            ->where('viewed_user_id', $currentUser->id)
            ->exists();
        
        if ($hasViewedCurrentUser) {
            $score += 15; // Bonus for reciprocal interest
        }

        // Event participation (indicates social engagement)
        $eventParticipation = $this->calculateEventParticipation($targetUser);
        $score += $eventParticipation * 2; // Up to 20 points

        // Message frequency (indicates engagement)
        $messageFrequency = $this->calculateMessageFrequency($targetUser);
        $score += min($messageFrequency, 5); // Up to 5 points

        return min($score, 100.0);
    }

    /**
     * Calculate response rate between two users
     */
    protected function calculateResponseRate(User $currentUser, User $targetUser): float
    {
        $messagesReceived = Message::where('from_user_id', $currentUser->id)
            ->where('to_user_id', $targetUser->id)
            ->count();

        $messagesReplied = Message::where('from_user_id', $targetUser->id)
            ->where('to_user_id', $currentUser->id)
            ->where('created_at', '>', function ($query) use ($currentUser, $targetUser) {
                $query->select(DB::raw('MAX(created_at)'))
                    ->from('messages')
                    ->where('from_user_id', $currentUser->id)
                    ->where('to_user_id', $targetUser->id);
            })
            ->count();

        if ($messagesReceived === 0) {
            return 0.5; // Neutral score for no interaction history
        }

        return min($messagesReplied / $messagesReceived, 1.0);
    }

    /**
     * Calculate event participation score
     */
    protected function calculateEventParticipation(User $user): float
    {
        $ticketSales = DB::table('ticket_sales')
            ->where('user_id', $user->id)
            ->where('ticket_status', 'confirmed')
            ->count();

        $checkins = DB::table('ticket_checkins')
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('ticket_sales')
                    ->whereColumn('ticket_sales.id', 'ticket_checkins.ticket_sale_id')
                    ->where('ticket_sales.user_id', $user->id);
            })
            ->count();

        return min(($ticketSales * 3) + ($checkins * 5), 10.0);
    }

    /**
     * Calculate message frequency (messages sent in last 30 days)
     */
    protected function calculateMessageFrequency(User $user): float
    {
        $messagesLast30Days = Message::where('from_user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return min($messagesLast30Days / 10, 5.0); // Normalize to 0-5 range
    }

    /**
     * Sort users by compatibility score for the current user
     */
    public function sortUsersByCompatibility(User $currentUser, $users): array
    {
        $usersWithScores = [];

        foreach ($users as $user) {
            $score = $this->calculateCompatibilityScore($currentUser, $user);
            $user->compatibility_score = $score;
            $usersWithScores[] = $user;
        }

        // Sort by compatibility score descending
        usort($usersWithScores, function ($a, $b) {
            return $b->compatibility_score <=> $a->compatibility_score;
        });

        return $usersWithScores;
    }
}
