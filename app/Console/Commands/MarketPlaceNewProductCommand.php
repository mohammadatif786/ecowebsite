<?php

namespace App\Console\Commands;

use App\Jobs\MarketPlaceNewProductJob;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarketPlaceNewProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-marketplace-new-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails about new marketplace products to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all users who have favorite sellers
        $users = \App\Models\User::whereHas('favoriteSellers')->with([
            'favoriteSellers.products' => function ($query) {
                $query->latest()->limit(1);
            }
        ])->get();

        foreach ($users as $user) {
            // Gather the 1 latest product across up to 3 favorite sellers
            $latestProducts = $user->favoriteSellers
                ->flatMap(fn($seller) => $seller->products)
                ->sortByDesc('created_at')
                ->take(3)
                ->values();

            if ($latestProducts->isEmpty()) continue;

            Log::info("Dispatching job for user #{$user->id} with {$latestProducts->count()} products.");
            MarketPlaceNewProductJob::dispatch($user, $latestProducts);
        }
    }
}

