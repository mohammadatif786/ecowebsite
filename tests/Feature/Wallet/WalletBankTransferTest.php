<?php

use App\Domain\Wallet\WalletPageData;
use App\Models\BankWithdrawal;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserWalletKyc;
use Illuminate\Support\Facades\Hash;

// Test harness only: stand-ins for the wallet package when it is not installed.
foreach ([__DIR__ . '/harness_transfer.php', __DIR__ . '/../Vibes/harness_wallet.php'] as $stub) {
    if (is_file($stub)) {
        require_once $stub;
    }
}

beforeEach(function () {
    $GLOBALS['__harness_wallet'] = [];
});

function walletUser(array $extra = [], string $kyc = 'approved', float $balance = 500): User
{
    static $n = 0; $n++;
    $user = User::create($extra + [
        'name' => "Wallet $n", 'email' => "wallet$n@example.test", 'password' => 'secret-pass',
        'is_wizard_completed' => true, 'transaction_pin' => Hash::make('1234'),
    ]);
    if ($kyc !== 'none') {
        UserWalletKyc::create(['user_id' => $user->id, 'status' => $kyc]);
    }
    if ($balance > 0) {
        deposit($balance, 'USD')->to($user)->overcharge()->commit();
    }
    return $user;
}

function addBank(User $user, array $extra = []): int
{
    return test()->actingAs($user)->postJson(route('user.wallet.bank_accounts.store'), $extra + [
        'bank_name' => 'Scotiabank', 'account_number' => '1234567890', 'account_type' => 'checking',
    ])->assertOk()->json('bank_account_id');
}

it('adds a bank account and returns it masked', function () {
    $user = walletUser();
    $res = $this->actingAs($user)->postJson(route('user.wallet.bank_accounts.store'), [
        'bank_name' => 'Scotiabank', 'account_number' => '12345 67890', 'account_type' => 'savings',
    ])->assertOk();

    expect($res->json('bankAccounts'))->toHaveCount(1)
        ->and($res->json('bankAccounts.0'))->toMatchArray(['bank_name' => 'Scotiabank', 'last4' => '7890', 'is_default' => true])
        ->and($res->json('bankAccounts.0'))->not->toHaveKey('account_number');
    expect(UserBankAccount::first()->account_number)->toBe('1234567890');
});

it('keeps existing banks when adding another, and blocks duplicates', function () {
    $user = walletUser();
    addBank($user);
    addBank($user, ['bank_name' => 'RBC', 'account_number' => '99998888', 'is_default' => true]);

    $banks = WalletPageData::bankAccounts($user->id);
    expect($banks)->toHaveCount(2)
        ->and(collect($banks)->firstWhere('bank_name', 'RBC')['is_default'])->toBeTrue()
        ->and(collect($banks)->firstWhere('bank_name', 'Scotiabank')['is_default'])->toBeFalse();

    $this->actingAs($user)->postJson(route('user.wallet.bank_accounts.store'), [
        'bank_name' => 'RBC', 'account_number' => '99998888',
    ])->assertStatus(422)->assertJsonValidationErrors('account_number');
});

it('rejects a bad account number', function () {
    $this->actingAs(walletUser())->postJson(route('user.wallet.bank_accounts.store'), [
        'bank_name' => 'X', 'account_number' => '12<script>',
    ])->assertStatus(422);
});

it('withdraws to a saved bank with the right PIN and takes the fee', function () {
    $user = walletUser(balance: 200);
    $bankId = addBank($user);

    $res = $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), [
        'amount' => 100, 'bank_account_id' => $bankId, 'pin' => '1234',
    ])->assertOk();

    expect($res->json('withdrawal.fee_amount'))->toEqual(5.0)
        ->and($res->json('withdrawal.payout_amount'))->toEqual(95.0)
        ->and($res->json('balance'))->toEqual(100.0);
    $w = BankWithdrawal::first();
    expect($w->status)->toBe('pending')->and($w->account_number)->toBe('1234567890');
});

it('refuses a wrong PIN, and locks after five wrong tries', function () {
    $user = walletUser();
    $bankId = addBank($user);
    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 20, 'bank_account_id' => $bankId, 'pin' => '0000'])
            ->assertStatus(422)->assertJsonValidationErrors('pin');
    }
    $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 20, 'bank_account_id' => $bankId, 'pin' => '1234'])
        ->assertStatus(422)->assertJsonFragment(['Too many wrong PINs. Try again in 900 seconds.']);
    expect(BankWithdrawal::count())->toBe(0);
});

it('needs a PIN to be set first', function () {
    $user = walletUser(['transaction_pin' => null]);
    $bankId = addBank($user);
    $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 20, 'bank_account_id' => $bankId, 'pin' => '1234'])
        ->assertStatus(422)->assertJsonFragment(['Set a transaction PIN first.']);
});

it("cannot withdraw to someone else's bank account", function () {
    $owner = walletUser();
    $theirBank = addBank($owner);
    $me = walletUser();
    addBank($me, ['account_number' => '55556666']);

    $this->actingAs($me)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 20, 'bank_account_id' => $theirBank, 'pin' => '1234'])
        ->assertStatus(422)->assertJsonValidationErrors('bank');
    expect(BankWithdrawal::count())->toBe(0);
});

it('blocks more than the balance and out-of-range amounts', function () {
    $user = walletUser(balance: 50);
    $bankId = addBank($user);
    $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 60, 'bank_account_id' => $bankId, 'pin' => '1234'])
        ->assertStatus(422)->assertJsonValidationErrors('amount');
    $this->actingAs($user)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 5, 'bank_account_id' => $bankId, 'pin' => '1234'])
        ->assertStatus(422)->assertJsonValidationErrors('amount');
    expect(BankWithdrawal::count())->toBe(0);
});

it('requires an approved identity check (JSON gets 403, pages go to the wallet with ?kyc=1)', function () {
    $pending = walletUser(kyc: 'pending');
    $this->actingAs($pending)->postJson(route('user.wallet.bank_accounts.store'), ['bank_name' => 'X', 'account_number' => '12345678'])
        ->assertForbidden();
    $this->actingAs($pending)->get(route('user.wallet.gated'))->assertRedirect(route('user.wallet', ['kyc' => 1]));

    $none = walletUser(kyc: 'none');
    $this->actingAs($none)->postJson(route('user.wallet.bank_withdrawal'), ['amount' => 20, 'bank_account_id' => 1, 'pin' => '1234'])
        ->assertForbidden();
});

it('wallet page data reports KYC status and PIN', function () {
    $user = walletUser(kyc: 'rejected');
    $data = WalletPageData::forUser($user);
    expect($data['kyc']['status'])->toBe('rejected')
        ->and($data['hasPin'])->toBeTrue()
        ->and($data['withdrawalFeePercent'])->toEqual(5.0)
        ->and($data['bankAccounts'])->toBe([]);
    expect(WalletPageData::kyc(walletUser(kyc: 'none')->id)['status'])->toBe('none');
});
