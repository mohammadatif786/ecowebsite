<?php

namespace App\Console\Commands;

use App\Jobs\SendEventMailJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EventMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send event emails to users who follow organizers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all users who follow at least one organizer
        $users = User::whereHas('followingOrganizers')->with('followingOrganizers.organizer')->get();

        // Dynamically fetch category IDs for Cookouts/Food and Wellness/Spa
        $cookoutCategoryIds = \App\Models\EventCategory::where('name', 'like', '%Cookout%')
            ->orWhere('name', 'like', '%Food%')
            ->pluck('id');
            
        $wellnessCategoryIds = \App\Models\EventCategory::where('name', 'like', '%Wellness%')
            ->orWhere('name', 'like', '%Spa%')
            ->pluck('id');

        // Fetch global cookouts and wellness events once
        $cookouts = \App\Models\LinkUpEvent::whereIn('category_id', $cookoutCategoryIds)->where('start_time', '>=', now())->latest()->take(4)->get();
        $wellness = \App\Models\LinkUpEvent::whereIn('category_id', $wellnessCategoryIds)->where('start_time', '>=', now())->latest()->take(4)->get();

        foreach ($users as $user) {
            $latestEvents = collect();

            foreach ($user->followingOrganizers as $following) {
                $organizer = $following->organizer;
                if ($organizer) {
                    // Get the latest event from this organizer
                    $latestEvent = \App\Models\LinkUpEvent::where('organizer_id', $organizer->id)->latest()->first();
                    if ($latestEvent) {
                        $latestEvent->setRelation('organizer', $organizer);
                        $latestEvents->push($latestEvent);
                    }
                }
            }

            // Sort by newest and take top 4 to fit in the 2-column email layout
            $latestEvents = $latestEvents->sortByDesc('created_at')->take(4)->values();

            if ($latestEvents->isEmpty() && $cookouts->isEmpty() && $wellness->isEmpty()) continue;

            Log::info("Dispatching event mail job for user #{$user->id} with {$latestEvents->count()} events, {$cookouts->count()} cookouts, and {$wellness->count()} wellness events.");
            SendEventMailJob::dispatch($user, $latestEvents, $cookouts, $wellness);
        }
    }
}
