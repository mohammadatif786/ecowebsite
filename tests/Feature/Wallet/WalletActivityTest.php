<?php

use App\Domain\Kyc\Actions\UpdateKycReviewStatusAction;
use App\Models\User;
use App\Models\UserWalletKyc;
use App\Models\WalletEvent;
use Illuminate\Support\Facades\Hash;

foreach ([__DIR__ . '/harness_transfer.php', __DIR__ . '/../Vibes/harness_wallet.php'] as $stub) {
    if (is_file($stub)) {
        require_once $stub;
    }
}

function activityUser(array $extra = []): User
{
    static $n = 0; $n++;
    $u = User::create($extra + ['name' => "Act $n", 'email' => "act$n@example.test", 'password' => 'secret-pass',
        'is_wizard_completed' => true, 'transaction_pin' => Hash::make('1234')]);
    UserWalletKyc::create(['user_id' => $u->id, 'status' => 'approved']);
    deposit(300, 'USD')->to($u)->overcharge()->commit();
    return $u;
}

it('records a successful bank withdrawal with amount, channel and IP — never the PIN', function () {
    $u = activityUser();
    $bank = $this->actingAs($u)->postJson(route('user.wallet.bank_accounts.store'), ['bank_name' => 'RBC', 'account_number' => '11112222'])->json('bank_account_id');
    $this->actingAs($u)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 50, 'bank_account_id' => $bank, 'pin' => '1234'])->assertOk();

    $saved = WalletEvent::where('type', 'bank_account_saved')->first();
    expect($saved->status)->toBe('success')->and(json_encode($saved->meta))->not->toContain('11112222');

    $e = WalletEvent::where('type', 'bank_withdrawal_requested')->first();
    expect($e->user_id)->toBe($u->id)
        ->and($e->status)->toBe('success')
        ->and((float) $e->amount)->toBe(50.0)
        ->and($e->channel)->toBe('web')
        ->and($e->ip)->toBe('127.0.0.1')
        ->and($e->meta)->toBe(['bank_account_id' => $bank]);
    expect(WalletEvent::all()->toJson())->not->toContain('1234"');
});

it('records failed attempts with the reason (wrong PIN)', function () {
    $u = activityUser();
    $bank = $this->actingAs($u)->postJson(route('user.wallet.bank_accounts.store'), ['bank_name' => 'RBC', 'account_number' => '11112222'])->json('bank_account_id');
    $this->actingAs($u)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 50, 'bank_account_id' => $bank, 'pin' => '9999'])->assertStatus(422);

    $e = WalletEvent::where('type', 'bank_withdrawal_requested')->first();
    expect($e->status)->toBe('failed')->and($e->message)->toBe('Incorrect transaction PIN. Please try again.');
});

it('records blocked attempts (identity check not done)', function () {
    $u = activityUser();
    UserWalletKyc::where('user_id', $u->id)->update(['status' => 'pending']);
    $this->actingAs($u)->postJson(route('user.wallet.bank_accounts.store'), ['bank_name' => 'RBC', 'account_number' => '11112222'])->assertForbidden();

    $e = WalletEvent::sole();
    expect($e->type)->toBe('bank_account_saved')->and($e->status)->toBe('failed')->and($e->message)->toBe('KYC verification required.');
});

it('ignores guests and non-wallet pages', function () {
    $this->get('/terms-of-service')->assertOk();
    $this->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 5])->assertUnauthorized();
    expect(WalletEvent::count())->toBe(0);
});

it('records admin KYC reviews against the member, marked as admin', function () {
    $admin = User::create(['name' => 'Boss', 'email' => 'boss@example.test', 'password' => 'secret-pass', 'type' => 'admin']);
    $member = activityUser();
    $kyc = UserWalletKyc::where('user_id', $member->id)->first();

    $this->actingAs($admin);
    app(UpdateKycReviewStatusAction::class)->execute('wallet', $kyc->id, 'rejected');

    $e = WalletEvent::where('type', 'kyc_reviewed')->sole();
    expect($e->user_id)->toBe($member->id)->and($e->actor_id)->toBe($admin->id)->and($e->actor_type)->toBe('admin')->and($e->message)->toBe('Set to rejected');
});

it('lets admins list, filter and export activity; members cannot', function () {
    $admin = User::create(['name' => 'Boss', 'email' => 'boss2@example.test', 'password' => 'secret-pass', 'type' => 'admin']);
    $a = activityUser(['name' => 'Alice Wallet']);
    $b = activityUser(['name' => 'Bob Wallet']);
    \App\Domain\Wallet\WalletActivity::record('send_money', ['user_id' => $a->id, 'actor_id' => $a->id, 'amount' => 25, 'counterparty_id' => $b->id, 'meta' => ['note' => '=HYPERLINK("x")']]);
    \App\Domain\Wallet\WalletActivity::record('send_money', ['user_id' => $b->id, 'actor_id' => $b->id, 'amount' => 900, 'status' => 'failed', 'message' => 'Incorrect transaction PIN.']);

    $this->actingAs($a)->getJson(route('admin.e-wallet.activity'))->assertForbidden();

    $all = $this->actingAs($admin)->getJson(route('admin.e-wallet.activity'))->assertOk();
    expect($all->json('meta.total'))->toBe(2)
        ->and($all->json('summary.failed_today'))->toBe(1)
        ->and($all->json('summary.pin_failures_24h'))->toBe(1)
        ->and($all->json('summary.money_out_today'))->toEqual(25.0)
        ->and($all->json('data.0.label'))->toBe('Sent money');

    expect($this->getJson(route('admin.e-wallet.activity', ['search' => 'Alice']))->json('meta.total'))->toBe(1);
    expect($this->getJson(route('admin.e-wallet.activity', ['user_id' => $b->id]))->json('meta.total'))->toBe(2); // as sender or receiver
    expect($this->getJson(route('admin.e-wallet.activity', ['status' => 'failed']))->json('data.0.user.name'))->toBe('Bob Wallet');
    expect($this->getJson(route('admin.e-wallet.activity', ['min_amount' => 100]))->json('meta.total'))->toBe(1);

    $csv = $this->get(route('admin.e-wallet.activity.export'))->assertOk()->streamedContent();
    expect($csv)->toContain('Alice Wallet')->toContain('Sent money')->toContain('Incorrect transaction PIN.');
});
