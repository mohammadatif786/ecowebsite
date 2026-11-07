<?php

use App\Actions\Admin\GetAdminCoinsDataAction;
use App\Actions\Admin\GetAdminWalletSummaryAction;
use App\Models\User;
use App\Repositories\Admin\AdminOverviewRepository;
use Illuminate\Support\Facades\DB;

// Harness only: the wallet package's Balance model (the real app has it installed).
if (! class_exists(\O21\LaravelWallet\Models\Balance::class)) {
    eval('namespace O21\\LaravelWallet\\Models; class Balance extends \\Illuminate\\Database\\Eloquent\\Model { protected $table = "balances"; }');
}

function figUser(array $extra = []): User
{
    static $n = 0; $n++;
    $u = User::create(['name' => "Fig $n", 'email' => "fig$n@example.test", 'password' => 'secret-pass']);
    DB::table('users')->where('id', $u->id)->update($extra + ['country' => 'PK']);
    return $u->fresh();
}

function balanceRow(string $type, int $id, float $value): void
{
    DB::table('balances')->insert(['payable_type' => $type, 'payable_id' => $id, 'currency' => 'USD', 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
}

function txRow(array $row): void
{
    DB::table('transactions')->insert($row + ['uuid' => (string) \Illuminate\Support\Str::uuid(), 'currency' => 'USD', 'status' => 'success', 'commission' => 0, 'received' => $row['amount'], 'created_at' => now(), 'updated_at' => now()]);
}

it('counts wallets stored with the short "user" type and leaves out deleted members', function () {
    $new = figUser();                     // morph-map row
    $old = figUser(['country' => 'Jamaica']); // legacy class-name row
    balanceRow('user', $new->id, 100);
    balanceRow(User::class, $old->id, 40);
    balanceRow(User::class, 99999, 138010.31); // wallet of a deleted member

    $repo = new AdminOverviewRepository();
    expect($repo->getWalletUsers()->pluck('id')->sort()->values()->all())->toBe(collect([$new->id, $old->id])->sort()->values()->all())
        ->and($repo->countWalletUsers())->toBe(2)
        ->and((float) $repo->getCurrencyExposure()->first()->total)->toBe(140.0)
        ->and($repo->getWalletBalancesByCountry()->pluck('storedValue', 'country')->map(fn ($v) => (float) $v)->sortKeys()->all())->toBe(['Jamaica' => 40.0, 'PK' => 100.0]);

    $s = app(GetAdminWalletSummaryAction::class)->execute();
    expect($s['balances'])->toEqual(140.0)->and($s['walletUsers'])->toBe(2)->and($s['fundedWallets'])->toBe(2)
        ->and($s['orphanWallets'])->toBe(1)->and($s['orphanBalance'])->toEqual(138010.31);
});

it('reports loads, transfers and active wallets from the ledger, member side first', function () {
    $a = figUser(); $b = figUser();
    txRow(['from_type' => 'custodian', 'from_id' => 1, 'to_type' => 'user', 'to_id' => $a->id, 'amount' => 100, 'processor_id' => 'deposit', 'meta' => json_encode(['type' => 'recharge'])]);
    txRow(['from_type' => 'user', 'from_id' => $a->id, 'to_type' => 'user', 'to_id' => $b->id, 'amount' => 25, 'processor_id' => 'transfer', 'meta' => null]);
    txRow(['from_type' => 'user', 'from_id' => $a->id, 'to_type' => 'custodian', 'to_id' => 2, 'amount' => 10, 'processor_id' => 'transfer', 'meta' => json_encode(['type' => 'bank_withdrawal'])]);

    $s = app(GetAdminWalletSummaryAction::class)->execute();
    expect($s['loadsAll'])->toEqual(100.0)->and($s['p2pAll'])->toEqual(25.0)->and($s['activeWallets24h'])->toBe(2)->and($s['txCount30d'])->toBe(3);

    // Country volume counts the deposit for the member who received it, not custodian #1.
    $byCountry = (new AdminOverviewRepository())->getWalletStats()->pluck('gtv', 'country')->map(fn ($v) => (float) $v)->all();
    expect($byCountry)->toBe(['PK' => 135.0]);
});

it('reports real coin purchases, holdings, gifts and dormant coins', function () {
    $buyer = figUser(['coins' => 1200, 'last_login_at' => now()->subMonths(13)]);
    $other = figUser(['coins' => 50, 'country' => 'Jamaica', 'last_login_at' => now()]);
    DB::table('subscriptions')->insert([
        ['user_id' => $buyer->id, 'type' => 'coin', 'stripe_id' => 'pi_abc12345678', 'stripe_price' => '10', 'quantity' => 1200, 'stripe_status' => 'complete', 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $buyer->id, 'type' => 'wallet', 'stripe_id' => 'pi_wallet', 'stripe_price' => '100', 'quantity' => 1, 'stripe_status' => 'complete', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('gift_purchases')->insert(['user_id' => $other->id, 'total_coins' => 30, 'created_at' => now(), 'updated_at' => now()]);

    $d = app(GetAdminCoinsDataAction::class)->execute();
    expect($d['summary'])->toMatchArray([
        'coinsBought' => 1200, 'usdPaid' => 10.0, 'buyers' => 1, 'purchases' => 1, 'boughtToday' => 1200,
        'held' => 1250, 'holders' => 2, 'spentLive' => 30, 'coinUsd' => 0.0083,
    ]);
    expect($d['recentPurchases'][0])->toMatchArray(['name' => $buyer->name, 'coins' => 1200, 'usd' => 10.0, 'ref' => '…12345678']);
    expect(collect($d['byCountry'])->firstWhere('country', 'PK'))->toMatchArray(['bought' => 1200, 'usd' => 10.0, 'held' => 1200]);
    expect(collect($d['dormant'])->firstWhere('months', 12))->toMatchArray(['coins' => 1200, 'members' => 1]);
    expect($d['topHolders'][0]['coins'])->toBe(1200);
});
