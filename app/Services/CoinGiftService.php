<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moves coins from one user to another for Big Ups and reel gifts.
 *
 * The balance check and the deduction are one SQL statement
 * (`UPDATE … SET coins = coins - n WHERE id = ? AND coins >= n`), so two
 * taps at the same moment can never spend the same coins twice or push a
 * balance below zero.
 */
class CoinGiftService
{
    /**
     * @param  Closure|null  $record  Runs inside the same transaction after the coins move
     *                                (gift record, counters, notification).
     * @return int The sender's balance after the gift.
     *
     * @throws ValidationException 422 for an invalid amount, a self-gift or not enough coins.
     */
    public function send(User $sender, User $receiver, int $coins, ?Closure $record = null): int
    {
        if ($coins < 1) {
            throw ValidationException::withMessages(['coins' => 'Choose how many coins to send.']);
        }

        if ((int) $sender->getKey() === (int) $receiver->getKey()) {
            throw ValidationException::withMessages(['coins' => 'You can’t send coins to yourself.']);
        }

        DB::transaction(function () use ($sender, $receiver, $coins, $record) {
            $taken = User::query()
                ->whereKey($sender->getKey())
                ->where('coins', '>=', $coins)
                ->decrement('coins', $coins);

            if ($taken !== 1) {
                throw ValidationException::withMessages(['coins' => 'Insufficient coins']);
            }

            User::query()->whereKey($receiver->getKey())->increment('coins', $coins);

            if ($record) {
                $record();
            }
        });

        return (int) User::query()->whereKey($sender->getKey())->value('coins');
    }
}
