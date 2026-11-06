<?php

namespace App\Http\Middleware;

use App\Domain\Wallet\WalletActivity;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every wallet action a member takes (web and mobile), whether it worked or not, so admins can
 * follow it in E-Wallet → Wallet Activity. Matched by controller action, so old and new screens are covered.
 * Never stores PINs, card or account numbers: only the safe fields listed in SAFE_META.
 */
class RecordWalletActivity
{
    /** "ControllerBaseName@method" => event type */
    public const ACTIONS = [
        // Top-ups (start = Stripe checkout opened, completed = return from Stripe)
        'WalletController@submitAmountToWallet' => 'topup_started',
        'AppWalletController@submitAmountToWallet' => 'topup_started',
        'WalletController@oneTimePaySuccess' => 'topup_completed',
        'AppWalletController@oneTimePaySuccess' => 'topup_completed',
        'WalletController@submitCoinToWallet' => 'coins_purchase_started',
        'AppWalletController@submitCoinToWallet' => 'coins_purchase_started',
        'WalletController@oneTimePayCoinSuccess' => 'coins_purchased',
        'AppWalletController@oneTimePayCoinSuccess' => 'coins_purchased',
        // Member to member
        'WalletController@sendMoney' => 'send_money',
        'UserWalletController@sendMoney' => 'send_money',
        'WalletController@requestMoney' => 'request_money',
        'UserWalletController@requestMoney' => 'request_money',
        'WalletController@SendMoneyRequest' => 'request_money',
        'WalletController@payMoneyRequest' => 'request_paid',
        'UserWalletController@payMoneyRequest' => 'request_paid',
        'WalletController@rejectMoneyRequest' => 'request_rejected',
        'UserWalletController@rejectMoneyRequest' => 'request_rejected',
        'WalletController@cancelMoneyRequest' => 'request_cancelled',
        'UserWalletController@cancelMoneyRequest' => 'request_cancelled',
        'WalletController@respond' => 'request_responded',
        'WalletController@buyTicket' => 'ticket_paid_wallet',
        'TicketPurchaseController@buyTicketWallet' => 'ticket_paid_wallet',
        'VibeShopController@buy' => 'tag_item_bought',
        // Coins and gifts
        'WalletController@sendCoins' => 'coins_gifted',
        'ReelInteractionController@sendGift' => 'gift_sent',
        'LiveStreamGumletController@sendGift' => 'gift_sent',
        'VibeInteractionController@sendGift' => 'gift_sent',
        'GiftsController@buyGifts' => 'gift_sent',
        'GiftCashOutController@store' => 'gifts_cashed_out',
        'VibeInteractionController@transferToWallet' => 'earnings_to_wallet',
        'LiveStreamController@transferEarnings' => 'earnings_to_wallet',
        'LiveStreamGumletController@transferSessionEarnings' => 'earnings_to_wallet',
        // Bank
        'WalletController@storeBankDetails' => 'bank_account_saved',
        'WalletController@addBankAccount' => 'bank_account_saved',
        'WalletController@initiateBankWithdrawal' => 'bank_withdrawal_requested',
        'WalletController@withdrawToBank' => 'bank_withdrawal_requested',
        'WalletController@cancelBankWithdrawal' => 'bank_withdrawal_cancelled',
        // Security and identity
        'ProfileSecurityController@updateTransactionPin' => 'pin_changed',
        'UserWalletController@updateTransactionPin' => 'pin_changed',
        'UserWalletController@verifyTransactionPin' => 'pin_verified',
        'WalletController@storeKyc' => 'kyc_submitted',
        'UsersController@submitVerifyKyc' => 'kyc_submitted',
        // Contacts and ASUE
        'WalletController@addContact' => 'contact_added',
        'WalletController@AddToContact' => 'contact_added',
        'UserWalletController@addContact' => 'contact_added',
        'WalletController@removeContact' => 'contact_removed',
        'UserWalletController@removeContact' => 'contact_removed',
        'UserWalletController@storeAsue' => 'asue_created',
        'UserWalletController@acceptAsueParticipation' => 'asue_joined',
    ];

    /** GET routes that still change money (payment returns). Everything else must be a write. */
    private const GET_TYPES = ['topup_completed', 'coins_purchased'];

    private const AMOUNT_KEYS = ['amount', 'amount_usd', 'usd', 'price'];
    private const COIN_KEYS = ['coins', 'coin', 'luc', 'quantity'];
    private const COUNTERPARTY_KEYS = ['recipientId', 'recipient_id', 'receiver_id', 'to_user_id', 'contact_user_id', 'user_id'];
    private const SAFE_META = ['note', 'gift_id', 'bank_account_id', 'bankIndex', 'id_type', 'tab', 'status', 'currency', 'payment_method'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $type = $this->typeFor($request);
            if ($type !== null) {
                $this->record($request, $response, $type);
            }
        } catch (\Throwable) {
            // The trail is best-effort; never affect the member's request.
        }

        return $response;
    }

    private function typeFor(Request $request): ?string
    {
        $route = $request->route();
        if (! $route || ! is_string($action = $route->getActionName()) || ! str_contains($action, '@')) {
            return null;
        }

        [$class, $method] = explode('@', $action, 2);
        $type = self::ACTIONS[class_basename($class) . '@' . $method] ?? null;
        if ($type === null) {
            return null;
        }

        if ($request->isMethod('GET') && ! in_array($type, self::GET_TYPES, true)) {
            return null;
        }

        return $type;
    }

    private function record(Request $request, Response $response, string $type): void
    {
        $user = $request->user();
        if (! $user) {
            return;
        }

        [$failed, $message] = $this->outcome($request, $response);

        $input = $request->except(['pin', 'current_pin', 'pin_confirmation', 'password', 'transaction_pin',
            'account_number', 'routing_number', 'id_number', 'tax_id', 'card', 'card_number', 'cvc', 'banks']);

        $amount = $this->firstNumeric($input, self::AMOUNT_KEYS);
        $coins = $this->firstNumeric($input, self::COIN_KEYS);
        $counterparty = $this->firstNumeric($input, self::COUNTERPARTY_KEYS);
        if ($counterparty !== null && (int) $counterparty === (int) $user->id) {
            $counterparty = null;
        }

        $meta = array_filter(Arr::only($input, self::SAFE_META), fn ($v) => is_scalar($v) && $v !== '');
        if (isset($meta['note'])) {
            $meta['note'] = mb_substr((string) $meta['note'], 0, 140);
        }
        if ($coins !== null) {
            $meta['coins'] = (int) $coins;
        }
        if ($type === 'request_responded' && $request->filled('action')) {
            $meta['action'] = mb_substr((string) $request->input('action'), 0, 20);
        }

        WalletActivity::record($type, [
            'user_id' => $user->id,
            'actor_id' => $user->id,
            'status' => $failed ? 'failed' : 'success',
            'amount' => $amount,
            'counterparty_id' => $counterparty !== null ? (int) $counterparty : null,
            'reference' => $this->reference($request),
            'message' => $message,
            'meta' => $meta ?: null,
        ]);
    }

    /** @return array{0: bool, 1: ?string} */
    private function outcome(Request $request, Response $response): array
    {
        $status = $response->getStatusCode();

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $message = is_array($data) ? ($this->firstError($data['errors'] ?? null) ?? ($data['error'] ?? $data['message'] ?? null)) : null;
            $failed = $status >= 400 || (is_array($data) && (($data['success'] ?? null) === false || ($data['status'] ?? null) === false));

            return [$failed, $failed && is_string($message) ? $message : null];
        }

        if ($status >= 400) {
            return [true, 'HTTP ' . $status];
        }

        // Inertia/classic forms: validation or business errors are flashed to the session during THIS request.
        if ($request->hasSession()) {
            $session = $request->session();
            $fresh = (array) $session->get('_flash.new', []);
            $errors = $session->get('errors');
            if (in_array('errors', $fresh, true) && $errors instanceof ViewErrorBag && $errors->any()) {
                return [true, $errors->first()];
            }
            if (in_array('error', $fresh, true) && ($flash = $session->get('error'))) {
                return [true, is_string($flash) ? $flash : 'Failed'];
            }
        }

        return [false, null];
    }

    private function firstError(mixed $errors): ?string
    {
        if (! is_array($errors)) {
            return null;
        }
        $first = Arr::first($errors);

        return is_array($first) ? (string) Arr::first($first) : (is_string($first) ? $first : null);
    }

    private function firstNumeric(array $input, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($input[$key]) && is_numeric($input[$key])) {
                return (float) $input[$key];
            }
        }

        return null;
    }

    private function reference(Request $request): ?string
    {
        $params = $request->route()?->parameters() ?? [];
        $parts = [];
        foreach ($params as $name => $value) {
            $id = is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : (is_scalar($value) ? $value : null);
            if ($id !== null) {
                $parts[] = $name . '#' . $id;
            }
        }

        return $parts ? implode(' ', $parts) : null;
    }
}
