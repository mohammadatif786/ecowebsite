<?php

namespace App\Console\Commands;

use App\Jobs\SendInterestMatchEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendInterestMatchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-interest-match-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $users = User::whereNotNull('interests')->get();
            $matches = [];

            foreach ($users as $user) {
                $userInterests = $user->interests;

                if (!is_array($userInterests) || empty($userInterests)) {
                    continue;
                }

                $userInterestCount = count($userInterests);
                if ($userInterestCount < 5) {
                    continue;
                }
                $userMatches = [];

                foreach ($users as $potentialMatch) {
                    if ($user->id == $potentialMatch->id) {
                        continue;
                    }

                    $matchInterests = $potentialMatch->interests;

                    if (!is_array($matchInterests) || empty($matchInterests)) {
                        continue;
                    }

                    $matchInterestCount = count($matchInterests);
                    if ($matchInterestCount < 5) {
                        continue;
                    }

                    $commonInterests = array_intersect($userInterests, $matchInterests);
                    $commonCount = count($commonInterests);

                    // Send email only if both users share at least 5 interests
                    if ($commonCount >= 5) {
                        $denominator = max($userInterestCount, $matchInterestCount, 1);
                        $matchPercentage = $commonCount / $denominator;

                        if ($matchPercentage < 0.90) {
                            continue;
                        }

                        $userMatches[] = [
                            'user' => $potentialMatch,
                            'percentage' => round($matchPercentage * 100),
                            'common_interests' => array_values($commonInterests)
                        ];
                    }
                }

                // Sort by percentage descending and take top 3
                usort($userMatches, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });
                $topMatches = array_slice($userMatches, 0, 3);

                if (!empty($topMatches)) {
                    $matches[$user->id] = $topMatches;
                }
            }

            foreach ($matches as $userId => $userMatches) {
                $user = User::find($userId);
                SendInterestMatchEmailJob::dispatch($user, $userMatches);
            }
        } catch (\Exception $e) {
            $this->error('Error sending interest match emails: ' . $e->getMessage());
        }
    }
}
