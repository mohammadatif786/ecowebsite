<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopularityTestSeeder extends Seeder
{
    public function run(): void
    {
        $user365 = User::find(365);
        $user340 = User::find(340);

        if (!$user365 || !$user340) {
            $this->command->error('Users 365 or 340 not found. Please check user IDs.');
            return;
        }

        $this->command->info('Adding dummy popularity data for users 365 and 340...');

        // Create some dummy users for interactions
        $dummyUsers = User::whereNotIn('id', [365, 340])
            ->where('status', true)
            ->take(10)
            ->get();

        if ($dummyUsers->isEmpty()) {
            $this->command->warn('No other users found for interactions. Creating minimal test data only.');
        }

        // ===== USER 365 DATA =====
        $this->command->info('Adding data for User 365...');

        // Profile views (100 views)
        foreach ($dummyUsers as $i => $viewer) {
            DB::table('user_profile_views')->updateOrInsert(
                [
                    'viewer_id' => $viewer->id,
                    'viewed_user_id' => 365,
                    'viewed_date' => now()->subDays($i % 30)->toDateString(),
                ],
                [
                    'viewed_at' => now()->subDays($i % 30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Likes received (50 likes)
        foreach ($dummyUsers as $i => $liker) {
            UserMatch::updateOrCreate(
                [
                    'user_id' => $liker->id,
                    'target_user_id' => 365,
                ],
                [
                    'status' => 'like',
                ]
            );
        }

        // Messages received (30 messages)
        foreach ($dummyUsers->take(8) as $i => $sender) {
            Message::create([
                'from_user_id' => $sender->id,
                'to_user_id' => 365,
                'content' => 'Test message ' . ($i + 1),
                'replied_to' => null,
            ]);
        }

        // Matches (20 mutual matches)
        foreach ($dummyUsers->take(6) as $i => $matchUser) {
            UserMatch::updateOrCreate(
                [
                    'user_id' => 365,
                    'target_user_id' => $matchUser->id,
                ],
                [
                    'status' => 'like',
                ]
            );
            UserMatch::updateOrCreate(
                [
                    'user_id' => $matchUser->id,
                    'target_user_id' => 365,
                ],
                [
                    'status' => 'like',
                ]
            );
        }

        // Update last_active for activity points
        $user365->last_active = now()->subDays(2);
        $user365->save();

        // ===== USER 340 DATA =====
        $this->command->info('Adding data for User 340...');

        // Profile views (50 views)
        foreach ($dummyUsers->take(8) as $i => $viewer) {
            DB::table('user_profile_views')->updateOrInsert(
                [
                    'viewer_id' => $viewer->id,
                    'viewed_user_id' => 340,
                    'viewed_date' => now()->subDays($i % 15)->toDateString(),
                ],
                [
                    'viewed_at' => now()->subDays($i % 15),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Likes received (25 likes)
        foreach ($dummyUsers->take(5) as $i => $liker) {
            UserMatch::updateOrCreate(
                [
                    'user_id' => $liker->id,
                    'target_user_id' => 340,
                ],
                [
                    'status' => 'like',
                ]
            );
        }

        // Messages received (15 messages)
        foreach ($dummyUsers->take(4) as $i => $sender) {
            Message::create([
                'from_user_id' => $sender->id,
                'to_user_id' => 340,
                'content' => 'Test message ' . ($i + 1),
                'replied_to' => null,
            ]);
        }

        // Matches (10 mutual matches)
        foreach ($dummyUsers->take(3) as $i => $matchUser) {
            UserMatch::updateOrCreate(
                [
                    'user_id' => 340,
                    'target_user_id' => $matchUser->id,
                ],
                [
                    'status' => 'like',
                ]
            );
            UserMatch::updateOrCreate(
                [
                    'user_id' => $matchUser->id,
                    'target_user_id' => 340,
                ],
                [
                    'status' => 'like',
                ]
            );
        }

        // Update last_active for activity points (more recent)
        $user340->last_active = now()->subHours(12);
        $user340->save();

        $this->command->info('Dummy data added successfully!');
        $this->command->info('User 365: 100 views, 50 likes, 30 messages, 20 matches, 2-day activity');
        $this->command->info('User 340: 50 views, 25 likes, 15 messages, 10 matches, 12-hour activity');
        $this->command->info('');
        $this->command->info('Now run: php artisan tinker');
        $this->command->info('Then run: app(App\Services\PopularityScoreService::class)->updateUserScore(App\Models\User::find(365))');
        $this->command->info('And: app(App\Services\PopularityScoreService::class)->updateUserScore(App\Models\User::find(340))');
    }
}
