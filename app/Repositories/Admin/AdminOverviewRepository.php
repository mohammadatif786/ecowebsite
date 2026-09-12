<?php

namespace App\Repositories\Admin;

use App\Models\Advertisement;
use App\Models\Asue;
use App\Models\EventOrganizer;
use App\Models\GiftPurchase;
use App\Models\Merchants;
use App\Models\Order;
use App\Models\Payout;
use App\Models\SubscriptionPlan;
use App\Models\TicketSale;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class AdminOverviewRepository
{
    public function getCountryNames(): array
    {
        return User::whereNotNull('country')->distinct()->pluck('country')->toArray();
    }

    public function getUserStats(): \Illuminate\Support\Collection
    {
        return User::select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();
    }

    public function getMerchantStats(): \Illuminate\Support\Collection
    {
        return Merchants::select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();
    }

    public function getOrganizerStats(): \Illuminate\Support\Collection
    {
        return EventOrganizer::join('users', 'event_organizers.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('count(*) as count'))
            ->whereNotNull('users.country')
            ->groupBy('users.country')
            ->get();
    }

    public function getTicketStats(): \Illuminate\Support\Collection
    {
        return TicketSale::join('users', 'ticket_sales.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(total) as gtv'))
            ->whereNotNull('users.country')
            ->groupBy('users.country')
            ->get();
    }

    public function getSubscriptionStats(): \Illuminate\Support\Collection
    {
        return UserSubscription::join('users', 'user_subscriptions.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(amount) as gtv'))
            ->whereNotNull('users.country')
            ->groupBy('users.country')
            ->get();
    }

    public function getMarketplaceStats(): \Illuminate\Support\Collection
    {
        return Order::select('country', DB::raw('sum(total) as gtv'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();
    }

    public function getLiveStats(): \Illuminate\Support\Collection
    {
        return GiftPurchase::join('users', 'gift_purchases.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(total_coins) as gtv'))
            ->whereNotNull('users.country')
            ->groupBy('users.country')
            ->get();
    }

    public function getWalletStats(): \Illuminate\Support\Collection
    {
        return Transaction::join('users', 'transactions.from_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(amount) as gtv'))
            ->whereNotNull('users.country')
            ->groupBy('users.country')
            ->get();
    }

    public function getAdStats(): \Illuminate\Support\Collection
    {
        return Advertisement::select('country', DB::raw('sum(cost) as gtv'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();
    }

    public function getWellnessStats(): \Illuminate\Support\Collection
    {
        return TicketSale::join('users', 'ticket_sales.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(wellness_total) as gtv'))
            ->whereNotNull('users.country')
            ->where('wellness_total', '>', 0)
            ->groupBy('users.country')
            ->get();
    }

    public function getCookoutStats(): \Illuminate\Support\Collection
    {
        return TicketSale::join('users', 'ticket_sales.user_id', '=', 'users.id')
            ->select('users.country', DB::raw('sum(cookout_total) as gtv'))
            ->whereNotNull('users.country')
            ->where('cookout_total', '>', 0)
            ->groupBy('users.country')
            ->get();
    }

    public function getRecentUsers(int $limit = 100): \Illuminate\Support\Collection
    {
        return User::select('id', 'name', 'country', 'birthday', 'gender', 'status', 'kyc_status', 'created_at')
            ->with(['subscribedPlans' => function($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getSubscriptionPlans(): \Illuminate\Support\Collection
    {
        return SubscriptionPlan::all();
    }

    public function getUserGrowthStats(int $months = 6): \Illuminate\Support\Collection
    {
        return User::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as count'),
            DB::raw("sum(case when status = 'Active' then 1 else 0 end) as active_count")
        )
        ->where('created_at', '>=', now()->subMonths($months))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();
    }

    public function getRecentEvents(int $limit = 100): \Illuminate\Support\Collection
    {
        return \App\Models\LinkUpEvent::select(
            'id', 'title', 'organizer_name', 'email', 'country', 'city', 'category_id', 'type', 'status', 'created_at', 'organizer_id'
        )
        ->with(['eventDetails', 'category', 'organizer'])
        ->withSum('ticketSales as ticketRevenue', 'total')
        ->withCount('event_audience as ticketsSold')
        ->withSum('tickets as ticketsTotal', 'quantity')
        ->latest()
        ->limit($limit)
        ->get();
    }

    public function getEventCategories(): \Illuminate\Support\Collection
    {
        return \App\Models\EventCategory::withCount('linkupEvents')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getTicketSalesRecords(int $limit = 100): \Illuminate\Support\Collection
    {
        return \App\Models\TicketSale::with(['user', 'event', 'ticket'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getWalletMovements(int $limit = 300): \Illuminate\Support\Collection
    {
        return Transaction::with(['from', 'to'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getBankWithdrawals(int $limit = 300): \Illuminate\Support\Collection
    {
        return \App\Models\BankWithdrawal::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Users who have an initialized wallet balance row (o21/laravel-wallet creates
     * one lazily the first time a user's wallet is touched), i.e. real "wallet users".
     */
    public function getWalletUsers(int $limit = 200): \Illuminate\Support\Collection
    {
        $balanceTable = (new \O21\LaravelWallet\Models\Balance())->getTable();

        return User::select('id', 'name', 'country', 'status', 'stripe_id', 'created_at')
            ->whereIn('id', function ($query) use ($balanceTable) {
                $query->select('payable_id')
                    ->from($balanceTable)
                    ->where('payable_type', User::class);
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function countWalletUsers(): int
    {
        $balanceTable = (new \O21\LaravelWallet\Models\Balance())->getTable();

        return (int) DB::table($balanceTable)->where('payable_type', User::class)->distinct()->count('payable_id');
    }

    public function getWalletBalancesByUserIds(array $userIds): \Illuminate\Support\Collection
    {
        return \O21\LaravelWallet\Models\Balance::where('payable_type', User::class)
            ->whereIn('payable_id', $userIds)
            ->get(['payable_id', 'value']);
    }

    /**
     * Real stored value + wallet-user count per country. Queried via the query builder
     * (not the Balance Eloquent model) so SUM() aggregates the raw decimal column
     * instead of going through the model's Numeric-object cast.
     */
    public function getWalletBalancesByCountry(): \Illuminate\Support\Collection
    {
        $balanceTable = (new \O21\LaravelWallet\Models\Balance())->getTable();

        return DB::table($balanceTable)
            ->join('users', function ($join) use ($balanceTable) {
                $join->on("{$balanceTable}.payable_id", '=', 'users.id')
                    ->where("{$balanceTable}.payable_type", '=', User::class);
            })
            ->whereNotNull('users.country')
            ->select('users.country', DB::raw('sum(' . $balanceTable . '.value) as storedValue'), DB::raw('count(distinct users.id) as walletUsers'))
            ->groupBy('users.country')
            ->get();
    }

    /**
     * Real balance total grouped by currency (balances.currency), for the
     * "Currency Exposure" breakdown.
     */
    public function getCurrencyExposure(): \Illuminate\Support\Collection
    {
        $balanceTable = (new \O21\LaravelWallet\Models\Balance())->getTable();

        return DB::table($balanceTable)
            ->where('payable_type', User::class)
            ->select('currency', DB::raw('sum(value) as total'))
            ->groupBy('currency')
            ->orderByDesc('total')
            ->get();
    }

    public function getSettlements(int $limit = 100): \Illuminate\Support\Collection
    {
        return Payout::with(['organizer.user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getSettlementStatusCounts(): \Illuminate\Support\Collection
    {
        return Payout::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    public function getTodaySettlementTotals(): object
    {
        return Payout::whereDate('created_at', now()->toDateString())
            ->selectRaw('coalesce(sum(amount), 0) as grossToday, coalesce(sum(fee_amount), 0) as feeToday')
            ->first();
    }

    public function getAsueCircles(int $limit = 200): \Illuminate\Support\Collection
    {
        return Asue::withCount('invitedUsers')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Real per-hand platform fees actually collected, keyed by Asue circle id.
     * AsueService::acceptPayout() stores `platform_fee`/`asue_id` in the deposit
     * transaction's meta JSON — this is the true historical record, rather than a
     * theoretical fee-rate * turns-completed estimate.
     */
    public function getAsuePayoutFeesByCircle(): array
    {
        $fees = [];

        Transaction::where('meta->type', 'asue_payout')->get(['meta'])->each(function ($tx) use (&$fees) {
            $asueId = $tx->meta['asue_id'] ?? null;
            if ($asueId === null) {
                return;
            }
            $fees[$asueId] = ($fees[$asueId] ?? 0) + (float)($tx->meta['platform_fee'] ?? 0);
        });

        return $fees;
    }

    public function getAsuePayoutTransactions(int $limit = 100): \Illuminate\Support\Collection
    {
        return Transaction::where('meta->type', 'asue_payout')
            ->with('to')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getLatestWalletKycByUserIds(array $userIds): \Illuminate\Support\Collection
    {
        return \App\Models\UserWalletKyc::whereIn('user_id', $userIds)
            ->latest()
            ->get(['user_id', 'status'])
            ->unique('user_id');
    }

    /**
     * Counts users by their most recent KYC submission status (not raw submission
     * rows, so a user who resubmitted after a rejection is only counted once).
     */
    public function getKycStatusCountsByUser(): \Illuminate\Support\Collection
    {
        return \App\Models\UserWalletKyc::orderByDesc('created_at')
            ->get(['user_id', 'status'])
            ->unique('user_id')
            ->countBy('status');
    }

    public function getDefaultBankAccountsByUserIds(array $userIds): \Illuminate\Support\Collection
    {
        return \App\Models\UserBankAccount::whereIn('user_id', $userIds)
            ->where('is_default', true)
            ->get(['user_id', 'bank_name', 'account_number']);
    }

    public function getSponsors(): \Illuminate\Support\Collection
    {
        return \App\Models\Sponsor::with('event')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getCoupons(): \Illuminate\Support\Collection
    {
        return \App\Models\Coupon::with('event')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getPendingCancellationRequests(): \Illuminate\Support\Collection
    {
        return \App\Models\TicketSale::where('ticket_status', 'cancelled')
            ->with(['event', 'user'])
            ->whereDoesntHave('cancellationRequest')
            ->get();
    }

    public function getCancellationOrders(): \Illuminate\Support\Collection
    {
        return \App\Models\CancellationRequests::with(['user', 'event'])->get();
    }

    public function getOrganizers(): \Illuminate\Support\Collection
    {
        return \App\Models\OrganizerProfile::with([
            'user' => function($query) {
                $query->withCount('linkupEvents')
                      ->withCount('ticketSales')
                      ->withSum('ticketSales', 'total');
            },
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])
        ->whereHas('user', function ($query) {
            $query->where('type', 'organizer');
        })
        ->orderBy('created_at', 'DESC')
        ->get();
    }

    public function getScanners(): \Illuminate\Support\Collection
    {
        return \App\Models\ScanSignUser::with('user')->get();
    }

    public function getSellerCashOutRequests(int $limit = 100): \Illuminate\Support\Collection
    {
        return \App\Models\SellerCashOutRequest::with(['user', 'bankAccount'])->latest()->limit($limit)->get();
    }

    public function getWithdrawRequests(int $limit = 100): \Illuminate\Support\Collection
    {
        return \App\Models\WithdrawRequest::with('user')->latest()->limit($limit)->get();
    }

    public function getPayoutActivityLogs(int $limit = 100): \Illuminate\Support\Collection
    {
        return \App\Models\Frontend\ActivityLog::with('user')
            ->where('title', 'like', '%payout%')
            ->orWhere('title', 'like', '%withdrawal%')
            ->orWhere('title', 'like', '%cash-out%')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
