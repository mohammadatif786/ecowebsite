<?php

use App\Actions\CancelBankWithdrawalAction;
use App\Models\BankWithdrawal;
use App\Models\User;
use App\Models\WalletEvent;

foreach ([__DIR__ . '/harness_transfer.php', __DIR__ . '/../Vibes/harness_wallet.php'] as $stub) {
    if (is_file($stub)) {
        require_once $stub;
    }
}

beforeEach(function () {
    $GLOBALS['__harness_wallet'] = [];
    $GLOBALS['__harness_mails'] = [];
});

function pendingWithdrawal(float $amount = 100): BankWithdrawal
{
    static $n = 0; $n++;
    $u = User::create(['name' => "Payee $n", 'email' => "payee$n@example.test", 'password' => 'secret-pass']);
    return BankWithdrawal::create(['user_id' => $u->id, 'amount' => $amount, 'fee_percent' => 5, 'fee_amount' => $amount * 0.05,
        'payout_amount' => $amount * 0.95, 'bank_name' => 'RBC', 'account_number' => '11112222', 'status' => 'pending']);
}

function payoutAdmin(): User
{
    return User::firstOrCreate(['email' => 'payout-admin@example.test'], ['name' => 'Payout Admin', 'password' => 'secret-pass', 'type' => 'admin']);
}

function walletOf(int $userId): float
{
    return (float) ($GLOBALS['__harness_wallet'][$userId] ?? 0);
}

it('approves a pending withdrawal once, emails the member and records it', function () {
    $w = pendingWithdrawal();
    $this->actingAs(payoutAdmin())->postJson(route('admin.e-wallet.withdrawals.approve', $w))->assertOk()->assertJson(['status' => 'completed']);

    expect($w->fresh()->status)->toBe('completed')->and($w->fresh()->processed_at)->not->toBeNull()
        ->and(walletOf($w->user_id))->toBe(0.0)
        ->and($GLOBALS['__harness_mails'])->toHaveCount(1);
    expect(WalletEvent::where('type', 'bank_withdrawal_approved')->sole())
        ->user_id->toBe($w->user_id)->actor_type->toBe('admin');

    // A second click (or a second admin) cannot approve it again.
    $this->postJson(route('admin.e-wallet.withdrawals.approve', $w))->assertStatus(422);
});

it('rejects with a reason and refunds exactly once', function () {
    $w = pendingWithdrawal(80);
    $this->actingAs(payoutAdmin())->postJson(route('admin.e-wallet.withdrawals.reject', $w), ['reason' => 'Name does not match'])->assertOk();

    expect($w->fresh()->status)->toBe('failed')->and($w->fresh()->failure_reason)->toBe('Name does not match')
        ->and(walletOf($w->user_id))->toBe(80.0);

    $this->postJson(route('admin.e-wallet.withdrawals.reject', $w), ['reason' => 'again'])->assertStatus(422);
    $this->postJson(route('admin.e-wallet.withdrawals.approve', $w))->assertStatus(422);
    expect(walletOf($w->user_id))->toBe(80.0); // no second refund
});

it('requires a reason to reject, and only admins can act', function () {
    $w = pendingWithdrawal();
    $this->actingAs(payoutAdmin())->postJson(route('admin.e-wallet.withdrawals.reject', $w), [])->assertStatus(422);
    $member = User::find($w->user_id);
    $this->actingAs($member)->postJson(route('admin.e-wallet.withdrawals.approve', $w))->assertForbidden();
    expect($w->fresh()->status)->toBe('pending');
});

it('a member cannot cancel after an admin rejected (no double refund), and can cancel a fresh one', function () {
    $w = pendingWithdrawal(50);
    $this->actingAs(payoutAdmin())->postJson(route('admin.e-wallet.withdrawals.reject', $w), ['reason' => 'Bad account'])->assertOk();

    expect(fn () => app(CancelBankWithdrawalAction::class)->execute($w->id, $w->user_id))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
    expect(walletOf($w->user_id))->toBe(50.0);

    $fresh = pendingWithdrawal(30);
    app(CancelBankWithdrawalAction::class)->execute($fresh->id, $fresh->user_id);
    expect($fresh->fresh()->status)->toBe('cancelled')->and(walletOf($fresh->user_id))->toBe(30.0);
});

it('does not let a member cancel after 30 minutes', function () {
    $w = pendingWithdrawal(20);
    BankWithdrawal::whereKey($w->id)->update(['created_at' => now()->subHour()]);
    expect(fn () => app(CancelBankWithdrawalAction::class)->execute($w->id, $w->user_id))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
    expect($w->fresh()->status)->toBe('pending')->and(walletOf($w->user_id))->toBe(0.0);
});
