<?php

namespace Database\Seeders;

use App\Models\LinkupLiveGift;
use Illuminate\Database\Seeder;

class LinkupLiveGiftSeeder extends Seeder
{
    /** Seed the initial active catalogue; re-running never duplicates gifts. */
    public function run(): void
    {
        foreach ([
            ['name' => 'Applause', 'emoji' => '👏', 'coins' => 5],
            ['name' => 'Shoutout', 'emoji' => '📣', 'coins' => 25],
            ['name' => 'Mic Drop', 'emoji' => '🎤', 'coins' => 50],
            ['name' => 'Spotlight', 'emoji' => '💡', 'coins' => 100],
            ['name' => 'Riddim Section', 'emoji' => '🥁', 'coins' => 200],
            ['name' => 'Soca Star', 'emoji' => '🌟', 'coins' => 350],
            ['name' => 'Confetti Drop', 'emoji' => '🎉', 'coins' => 500],
            ['name' => 'Fyah Pon Stage', 'emoji' => '🔥', 'coins' => 1000],
            ['name' => 'Carnival Crown', 'emoji' => '👑', 'coins' => 2000],
        ] as $gift) {
            LinkupLiveGift::query()->updateOrCreate(
                ['name' => $gift['name']],
                [...$gift, 'active' => true]
            );
        }
    }
}
