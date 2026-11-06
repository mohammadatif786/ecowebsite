<?php

namespace App\Actions\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Real numbers for E-Wallet → Coins.
 *  - Purchases: Stripe coin purchases, recorded in `subscriptions` with type = 'coin' (quantity = coins, stripe_price = USD).
 *  - Held: coins sitting in members' accounts right now (users.coins).
 *  - Spent: coins sent as gifts (Vibes/Reels gifts in gift_coins, Live gifts in gift_purchases).
 */
class GetAdminCoinsDataAction
{
    /** Default price of one coin when there are no purchases yet (100 coins = $1 pack). */
    private const DEFAULT_COIN_USD = 0.01;

    public function execute(): array
    {
        $purchases = fn () => DB::table('subscriptions')->where('subscriptions.type', 'coin');

        $coinsBought = (int) $purchases()->sum('quantity');
        $usdPaid = (float) $purchases()->sum(DB::raw('CAST(stripe_price AS DECIMAL(12,2))'));
        $coinUsd = $coinsBought > 0 ? $usdPaid / $coinsBought : self::DEFAULT_COIN_USD;

        $held = (int) DB::table('users')->sum('coins');
        $holders = (int) DB::table('users')->where('coins', '>', 0)->count();

        $spentGifts = $this->sumIfTable('gift_coins', 'coins');
        $spentLive = $this->sumIfTable('gift_purchases', 'total_coins');

        $today = now()->startOfDay();
        $since30 = now()->subDays(30);

        return [
            'summary' => [
                'coinsBought' => $coinsBought,
                'usdPaid' => round($usdPaid, 2),
                'buyers' => (int) $purchases()->distinct()->count('user_id'),
                'purchases' => (int) $purchases()->count(),
                'boughtToday' => (int) $purchases()->where('created_at', '>=', $today)->sum('quantity'),
                'usdToday' => round((float) $purchases()->where('created_at', '>=', $today)->sum(DB::raw('CAST(stripe_price AS DECIMAL(12,2))')), 2),
                'bought30d' => (int) $purchases()->where('created_at', '>=', $since30)->sum('quantity'),
                'usd30d' => round((float) $purchases()->where('created_at', '>=', $since30)->sum(DB::raw('CAST(stripe_price AS DECIMAL(12,2))')), 2),
                'held' => $held,
                'heldUsd' => round($held * $coinUsd, 2),
                'holders' => $holders,
                'spentGifts' => $spentGifts,
                'spentLive' => $spentLive,
                'spent' => $spentGifts + $spentLive,
                'coinUsd' => round($coinUsd, 4),
            ],
            'byCountry' => $this->byCountry($coinUsd),
            'recentPurchases' => $purchases()
                ->leftJoin('users', 'users.id', '=', 'subscriptions.user_id')
                ->orderByDesc('subscriptions.id')
                ->limit(50)
                ->get(['subscriptions.id', 'subscriptions.user_id', 'users.name', 'users.email', 'users.country',
                    'subscriptions.quantity', 'subscriptions.stripe_price', 'subscriptions.stripe_id', 'subscriptions.created_at'])
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'userId' => $r->user_id,
                    'name' => $r->name ?? 'Deleted user #' . $r->user_id,
                    'email' => $r->email,
                    'country' => $r->country ?: 'Unknown',
                    'coins' => (int) $r->quantity,
                    'usd' => round((float) $r->stripe_price, 2),
                    'ref' => $r->stripe_id ? '…' . substr($r->stripe_id, -8) : null,
                    'date' => $r->created_at,
                ])->values(),
            'topHolders' => DB::table('users')->where('coins', '>', 0)->orderByDesc('coins')->limit(20)
                ->get(['id', 'name', 'email', 'country', 'coins', 'last_login_at'])
                ->map(fn ($u) => [
                    'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'country' => $u->country ?: 'Unknown',
                    'coins' => (int) $u->coins, 'usd' => round($u->coins * $coinUsd, 2), 'lastLogin' => $u->last_login_at,
                ])->values(),
            'dormant' => $this->dormant($coinUsd),
        ];
    }

    private function byCountry(float $coinUsd): array
    {
        $bought = DB::table('subscriptions')->where('subscriptions.type', 'coin')
            ->join('users', 'users.id', '=', 'subscriptions.user_id')
            ->groupBy('users.country')
            ->get(['users.country', DB::raw('sum(subscriptions.quantity) as coins'), DB::raw('sum(CAST(subscriptions.stripe_price AS DECIMAL(12,2))) as usd'), DB::raw('count(distinct subscriptions.user_id) as buyers')])
            ->keyBy(fn ($r) => $r->country ?: 'Unknown');

        $held = DB::table('users')->where('coins', '>', 0)->groupBy('country')
            ->get(['country', DB::raw('sum(coins) as coins'), DB::raw('count(*) as holders')])
            ->keyBy(fn ($r) => $r->country ?: 'Unknown');

        return collect($bought->keys())->merge($held->keys())->unique()->map(fn ($c) => [
            'country' => $c,
            'bought' => (int) ($bought[$c]->coins ?? 0),
            'usd' => round((float) ($bought[$c]->usd ?? 0), 2),
            'buyers' => (int) ($bought[$c]->buyers ?? 0),
            'held' => (int) ($held[$c]->coins ?? 0),
            'heldUsd' => round(((int) ($held[$c]->coins ?? 0)) * $coinUsd, 2),
            'holders' => (int) ($held[$c]->holders ?? 0),
        ])->sortByDesc('usd')->values()->all();
    }

    /** Coins held by members who haven't signed in for a while (real last_login_at). */
    private function dormant(float $coinUsd): array
    {
        $hasLogin = Schema::hasColumn('users', 'last_login_at');

        return collect([3, 6, 12, 24])->map(function ($months) use ($hasLogin, $coinUsd) {
            $q = DB::table('users')->where('coins', '>', 0);
            if ($hasLogin) {
                $q->where(fn ($w) => $w->whereNull('last_login_at')->orWhere('last_login_at', '<', now()->subMonths($months)));
            }
            $coins = (int) (clone $q)->sum('coins');

            return ['months' => $months, 'coins' => $coins, 'usd' => round($coins * $coinUsd, 2), 'members' => (int) $q->count()];
        })->all();
    }

    private function sumIfTable(string $table, string $column): int
    {
        return Schema::hasTable($table) ? (int) DB::table($table)->sum($column) : 0;
    }
}
