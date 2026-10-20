<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\DTOs\CoinPaymentSuccessDTO;
use App\DTOs\CoinRechargeDTO;
use App\Models\SubscribedPlan;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\User;
use Stripe\Stripe;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Subscription;
use O21\LaravelWallet\Models\Custodian;
use Stripe\PaymentMethod;
use Stripe\Price;
use App\Actions\InitiateCoinRechargeAction;
use App\Actions\CompleteCoinRechargeAction;

class AppWalletController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    public function index()
    {
        $user = auth()->user();
        $balance = $user->balance('USD')->value->get();
        $transactions = $user->receivedTransactions()->latest()->get();
        return response()->json([
            'status' => true,
            'currentBalance' => $balance,
            'transactions' => $transactions,
        ], 200);
    }

    public function addAmoutPage()
    {
        $user = auth()->user();
        $balance = $user->balance('USD')->value->get();
        return response()->json([
            'status' => true,
            'currentBalance' => $balance
        ], 200);
    }

    public function walletActivity(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $balance = (float) ($user->balance('USD')->value->get() ?? 0);
        $runningBalance = $balance;

        // 1. Transactions (Laravel Wallet)
        $activities = $user->transactions()
            ->with(['to', 'from'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($transaction) use (&$runningBalance, $user) {
                $isPositive = (int) $transaction->to_id === (int) $user->id
                    && in_array($transaction->to_type, [$user->getMorphClass(), User::class], true);

                $amount = (float) $transaction->amount;
                $currentRunningBalance = $runningBalance;

                if ($isPositive) {
                    $runningBalance -= $amount;
                } else {
                    $runningBalance += $amount;
                }

                $meta = is_array($transaction->meta) ? $transaction->meta : [];
                $counterpartyName = $isPositive
                    ? ($meta['sender_name'] ?? $transaction->from?->name ?? 'System')
                    : ($meta['recipient_name'] ?? $transaction->to?->name ?? 'System');

                $counterpartyTag = $isPositive
                    ? ($meta['sender_linkup_id'] ?? $transaction->from?->linkup_id ?? '')
                    : ($meta['recipient_linkup_id'] ?? $transaction->to?->linkup_id ?? '');

                $type = $meta['type'] ?? $transaction->type ?? 'wallet';
                $kind = $isPositive ? 'received' : 'sent';
                if (in_array($type, ['recharge', 'cash_deposit', 'deposit'])) {
                    $kind = 'deposits';
                }

                return [
                    'id' => (string) ($transaction->uuid ?? $transaction->id),
                    'title' => $this->walletActivityTitle((string) $type, $isPositive, $counterpartyName),
                    'amount' => $amount,
                    'isPositive' => $isPositive,
                    'status' => strtolower((string) ($transaction->status ?? 'success')),
                    'type' => $type,
                    'kind' => $kind,
                    'counterparty' => trim($counterpartyName . ($counterpartyTag ? ' @' . ltrim($counterpartyTag, '@') : '')),
                    'note' => $meta['note'] ?? null,
                    'runningBalance' => $currentRunningBalance,
                    'date' => optional($transaction->created_at)->toISOString(),
                    'dateLabel' => optional($transaction->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        // 2. Subscriptions (Cash & Coin Deposits)
        $subscriptions = \Laravel\Cashier\Subscription::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($sub) {
                $isCoin = $sub->type === 'coin' || $sub->name === 'coin';
                return [
                    'id' => 'sub-' . $sub->id,
                    'title' => $isCoin ? 'Coin Deposit' : 'Cash Deposit',
                    'amount' => (float) ($sub->stripe_price ?? 0),
                    'isPositive' => true,
                    'status' => strtolower((string) ($sub->stripe_status ?? 'success')),
                    'type' => $isCoin ? 'coin_deposit' : 'cash_deposit',
                    'kind' => 'wallet-coins',
                    'counterparty' => 'Stripe Payment',
                    'note' => $isCoin ? "{$sub->quantity} Coins Purchased" : 'Cash Top Up',
                    'runningBalance' => null,
                    'date' => optional($sub->created_at)->toISOString(),
                    'dateLabel' => optional($sub->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        // 3. Money Requests
        $moneyRequests = \App\Models\UserMoneyRequest::with(['requester:id,name,linkup_id', 'recipient:id,name,linkup_id'])
            ->where(function ($q) use ($user) {
                $q->where('recipient_id', $user->id)
                    ->orWhere('requester_id', $user->id);
            })
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($req) use ($user) {
                $isIncoming = (int) $req->recipient_id === (int) $user->id;
                $person = $isIncoming ? $req->requester : $req->recipient;
                return [
                    'id' => 'req-' . $req->id,
                    'title' => $isIncoming ? 'Request from ' . ($person->name ?? 'LinkUp User') : 'Request to ' . ($person->name ?? 'LinkUp User'),
                    'amount' => (float) $req->amount,
                    'isPositive' => $isIncoming,
                    'status' => strtolower((string) ($req->status ?? 'pending')),
                    'type' => 'money_request',
                    'kind' => 'requested',
                    'counterparty' => $person->name ?? '',
                    'note' => $req->note ?? null,
                    'runningBalance' => null,
                    'date' => optional($req->created_at)->toISOString(),
                    'dateLabel' => optional($req->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        // 4. Bank Withdrawals
        $withdrawals = \App\Models\BankWithdrawal::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($w) {
                return [
                    'id' => 'wd-' . $w->id,
                    'title' => 'Bank Withdrawal',
                    'amount' => (float) $w->amount,
                    'isPositive' => false,
                    'status' => strtolower((string) ($w->status ?? 'processing')),
                    'type' => 'withdrawal',
                    'kind' => 'withdrawals',
                    'counterparty' => $w->bank_name ?? 'Bank',
                    'note' => $w->failure_reason ?? null,
                    'runningBalance' => null,
                    'date' => optional($w->created_at)->toISOString(),
                    'dateLabel' => optional($w->created_at)->diffForHumans(null, true) . ' ago',
                ];
            });

        // Merge all sources & sort by date
        $all = $activities
            ->concat($subscriptions)
            ->concat($moneyRequests)
            ->concat($withdrawals)
            ->sortByDesc(fn($item) => $item['date'] ?? '')
            ->values();

        // Optional Server-side Filtering & Search
        $filter = strtolower(trim($request->get('filter') ?? $request->get('type') ?? $request->get('tab') ?? 'all'));
        $search = strtolower(trim($request->get('search') ?? ''));

        if ($filter !== 'all') {
            $all = $all->filter(function ($item) use ($filter) {
                if ($filter === 'deposits') {
                    return $item['kind'] === 'deposits' || $item['kind'] === 'wallet-coins' || ($item['isPositive'] && in_array($item['type'], ['coin_deposit', 'cash_deposit', 'recharge', 'deposit']));
                }
                if ($filter === 'received') {
                    return $item['isPositive'] && $item['kind'] !== 'requested';
                }
                if ($filter === 'sent') {
                    return ! $item['isPositive'] && $item['kind'] !== 'requested';
                }
                return $item['kind'] === $filter || $item['type'] === $filter;
            })->values();
        }

        if ($search !== '') {
            $all = $all->filter(function ($item) use ($search) {
                $haystack = strtolower(implode(' ', [
                    $item['title'],
                    $item['counterparty'],
                    $item['note'] ?? '',
                    $item['status'],
                    $item['amount'],
                ]));
                return str_contains($haystack, $search);
            })->values();
        }

        return response()->json([
            'status' => true,
            'activity' => $all,
        ], 200);
    }

    protected function walletActivityTitle(string $type, bool $isPositive, string $counterpartyName): string
    {
        return match ($type) {
            'recharge', 'cash_deposit' => 'Cash Deposit',
            'coin', 'coin_deposit', 'coin_purchase' => 'Coin Deposit',
            'p2p_transfer' => $isPositive ? 'Received from ' . $counterpartyName : 'Sent to ' . $counterpartyName,
            'money_request_payment' => $isPositive ? 'Request paid by ' . $counterpartyName : 'Paid request to ' . $counterpartyName,
            default => $isPositive ? 'Cash Deposit' : 'Wallet payment',
        };
    }

    // buy wallet cash top-up
    public function submitAmountToWallet(Request $request)
    {
        $request->validate([
            'selectedAmount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $amount = (int) $request->selectedAmount * 100;

        $customer = $stripe->customers->create([
            'name' => $user->name,
            'email' => $user->email,
            'address' => [
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
            ],
        ]);

        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' => $customer->id,
        ], [
            'stripe_version' => '2025-05-28.basil',
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customer->id,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return response()->json([
            'status' => true,
            'customer_id' => $customer->id,
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'ephemeralKey' => $ephemeralKey->secret,
            'created_at' => $paymentIntent->created,
            'publishableKey' => config('services.stripe.key'),
        ], 200);
    }

    public function submitCoinToWallet(Request $request)
    {
        $request->validate([
            'selectedCoin' => 'required|numeric|min:1|max:10000',
            'coinCount' => 'required|integer|min:1|max:1000000',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $amount = (int) ($request->selectedCoin * 100);

        $customer = $stripe->customers->create([
            'name' => $user->name,
            'email' => $user->email,
            'address' => [
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
            ],
        ]);

        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' => $customer->id,
        ], [
            'stripe_version' => '2025-05-28.basil',
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',
            'customer' => $customer->id,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'type' => 'coin_purchase',
                'user_id' => $user->id,
                'coin_count' => (int) $request->coinCount,
            ],
        ]);

        return response()->json([
            'status' => true,
            'customer_id' => $customer->id,
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount,
            'currency' => $paymentIntent->currency,
            'ephemeralKey' => $ephemeralKey->secret,
            'created_at' => $paymentIntent->created,
            'publishableKey' => config('services.stripe.key'),
        ], 200);
    }

    public function oneTimePaySuccess(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'stripe_id' => 'required',
            'stripe_price' => 'required',
            'stripe_status' => 'required',
        ]);

        $user_id = $request->user_id;
        $user = User::findOrFail($user_id);

        Subscription::create([
            'user_id' => $request->user_id,
            'type' => 'payment',
            'quantity' => 1,
            'stripe_id' => $request->stripe_id,
            'stripe_price' => $request->stripe_price / 100,
            'stripe_status' => $request->stripe_status,
            'ends_at' => Carbon::now(),
        ]);
        deposit($request->stripe_price / 100, 'USD')->from(Custodian::of('e_money'))->to($user)->overcharge()->commit();

        return response()->json([
            'status' => true,
            'message' => 'Payment successful'
        ], 200);
    }

    public function oneTimePayCoinSuccess(Request $request, CompleteCoinRechargeAction $action)
    {
        $request->validate([
            'session_id' => 'required',
            'user_id' => 'required|integer',
        ]);

        $dto = new CoinPaymentSuccessDTO(
            $request->session_id,
            (int) $request->user_id,
            0
        );

        try {
            $action->execute($dto);
            return response()->json([
                'status' => true,
                'message' => 'Coins added successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // get the payment intent for purchase plan
    public function createSubscriptionPaymentIntent(Request $request)
    {
        $request->validate([
            'plan_price_id' => 'required',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $existing_plan = SubscribedPlan::where('user_id', $user->id)->first();

        if ($existing_plan) {
            return response()->json([
                'status' => false,
                'message' => 'You have already purchased a subscription plan.'
            ], 404);
        }


        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // Create Stripe customer if not exists
        if (!$user->stripe_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'address' => [
                    'city' => $user->new_city,
                    'state' => $user->new_state,
                    'country' => $user->country,
                ]
            ]);
            $user->stripe_id = $customer->id;
            $user->save();
        }

        try {
            // Retrieve the Stripe price object
            $price = $stripe->prices->retrieve($request->plan_price_id);
            if (!$price || !$price->id) {
                return response()->json(['error' => 'No valid price found for this product.'], 400);
            }

            $ephemeralKey = $stripe->ephemeralKeys->create([
                'customer' => $user->stripe_id,
            ], [
                'stripe_version' => '2025-05-28.basil',
            ]);

            // Create subscription via Stripe API
            $subscription = $stripe->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'user_id' => $user->id,
                    'price_id' => $price->id
                ],
            ]);

            $paymentIntent = $subscription->latest_invoice->payment_intent;

            return response()->json([
                'status' => true,
                'customer_id' => $user->stripe_id,
                'subscription_id' => $subscription->id,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret ?? null,
                'ephemeralKey' => $ephemeralKey->secret,
                'trial_end' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'created_at' => date('Y-m-d H:i:s', $paymentIntent->created),
                'publishableKey' => config('services.stripe.key'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Subscription creation failed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function subscriptionSuccess(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'subscription_id' => 'required',
            'customer_id' => 'required',
            'plan_id' => 'required',
            'stripe_status' => 'required',
            'amount_paid' => 'required',
            'currency' => 'required',
        ]);
        $user = User::findOrFail($request->user_id);
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $start_date = now(); // Subscription start date
        $end_date = now()->addMonth(); // Example: Subscription ends after 1 month
        // Store the data in the database
        SubscribedPlan::create([
            'subscription_id' => $request->subscription_id,
            'customer_id' => $request->customer_id,
            'user_id' => $request->user_id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'plan_id' => $request->plan_id,
            'status' => $request->stripe_status,
            'amount_paid' => $request->amount_paid / 100,
            'currency' => $request->currency,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Subscription created successfully.'
        ], 200);
    }
}
