<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    public function createTransaction($data)
    {
        return Transaction::create($data);
    }

    public function createSubscription(array $data)
    {
        return \Laravel\Cashier\Subscription::create($data);
    }

    public function isPaymentProcessed(string $stripePaymentIntentId): bool
    {
        return \Laravel\Cashier\Subscription::where('stripe_id', $stripePaymentIntentId)->exists();
    }
}