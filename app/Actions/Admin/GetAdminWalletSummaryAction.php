<?php

namespace App\Actions\Admin;

use App\Models\User;
use App\Models\WalletEvent;
use App\Repositories\Admin\AdminOverviewRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Real numbers for E-Wallet → Mission Control, straight from the wallet tables.
 * Nothing here is estimated: no reserve multipliers, no assumed fee splits.
 */
class GetAdminWalletSummaryAction
{
    private const CREDIT_PROCESSORS = ['deposit', 'live deposit', 'conversion_credit'];

    public function execute(): array
    {
        $types = AdminOverviewRepository::USER_TYPES;
        $balances = 'balances';

        $memberBalances = DB::table($balances)
            ->join('users', 'users.id', '=', "{$balances}.payable_id")
            ->whereIn("{$balances}.payable_type", $types)
            ->where("{$balances}.currency", 'USD');

        $orphans = DB::table($balances)
            ->whereIn("{$balances}.payable_type", $types)
            ->whereNotExists(fn (Builder $q) => $q->select(DB::raw(1))->from('users')->whereColumn('users.id', "{$balances}.payable_id"));

        $since30 = now()->subDays(30);
        $tx = fn () => DB::table('transactions')->where('status', 'success');

        $loads = fn ($q) => $q->where('processor_id', 'deposit')->whereIn('to_type', $types)->where('meta->type', 'recharge');
        $p2p = fn ($q) => $q->where('processor_id', 'transfer')->whereIn('from_type', $types)->whereIn('to_type', $types);
        $payments = fn ($q) => $q->where('processor_id', 'charge')->whereIn('from_type', $types);

        $bw = fn () => DB::table('bank_withdrawals');

        return [
            'balances' => round((float) (clone $memberBalances)->sum("{$balances}.value"), 2),
            'walletUsers' => (int) (clone $memberBalances)->distinct()->count("{$balances}.payable_id"),
            'fundedWallets' => (int) (clone $memberBalances)->where("{$balances}.value", '>', 0)->distinct()->count("{$balances}.payable_id"),
            'orphanWallets' => (int) (clone $orphans)->count(),
            'orphanBalance' => round((float) (clone $orphans)->sum("{$balances}.value"), 2),

            'loads30d' => round((float) $loads($tx())->where('created_at', '>=', $since30)->sum('amount'), 2),
            'loadsAll' => round((float) $loads($tx())->sum('amount'), 2),
            'p2p30d' => round((float) $p2p($tx())->where('created_at', '>=', $since30)->sum('amount'), 2),
            'p2pAll' => round((float) $p2p($tx())->sum('amount'), 2),
            'payments30d' => round((float) $payments($tx())->where('created_at', '>=', $since30)->sum('amount'), 2),
            'paymentsAll' => round((float) $payments($tx())->sum('amount'), 2),
            'volume30d' => round((float) $tx()->where('created_at', '>=', $since30)->sum('amount'), 2),
            'txCount30d' => (int) $tx()->where('created_at', '>=', $since30)->count(),
            'walletFeesAll' => round((float) $tx()->sum('commission'), 2),

            'cashoutsCompleted' => round((float) $bw()->where('status', 'completed')->sum('amount'), 2),
            'cashoutFees' => round((float) $bw()->where('status', 'completed')->sum('fee_amount'), 2),
            'pendingCashouts' => (int) $bw()->whereIn('status', ['pending', 'processing'])->count(),
            'pendingCashoutAmount' => round((float) $bw()->whereIn('status', ['pending', 'processing'])->sum('amount'), 2),

            'activeWallets24h' => $this->activeWallets24h($types),
            'largestMovement30d' => round((float) $tx()->where('created_at', '>=', $since30)->max('amount'), 2),
            'kycPending' => (int) DB::table('user_wallet_kycs')->where('status', 'pending')->count(),
            'failedToday' => $this->walletEvents(fn ($q) => $q->where('status', 'failed')->where('created_at', '>=', now()->startOfDay())),
            'wrongPins24h' => $this->walletEvents(fn ($q) => $q->where('status', 'failed')->where('message', 'like', '%PIN%')->where('created_at', '>=', now()->subDay())),
        ];
    }

    private function activeWallets24h(array $types): int
    {
        $since = now()->subDay();
        $from = DB::table('transactions')->where('created_at', '>=', $since)->whereIn('from_type', $types)->select('from_id as uid');
        $to = DB::table('transactions')->where('created_at', '>=', $since)->whereIn('to_type', $types)->select('to_id as uid');

        return (int) DB::query()->fromSub($from->union($to), 'u')->count();
    }

    private function walletEvents(callable $scope): int
    {
        try {
            return Schema::hasTable('wallet_events') ? (int) $scope(WalletEvent::query())->count() : 0;
        } catch (Throwable) {
            return 0;
        }
    }
}
