<?php

namespace App\Domain\Vibes\Actions;

use App\Domain\Vibes\DTOs\GiftCashOutResult;
use App\Models\GiftCoins;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use O21\LaravelWallet\Models\Custodian;

/**
 * "Convert to cash" on the Gifts tab: turns coins the member received as gifts into wallet USD.
 *
 * Received gift coins were also added to the member's coin balance when the gift arrived, so a
 * coin can be cashed out only while it is still in that balance (cash it or spend it, not both).
 * Rate, payout share and minimum come from config — never from the request. Everything runs in
 * one transaction with the member's row and gift rows locked, so two taps can't cash the same coins.
 */
class ConvertGiftCoinsToCashAction
{
    /** Gift coins still waiting to be cashed, capped by the coins the member still holds. */
    public static function cashableCoins(User $user): int
    {
        $received = (int) GiftCoins::where('recieved_id', $user->getKey())->whereNull('status')->sum('coins');

        return max(0, min($received, (int) $user->coins));
    }

    /** @throws ValidationException */
    public function execute(User $user, int $coins): GiftCashOutResult
    {
        $rate = (float) config('vibes.cashout.coin_rate', 0.01);
        $ratio = (float) config('vibes.cashout.payout_ratio', 0.5);
        $minimum = (float) config('vibes.cashout.minimum_usd', 25);

        if ($coins < 1) {
            throw ValidationException::withMessages(['coins' => 'Choose how many coins to convert.']);
        }

        $payout = round($coins * $rate * $ratio, 2);
        if ($payout < $minimum) {
            throw ValidationException::withMessages([
                'coins' => 'Minimum cash-out is $'.number_format($minimum, 2).'. You need $'.number_format($minimum - $payout, 2).' more to convert.',
            ]);
        }

        return DB::transaction(function () use ($user, $coins, $payout) {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $gifts = GiftCoins::where('recieved_id', $locked->getKey())
                ->whereNull('status')
                ->orderBy('created_at')->orderBy('id')
                ->lockForUpdate()
                ->get();

            $received = (int) $gifts->sum('coins');
            if ($coins > min($received, (int) $locked->coins)) {
                throw ValidationException::withMessages([
                    'coins' => 'You don’t have enough received gift coins to convert this amount.',
                ]);
            }

            // The coins leave the spendable balance (guarded, so it can never go below zero).
            $taken = User::query()->whereKey($locked->getKey())->where('coins', '>=', $coins)->decrement('coins', $coins);
            if ($taken !== 1) {
                throw ValidationException::withMessages(['coins' => 'You don’t have enough coins to convert this amount.']);
            }

            $left = $coins;
            foreach ($gifts as $gift) {
                if ($left <= 0) {
                    break;
                }
                if ($gift->coins <= $left) {
                    $left -= $gift->coins;
                    $gift->forceFill(['status' => now()])->save();

                    continue;
                }
                // Part of this gift is cashed: split it so the rest stays available.
                $rest = $gift->replicate();
                $rest->coins = $gift->coins - $left;
                $rest->status = null;
                $rest->save();
                $gift->forceFill(['coins' => $left, 'status' => now()])->save();
                $left = 0;
            }

            deposit($payout, 'USD')
                ->from(Custodian::of('e_money'))
                ->to($locked)
                ->overcharge()
                ->commit();

            $locked->refresh();

            return new GiftCashOutResult(
                $coins,
                $payout,
                (float) ($locked->balance('USD')->value->get() ?? 0),
                max(0, min($received - $coins, (int) $locked->coins)),
                (int) $locked->coins,
            );
        });
    }
}
