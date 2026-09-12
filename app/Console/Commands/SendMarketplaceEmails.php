<?php

namespace App\Console\Commands;

use App\Jobs\SendMarketplaceEmailJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class SendMarketplaceEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-marketplace-emails {--limit= : Limit number of emails to send in one run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send LinkUp Marketplace promotional email to eligible users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $limit = $this->option('limit');
            $limit = is_numeric($limit) ? (int) $limit : null;

            $query = User::query()
                ->whereNotNull('email')
                ->where('promotion_notification', true);

            if ($limit && $limit > 0) {
                $query->limit($limit);
            }

            $users = $query->get();

            foreach ($users as $user) {
                $topPicks = $this->getTopPicksForUser($user->id);
                $popularSellers = $this->getPopularSellers();
                SendMarketplaceEmailJob::dispatch($user, $topPicks, $popularSellers);
            }

            $this->info('Queued marketplace emails for ' . $users->count() . ' users.');
        } catch (\Exception $e) {
            $this->error('Error sending marketplace emails: ' . $e->getMessage());
        }
    }

    /**
     * Get top 4 latest products based on user's order history
     */
    protected function getTopPicksForUser($userId)
    {
        // Get product categories the user frequently orders
        $categoryIds = DB::table('order_items as oi')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('orders as o', 'oi.order_id', '=', 'o.id')
            ->where('o.user_id', $userId)
            ->where('o.status', '!=', 'cancelled')
            ->where('p.status', true)
            ->groupBy('p.product_category_id')
            ->orderByRaw('COUNT(oi.qty) DESC')
            ->limit(3)
            ->pluck('p.product_category_id');

        // Get latest products from those categories
        $products = Product::with(['user'])
            ->whereIn('product_category_id', $categoryIds)
            ->where('status', true)
            ->where('qty', '>', 0)
            ->latest()
            ->take(4)
            ->get(['id', 'name', 'price', 'cover_image', 'user_id', 'product_category_id']);

        // Fallback to latest products if no order history
        if ($products->isEmpty()) {
            $products = Product::with(['user'])
                ->where('status', true)
                ->where('qty', '>', 0)
                ->latest()
                ->take(4)
                ->get(['id', 'name', 'price', 'cover_image', 'user_id', 'product_category_id']);
        }

        return $products;
    }

    /**
     * Get popular sellers based on follower count
     */
    protected function getPopularSellers()
    {
        return DB::table('favorite_sellers as fs')
            ->join('users as u', 'fs.seller_id', '=', 'u.id')
            ->join('users as seller', 'fs.seller_id', '=', 'seller.id')
            ->select([
                'fs.seller_id',
                'seller.name',
                'seller.avatar',
                DB::raw('COUNT(fs.user_id) as follower_count')
            ])
            ->groupBy('fs.seller_id', 'seller.name', 'seller.avatar')
            ->orderBy('follower_count', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($seller) {
                // Get seller's main category (most common product category)
                $mainCategory = DB::table('products as p')
                    ->join('product_categories as pc', 'p.product_category_id', '=', 'pc.id')
                    ->where('p.user_id', $seller->seller_id)
                    ->where('p.status', true)
                    ->groupBy('p.product_category_id', 'pc.name')
                    ->orderByRaw('COUNT(p.id) DESC')
                    ->limit(1)
                    ->pluck('pc.name')
                    ->first();

                $seller->main_category = $mainCategory ?: 'Marketplace';
                return $seller;
            });
    }
}
