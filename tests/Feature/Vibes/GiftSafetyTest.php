<?php

use App\Domain\Vibes\Actions\ConvertGiftCoinsToCashAction;
use App\Http\Requests\Vibes\StoreUVibeRequest;
use App\Http\Requests\Vibes\StoreVibeRequest;
use App\Models\GiftCoins;
use App\Models\User;
use App\Support\VibeGiftCatalog;
use Illuminate\Validation\ValidationException;

if (is_file(__DIR__.'/harness_wallet.php')) {
    require_once __DIR__.'/harness_wallet.php';
}

function giftReceiver(int $giftCoins, int $held): User
{
    $sender = User::factory()->create();
    $user = User::factory()->create();
    $user->forceFill(['coins' => $held])->save();
    GiftCoins::create(['sender_id' => $sender->id, 'recieved_id' => $user->id, 'name' => 'Big Up', 'emoji' => '⚡', 'coins' => $giftCoins]);

    return $user->fresh();
}

// ---- Cash-out ---------------------------------------------------------------

it('cashes out received gift coins once and takes them out of the coin balance', function () {
    $user = giftReceiver(6000, 6000);

    $result = app(ConvertGiftCoinsToCashAction::class)->execute($user, 5000);

    expect($result->payout)->toBe(25.0)
        ->and((int) $user->fresh()->coins)->toBe(1000)
        ->and((int) GiftCoins::where('recieved_id', $user->id)->whereNull('status')->sum('coins'))->toBe(1000)
        ->and((int) GiftCoins::where('recieved_id', $user->id)->whereNotNull('status')->sum('coins'))->toBe(5000);
});

it('will not cash out gift coins that were already spent', function () {
    $user = giftReceiver(6000, 1000); // received 6000, spent 5000 of them

    expect(fn () => app(ConvertGiftCoinsToCashAction::class)->execute($user, 5000))->toThrow(ValidationException::class);
    expect((int) $user->fresh()->coins)->toBe(1000);
    expect(ConvertGiftCoinsToCashAction::cashableCoins($user->fresh()))->toBe(1000);
});

it('cannot cash out the same coins twice', function () {
    $user = giftReceiver(5000, 5000);
    app(ConvertGiftCoinsToCashAction::class)->execute($user, 5000);

    expect(fn () => app(ConvertGiftCoinsToCashAction::class)->execute($user->fresh(), 5000))->toThrow(ValidationException::class);
    expect((int) $user->fresh()->coins)->toBe(0);
});

it('ignores a minimum sent by the app', function () {
    $user = giftReceiver(1000, 1000);

    $this->actingAs($user)
        ->postJson('/user/convert-gifts-to-cash', ['coins' => 100, 'minimum_cashout' => 1])
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect((int) $user->fresh()->coins)->toBe(1000);
});

// ---- Gift catalog -------------------------------------------------------------

it('prices a gift from the catalog, not from the app', function () {
    $gift = VibeGiftCatalog::resolve(['gift_id' => 'crown', 'gift_name' => 'Likkle Up', 'coins' => 1]);

    expect($gift)->toMatchArray(['name' => 'Caribbean Crown', 'emoji' => '👑', 'coins' => 1000]);
});

it('maps an older app\'s gift name to the catalog price', function () {
    expect(VibeGiftCatalog::resolve(['gift_name' => 'caribbean crown', 'coins' => 1])['coins'])->toBe(1000);
});

it('refuses a made-up gift name', function () {
    VibeGiftCatalog::resolve(['gift_name' => 'Diamond Crown', 'coins' => 1]);
})->throws(ValidationException::class);

it('sends a plain Big Up for a custom amount, never with a custom label', function () {
    expect(VibeGiftCatalog::resolve(['coins' => 7]))->toMatchArray(['name' => 'Big Up', 'emoji' => '⚡', 'coins' => 7]);
});

it('needs a catalog gift for reel gifts', function () {
    VibeGiftCatalog::resolve(['coins' => 7], allowCustom: false);
})->throws(ValidationException::class);

// ---- Vibes vs U Vibe posts ----------------------------------------------------

it('never lets a Vibes post become a U Vibe post', function () {
    $request = StoreVibeRequest::create('/user/vibes', 'POST', ['is_uvibe' => '1', 'caption' => 'x']);
    (fn () => $this->prepareForValidation())->call($request);

    expect($request->input('is_uvibe'))->toBeFalse();
});

it('keeps U Vibe posts as U Vibe posts', function () {
    $request = StoreUVibeRequest::create('/user/uvibe/posts', 'POST', ['caption' => 'x']);
    (fn () => $this->prepareForValidation())->call($request);

    expect($request->input('is_uvibe'))->toBeTrue();
});

// ---- Rate limits ----------------------------------------------------------------

it('gives every numeric rate limit in the member routes its own counter', function () {
    foreach (['routes/web/user.php', 'routes/api/mobile.php'] as $file) {
        $path = base_path($file);
        if (! is_file($path)) {
            continue;
        }
        expect(preg_match_all("/throttle:\\d+,\\d+'/", file_get_contents($path)))->toBe(0, "$file has a throttle without a prefix");
    }
});
