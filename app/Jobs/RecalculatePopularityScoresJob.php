<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PopularityScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculatePopularityScoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PopularityScoreService $service): void
    {
        User::query()->select(['id'])->chunkById(500, function ($users) use ($service) {
            foreach ($users as $user) {
                $u = User::find($user->id);
                if (!$u) {
                    continue;
                }
                $service->updateUserScore($u);
            }
        });
    }
}
