<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Asue;
use App\Models\EventFeeSetting;
use App\Models\LinkUpEvent;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketSale;
use App\Models\TicketSaleAddon;
use App\DTOs\WalletRechargeDTO;
use App\Actions\InitiateWalletRechargeAction;
use App\DTOs\WalletPaymentSuccessDTO;
use App\Actions\CompleteWalletRechargeAction;
use App\DTOs\CoinRechargeDTO;
use App\DTOs\CoinPaymentSuccessDTO;
use App\Actions\InitiateCoinRechargeAction;
use App\Actions\CompleteCoinRechargeAction;
use App\Actions\StoreUserBankDetailsAction;
use App\Actions\InitiateBankWithdrawalAction;
use App\Actions\CancelBankWithdrawalAction;
use App\DTOs\BankWithdrawalDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserBankAccount;
use App\Models\UserMoneyRequest;
use App\Models\UserWalletKyc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use O21\LaravelWallet\Models\Custodian;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use App\Http\Requests\AsueStoreRequest;
use App\Actions\AsueAction;
use App\DTOs\SimulateAsueCycleDTO;
use App\DTOs\SimulateAsueAcceptDTO;
use App\Actions\SimulateAsueCycleAction;
use App\Actions\SimulateAsueAcceptAction;
use App\Helpers\UserBankAccountHelper;
use App\Services\AsueService;
use App\Services\TaxRateService;
use App\Services\WalletEmailService;

class WalletController extends Controller
{

    public function withdrawalSettings(Request $request)
    {
        $feePercentRaw = env('BANK_PROCESSING_FEE_PERCENT', 5);
        $feePercent = is_numeric($feePercentRaw) ? (float) $feePercentRaw : 5.0;

        if (!is_finite($feePercent) || $feePercent < 0) {
            $feePercent = 5.0;
        }

        return response()->json([
            'bankProcessingFeePercent' => $feePercent,
        ]);
    }

    private function filterFeeBreakdownByTicketType($ticket, ?array $feeBreakdown): ?array
    {
        if (!$feeBreakdown || !$ticket) {
            return $feeBreakdown;
        }

        $type = strtolower(trim((string)($ticket->type ?? '')));
        $isCookoutType = str_contains($type, 'Cookouts/Food') || str_contains($type, 'Cookouts');

        if ($isCookoutType) {
            return [
                'cookout_fee_pct_rate' => $feeBreakdown['cookout_fee_pct_rate'] ?? 0,
                'cookout_fee_fixed_rate' => $feeBreakdown['cookout_fee_fixed_rate'] ?? 0,
                'cookout_fee_amount' => $feeBreakdown['cookout_fee_amount'] ?? 0,
            ];
        }

        return [
            'service_fee_pct_rate' => $feeBreakdown['service_fee_pct_rate'] ?? 0,
            'service_fee_pct_amount' => $feeBreakdown['service_fee_pct_amount'] ?? 0,
            'service_fee_fixed' => $feeBreakdown['service_fee_fixed'] ?? 0,
            'processing_fee_pct_rate' => $feeBreakdown['processing_fee_pct_rate'] ?? 0,
            'processing_fee_pct_amount' => $feeBreakdown['processing_fee_pct_amount'] ?? 0,
            'processing_fee_fixed' => $feeBreakdown['processing_fee_fixed'] ?? 0,
        ];
    }

    public function getBankAccounts(Request $request)
    {
        $userId = Auth::id();
        $bankAccounts = UserBankAccount::where('user_id', $userId)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'account_number' => $account->account_number,
                    'routing_number' => $account->routing_number,
                    'paypal_id' => $account->paypal_id,
                    'is_default' => $account->is_default,
                ];
            });

        return response()->json([
            'bank_accounts' => $bankAccounts,
        ]);
    }

    public function storeBankDetails(Request $request)
    {
        $userId = (int) Auth::id();
        if ($userId <= 0) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'paypal_id' => 'nullable|email:rfc,dns|max:255',
            'banks' => 'nullable|array|max:10',
            'banks.*.bank_name' => 'nullable|string|max:255',
            'banks.*.name' => 'nullable|string|max:255',
            'banks.*.account_number' => 'nullable|string|max:255',
            'banks.*.accountNumber' => 'nullable|string|max:255',
            'banks.*.routing_number' => 'nullable|string|max:255',
            'banks.*.routingNumber' => 'nullable|string|max:255',
            'banks.*.account_type' => 'nullable|in:checking,savings|max:20',
            'banks.*.accountType' => 'nullable|in:checking,savings|max:20',
            'banks.*.is_default' => 'nullable|boolean',
            'banks.*.isDefault' => 'nullable|boolean',
        ]);

        $dto = new \App\DTOs\UserBankDetailsDTO(
            $userId,
            $validated['paypal_id'] ?? null,
            $validated['banks'] ?? [],
        );

        app(StoreUserBankDetailsAction::class)->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Bank details saved successfully.',
        ]);
    }

    public function initiateBankWithdrawal(Request $request, InitiateBankWithdrawalAction $action)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'bankIndex' => 'required|integer|min:0',
        ]);

        $userId = (int) Auth::id();
        $amount = (float) $request->amount;
        $bankIndex = (int) $request->bankIndex;

        $dto = new BankWithdrawalDTO(
            $userId,
            $amount,
            $bankIndex,
        );

        $withdrawal = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully. It is pending processing.',
            'withdrawal_id' => $withdrawal->id,
        ]);
    }

    public function cancelBankWithdrawal($id, CancelBankWithdrawalAction $action)
    {
        $userId = (int) Auth::id();
        if ($userId <= 0) {
            return redirect()->route('login');
        }

        try {
            $withdrawal = $action->execute((int) $id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal cancelled successfully. The amount has been refunded to your wallet.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $balance = $user->balance('USD')->value->get();

        $transactions = $user->transactions()
            ->latest()
            ->get();

        $runningBalance = $balance;

        $transactions = $transactions->map(function ($tx) use (&$runningBalance, $user) {
            $isPositive = $tx->to_id == $user->id;

            $tx->is_positive = $isPositive;

            $tx->running_balance = $runningBalance;

            if ($isPositive) {
                $runningBalance -= $tx->amount;
            } else {
                $runningBalance += $tx->amount;
            }

            if (!empty($tx->meta) && isset($tx->meta['recipient_name'])) {
                $tx->recipient = [
                    'name' => $tx->meta['recipient_name'],
                    'linkup_id' => $tx->meta['recipient_linkup_id'] ?? '',
                ];
            }
            return $tx;
        });

        $users = User::contactForWallet()->get();
        $contacts = UserContact::where('user_id', $user->id)->with('contactUser')->get();
        $sentRequests = UserMoneyRequest::with('recipient', 'requester')->where('requester_id', $user->id)->orWhere('recipient_id', $user->id)->latest()->get();
        $subscriptions = $user->subscribed()->where('stripe_status', 'complete')->latest()->get();

        $activeAsue = Asue::whereIn('status', ['active', 'ACTIVE', 'inviting', 'INVITING'])
            ->whereNotNull('asue_unique_code')
            ->whereHas('invitedUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['invitedUsers' => function ($q) {
                $q->orderBy('asue_invites.position', 'asc');
            }])->latest()->first();

        $kycStatus = UserWalletKyc::where('user_id', $user->id)->first()?->status ?? 'not_submitted';

        $bankAccounts = UserBankAccount::where('user_id', $user->id)->get();
        $paypalId = $bankAccounts->first()?->paypal_id ?? null;
        $banks = $bankAccounts->map(function ($account) {
            return [
                'bank_name' => UserBankAccountHelper::safeDecrypt(fn() => $account->bank_name),
                'account_number' => UserBankAccountHelper::safeDecrypt(fn() => $account->account_number),
                'routing_number' => UserBankAccountHelper::safeDecrypt(fn() => $account->routing_number),
                'account_type' => UserBankAccountHelper::safeDecrypt(fn() => $account->account_type),
                'is_default' => $account->is_default ?? false,
            ];
        })->filter(function ($bank) {
            return !empty($bank['bank_name']) || !empty($bank['account_number']) || !empty($bank['routing_number']);
        })->values()->toArray();

        $bankWithdrawals = \App\Models\BankWithdrawal::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'fee_percent' => $withdrawal->fee_percent,
                    'fee_amount' => $withdrawal->fee_amount,
                    'payout_amount' => $withdrawal->payout_amount,
                    'bank_name' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->bank_name),
                    'account_number' => UserBankAccountHelper::safeDecrypt(fn() => $withdrawal->account_number),
                    'status' => $withdrawal->status,
                    'failure_reason' => $withdrawal->failure_reason,
                    'created_at' => $withdrawal->created_at->toISOString(),
                    'processed_at' => $withdrawal->processed_at?->toISOString(),
                ];
            });

        return Inertia::render('User/UserWallet/Index', [
            'currentBalance' => $balance,
            'transactions' => $transactions,
            'users' => $users,
            'contacts' => $contacts,
            'sentRequests' => $sentRequests,
            'coinBalance' => $user->coins ?? 0,
            'subscriptions' => $subscriptions,
            'activeAsue' => $activeAsue,
            'kycStatus' => $kycStatus,
            'bankDetails' => [
                'paypal_id' => $paypalId,
                'banks' => $banks,
            ],
            'bankWithdrawals' => $bankWithdrawals,
        ]);
    }

    public function addAmoutPage()
    {
        $user = auth()->user();
        // dd($user);
        $balance = $user->balance('USD')->value->get();
        return Inertia::render('User/Wallet/SelectAmount', [
            'currentBalance' => $balance
        ]);
    }

    public function submitAmountToWallet(Request $request, InitiateWalletRechargeAction $action)
    {
        $request->validate([
            'selectedAmount' => 'required|integer|min:10|max:1000',
        ]);

        $userId = Auth::id();
        $amount = (int) $request->selectedAmount;
        $redirectTo = $request->redirect_to;

        $dto = new WalletRechargeDTO($userId, $amount, $redirectTo);

        $url = $action->execute($dto);

        return Inertia::location($url);
    }
    public function addCoinPage()
    {
        $user = Auth::user();
        $coin = $user->coins ?? 0;
        return Inertia::render('User/Wallet/SelectCoin', [
            'currentCoin' => $coin
        ]);
    }

    public function submitCoinToWallet(Request $request, InitiateCoinRechargeAction $action)
    {
        $request->validate([
            'selectedCoin' => 'required|integer|min:1|max:10000',
            'coinCount' => 'required|integer|min:1|max:1000',
        ]);

        $userId = Auth::id();
        $priceUsd = (int) $request->selectedCoin;
        $coinCount = (int) $request->coinCount;
        $redirectTo = $request->redirect_to;

        $dto = new CoinRechargeDTO($userId, $priceUsd, $coinCount, $redirectTo);
        $url = $action->execute($dto);

        return Inertia::location($url);
    }
    public function oneTimePayCoinSuccess(Request $request, CompleteCoinRechargeAction $action, WalletEmailService $emailService)
    {
        try {
            $userId = (int) Auth::id();

            if ($userId <= 0) {
                return redirect()->route('login');
            }

            $dto = new CoinPaymentSuccessDTO(
                $request->session_id,
                $userId,
                0
            );

            $result = $action->execute($dto);

            // Send email notification
            if ($result && isset($result['amount']) && isset($result['coins']) && isset($result['user_id'])) {
                $user = User::find($result['user_id']);
                if ($user) {
                    $emailService->sendCoinsRechargedEmail($user, $result['amount'], $result['coins']);
                }
            }

            if ($request->redirect_to) {
                return redirect($request->redirect_to)->with('success', 'Coins added successfully!');
            }

            return redirect()->route('frontend.user.wallet')->with('success', 'Coins added successfully!');
        } catch (\Exception $e) {
            logger('Stripe API Error: ' . $e->getMessage());
            return redirect('/subscription-error')->with('error', $e->getMessage());
        }
    }

    public function oneTimePaySuccess(Request $request, CompleteWalletRechargeAction $action, WalletEmailService $emailService)
    {
        try {
            $dto = new WalletPaymentSuccessDTO(
                $request->session_id
            );

            $result = $action->execute($dto);

            // Send email notification
            if ($result && isset($result['amount']) && isset($result['user_id'])) {
                $user = User::find($result['user_id']);
                if ($user) {
                    $emailService->sendWalletRechargedEmail($user, $result['amount']);
                }
            }

            if ($request->redirect_to) {
                return redirect($request->redirect_to)->with('success', 'Funds added successfully!');
            }

            return redirect()->route('frontend.user.wallet')->with('success', 'Funds added successfully!');
        } catch (\Exception $e) {
            logger('Stripe API Error: ' . $e->getMessage());
            return redirect('/subscription-error')->with('error', $e->getMessage());
        }
    }

    public function buyTicket(Request $request)
    {
        $cart = $request->validate([
            'items' => 'array',
            'items.*.ticketId' => 'integer',
            'items.*.qty' => 'integer|min:0',
            'items.*.addons' => 'array|nullable',
            'items.*.addons.*.addon_id' => 'sometimes|integer|nullable',
            'items.*.addons.*.name' => 'sometimes|string|nullable',
            'items.*.addons.*.quantity' => 'integer',
            'items.*.addons.*.category' => 'sometimes|string|nullable',
            'items.*.addons.*.section' => 'sometimes|string|nullable',
            'items.*.cookout' => 'sometimes|array|nullable',
            'items.*.cookout.includedProtein' => 'sometimes|string|nullable',
            'items.*.cookout.proteins' => 'sometimes|array|nullable',
            'items.*.cookout.proteins.*.name' => 'required_with:items.*.cookout.proteins|string',
            'items.*.cookout.proteins.*.qty' => 'required_with:items.*.cookout.proteins|integer|min:0',
            'items.*.cookout.drinks' => 'sometimes|array|nullable',
            'items.*.cookout.drinks.*.name' => 'required_with:items.*.cookout.drinks|string',
            'items.*.cookout.drinks.*.qty' => 'required_with:items.*.cookout.drinks|integer|min:0',
            'items.*.cookout.manualAddons' => 'sometimes|array|nullable',
            'items.*.cookout.manualAddons.*.name' => 'required_with:items.*.cookout.manualAddons|string',
            'items.*.cookout.manualAddons.*.qty' => 'required_with:items.*.cookout.manualAddons|integer|min:0',
            'items.*.wellness' => 'sometimes|array|nullable',
            'items.*.wellness.selectedSlot' => 'sometimes|string|nullable',
            'items.*.wellness.selectedSlotDate' => 'sometimes|string|nullable',
            'items.*.wellness.holdExpiresAt' => 'sometimes|numeric|nullable',
            'items.*.wellness.includedService' => 'sometimes|string|nullable',
            'items.*.wellness.services' => 'sometimes|array|nullable',
            'items.*.wellness.services.*.name' => 'required_with:items.*.wellness.services|string',
            'items.*.wellness.services.*.qty' => 'required_with:items.*.wellness.services|integer|min:0',
            'items.*.wellness.manualAddons' => 'sometimes|array|nullable',
            'items.*.wellness.manualAddons.*.name' => 'required_with:items.*.wellness.manualAddons|string',
            'items.*.wellness.manualAddons.*.qty' => 'required_with:items.*.wellness.manualAddons|integer|min:0',
            'appliedCoupons' => 'array',
            'appliedCoupons.*' => 'string',
        ]);
        // Get event fee settings (by event, fallback to first)
        $ticketIds = array_column($cart['items'], 'ticketId');
        $tickets = Ticket::whereIn('id', $ticketIds)->with(['drinkPackage', 'extraSetting'])->get();
        $eventFeeSettings = null;
        if ($tickets->count() > 0 && \Illuminate\Support\Facades\Schema::hasColumn('event_fee_settings', 'link_up_event_id')) {
            $eventFeeSettings = EventFeeSetting::where('link_up_event_id', $tickets->first()->event_id)->first();
        }
        if (!$eventFeeSettings) {
            $eventFeeSettings = EventFeeSetting::first();
        }

        if ($tickets->count() !== count($ticketIds)) {
            return back()->withErrors(['cart' => 'One or more tickets are invalid']);
        }

        $total = 0;
        $cartSubtotal = 0;
        $ticketOnlySubtotal = 0; // tickets only, no tables/drinks
        $vipTicketSubtotal = 0;
        $standardTicketSubtotal = 0;
        $totalServiceFeePercent = 0;
        $totalServiceFeeFixed = 0;
        $totalProcessingFeePercent = 0;
        $totalProcessingFeeFixed = 0;
        $totalDrinkFees = 0;
        $totalBottleFees = 0;
        $totalVipFees = 0;
        $totalMobileFee = 0;
        $eventTaxRate = 0;
        $shouldTaxFees = false;
        $hasNoSalesTax = false;

        $event = LinkUpEvent::with('eventDetails')->find($tickets->first()->event_id);
        $taxIncluded = $event?->eventDetails?->tax_included === 'yes';

        foreach ($cart['items'] as &$item) {
            $ticket = $tickets->firstWhere('id', $item['ticketId']);
            if (!$ticket) {
                return back()->withErrors(["{$item['ticketId']}" => 'Ticket not found']);
            }

            if ($ticket->quantity < $item['qty']) {
                return back()->withErrors(["{$ticket->name}" => 'Ticket is sold out or insufficient quantity']);
            }

            // Verify event exists
            $event = LinkUpEvent::find($ticket->event_id);
            if (!$event) {
                return back()->withErrors(['cart' => 'Event not found for ticket']);
            }

            $item['ticket'] = $ticket;
            // Apply promo_price discount to base ticket price (min 0)
            $base = floatval($ticket->price ?? 0);
            $promo = floatval($ticket->promo_price ?? 0);
            $discounted = $base - (is_nan($promo) ? 0 : $promo);
            if ($discounted < 0) {
                $discounted = 0;
            }
            $item['price'] = $discounted;
            $item['fee'] = 0;
            $item['tax'] = 0;

            $cookoutTotal = 0;
            $cookoutAddonsJson = [];
            $cookoutConfig = $ticket->cookout;
            $cookoutInput = $item['cookout'] ?? null;
            if (is_array($cookoutConfig) && (($cookoutConfig['includeFood'] ?? null) === 'yes') && is_array($cookoutInput)) {
                $allowedProteins = [];
                foreach (($cookoutConfig['proteins'] ?? []) as $p) {
                    if (!is_array($p)) {
                        continue;
                    }
                    if (($p['mode'] ?? null) !== 'addon') {
                        continue;
                    }
                    $name = trim((string) ($p['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = strtolower($name);
                    $allowedProteins[$key] = [
                        'name' => $name,
                        'price' => floatval($p['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $p) ? intval($p['qty'] ?? 0) : null,
                    ];
                }

                $allowedManual = [];
                foreach (($cookoutConfig['manualAddons'] ?? []) as $a) {
                    if (!is_array($a)) {
                        continue;
                    }
                    $name = trim((string) ($a['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $key = strtolower($name);
                    $allowedManual[$key] = [
                        'name' => $name,
                        'price' => floatval($a['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $a) ? intval($a['qty'] ?? 0) : null,
                    ];
                }

                foreach (($cookoutInput['proteins'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedProteins[$key])) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon selected']);
                    }
                    $maxQty = $allowedProteins[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon quantity']);
                    }
                    $unitPrice = floatval($allowedProteins[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $allowedProteins[$key]['name'],
                        'category' => 'cookout_protein',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }

                foreach (($cookoutInput['manualAddons'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedManual[$key])) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon selected']);
                    }
                    $maxQty = $allowedManual[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['cookout' => 'Invalid cookout addon quantity']);
                    }
                    $unitPrice = floatval($allowedManual[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $allowedManual[$key]['name'],
                        'category' => 'cookout_extra',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }

                // Process cookout drinks
                foreach (($cookoutInput['drinks'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    // For drinks, we don't need to validate against a config - they're added directly
                    $unitPrice = 0; // Cookout drinks might have prices in the config, but for now assume 0
                    $lineTotal = $unitPrice * $qty;
                    $cookoutTotal += $lineTotal;
                    $cookoutAddonsJson[] = [
                        'name' => $name,
                        'category' => 'cookout_drink',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }
            }
            $item['cookout_total'] = $cookoutTotal;
            $item['cookout_addons_json'] = $cookoutAddonsJson;

            // Process cookout selection (included protein and sides)
            $cookoutSelection = null;
            if (is_array($cookoutConfig) && (($cookoutConfig['includeFood'] ?? null) === 'yes') && is_array($cookoutInput)) {
                $cookoutSelection = [
                    'includedProtein' => $cookoutInput['includedProtein'] ?? null,
                    'includedSides' => $cookoutInput['includedSides'] ?? [],
                ];
            }
            $item['cookout_selection'] = $cookoutSelection;

            $wellnessTotal = 0;
            $wellnessAddonsJson = [];
            $wellnessConfig = $ticket->wellness;
            $wellnessInput = $item['wellness'] ?? null;
            $wellnessIncludedService = null;
            $wellnessIncludedServiceType = null;
            $wellnessSelectedSlot = null;

            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                $booking = $wellnessConfig['booking'] ?? null;
                if (is_array($booking) && (isset($booking['mode']) && $booking['mode'] === 'mobile')) {
                    $fee = floatval($booking['mobileFee'] ?? 0);
                    if ($fee > 0) {
                        $totalMobileFee += $fee * $item['qty'];
                    }
                }
            }

            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes') && is_array($wellnessInput)) {
                $wellnessSelectedSlot = $wellnessInput['selectedSlot'] ?? null;
                $wellnessSelectedSlotDate = $wellnessInput['selectedSlotDate'] ?? null;
                $wellnessHoldExpiresAt = $wellnessInput['holdExpiresAt'] ?? null;

                if ($wellnessSelectedSlot && $wellnessHoldExpiresAt) {
                    $now = round(microtime(true) * 1000);
                    if ($now > $wellnessHoldExpiresAt) {
                        $wellnessSelectedSlot = null;
                        $wellnessSelectedSlotDate = null;
                    }
                }

                if ($wellnessSelectedSlot && $wellnessSelectedSlotDate) {
                    $times = explode('-', $wellnessSelectedSlot);
                    if (count($times) === 2) {
                        $start = trim($times[0]) . ':00';
                        $end = trim($times[1]) . ':00';
                        $block = \App\Models\WellnessSlotBlock::where([
                            'ticket_id' => $ticket->id,
                            'slot_date' => $wellnessSelectedSlotDate,
                            'start_time' => $start,
                            'end_time' => $end,
                        ])->first();

                        if (!$block || $block->count <= 0) {
                            $wellnessSelectedSlot = null;
                            $wellnessSelectedSlotDate = null;
                        } else {
                            $item['wellness_slot_block_id'] = $block->id;
                        }
                    }
                }

                $includedName = trim((string) ($wellnessInput['includedService'] ?? ''));
                if ($includedName !== '') {
                    $allowedIncluded = [];
                    foreach (($wellnessConfig['services'] ?? []) as $s) {
                        if (!is_array($s)) {
                            continue;
                        }
                        if (($s['mode'] ?? null) === 'addon') {
                            continue;
                        }
                        $name = trim((string) ($s['name'] ?? ''));
                        if ($name === '') {
                            continue;
                        }
                        $allowedIncluded[strtolower($name)] = [
                            'name' => $name,
                            'type' => $s['type'] ?? null,
                        ];
                    }
                    $key = strtolower($includedName);
                    if (!isset($allowedIncluded[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid included wellness service selected']);
                    }
                    $wellnessIncludedService = $allowedIncluded[$key]['name'] ?? null;
                    $wellnessIncludedServiceType = $allowedIncluded[$key]['type'] ?? null;
                }

                $allowedServices = [];
                foreach (($wellnessConfig['services'] ?? []) as $s) {
                    if (!is_array($s)) {
                        continue;
                    }
                    if (($s['mode'] ?? null) !== 'addon') {
                        continue;
                    }
                    $name = trim((string) ($s['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $allowedServices[strtolower($name)] = [
                        'name' => $name,
                        'price' => floatval($s['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $s) ? intval($s['qty'] ?? 0) : null,
                    ];
                }

                $allowedManual = [];
                foreach (($wellnessConfig['manualAddons'] ?? []) as $a) {
                    if (!is_array($a)) {
                        continue;
                    }
                    $name = trim((string) ($a['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $allowedManual[strtolower($name)] = [
                        'name' => $name,
                        'price' => floatval($a['price'] ?? 0),
                        'maxQty' => array_key_exists('qty', $a) ? intval($a['qty'] ?? 0) : null,
                    ];
                }

                foreach (($wellnessInput['services'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedServices[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid wellness service selected']);
                    }
                    $maxQty = $allowedServices[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['wellness' => 'Invalid wellness service quantity']);
                    }
                    $unitPrice = floatval($allowedServices[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $wellnessTotal += $lineTotal;
                    $wellnessAddonsJson[] = [
                        'name' => $allowedServices[$key]['name'],
                        'category' => 'wellness_service',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }

                foreach (($wellnessInput['manualAddons'] ?? []) as $sel) {
                    if (!is_array($sel)) {
                        continue;
                    }
                    $name = trim((string) ($sel['name'] ?? ''));
                    $qty = intval($sel['qty'] ?? 0);
                    if ($name === '' || $qty <= 0) {
                        continue;
                    }
                    $key = strtolower($name);
                    if (!isset($allowedManual[$key])) {
                        return back()->withErrors(['wellness' => 'Invalid wellness addon selected']);
                    }
                    $maxQty = $allowedManual[$key]['maxQty'];
                    if ($maxQty !== null && $maxQty > 0 && $qty > $maxQty) {
                        return back()->withErrors(['wellness' => 'Invalid wellness addon quantity']);
                    }
                    $unitPrice = floatval($allowedManual[$key]['price']);
                    $lineTotal = $unitPrice * $qty;
                    $wellnessTotal += $lineTotal;
                    $wellnessAddonsJson[] = [
                        'name' => $allowedManual[$key]['name'],
                        'category' => 'wellness_manual',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                    ];
                }
            }

            $item['wellness_total'] = $wellnessTotal;
            if ($wellnessSelectedSlot) {
                $formattedDate = $wellnessSelectedSlotDate ? \Carbon\Carbon::parse($wellnessSelectedSlotDate)->format('M d, Y') : null;
                $wellnessAddonsJson[] = [
                    'name' => 'Selected Slot: ' . $wellnessSelectedSlot . ($formattedDate ? ' (' . $formattedDate . ')' : ''),
                    'category' => 'wellness_slot',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                    'slot' => $wellnessSelectedSlot,
                    'slot_date' => $wellnessSelectedSlotDate,
                ];
            }
            if ($wellnessIncludedService) {
                $wellnessAddonsJson[] = [
                    'name' => $wellnessIncludedService,
                    'category' => 'wellness_included_service',
                    'service_type' => $wellnessIncludedServiceType,
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                ];
            }
            $item['wellness_addons_json'] = $wellnessAddonsJson;
            $item['wellness_included_service'] = $wellnessIncludedService;

            // Calculate addon totals (tables and drinks) and prepare JSON data
            $tablesTotal = 0;
            $drinksTotal = 0;
            $drinkAddonsJson = [];
            $tableAddonsJson = [];
            // Determine package presence strictly (must have package, relation, and tables enabled)
            $hasPackage = !empty($ticket->package_id) && !empty($ticket->drinkPackage) && ($ticket->has_table === 'yes');
            $hasExplicitTableAddon = false;
            $packageTableCharge = 0;
            $tableSections = [];

            if (isset($item['addons']) && is_array($item['addons']) && count($item['addons']) > 0) {
                foreach ($item['addons'] as $addonData) {
                    // Table addons
                    if (isset($addonData['category']) && $addonData['category'] === 'table') {
                        // Track section for later JSON persistence
                        $secLabel = $addonData['section'] ?? '-';
                        $secQty = intval($addonData['quantity'] ?? 0);
                        if ($secQty > 0) {
                            $tableSections[] = ['section' => $secLabel, 'quantity' => $secQty];
                        }
                        // If package exists, ignore explicit table charge to prevent double charging
                        if ($hasPackage) {
                            $hasExplicitTableAddon = true;
                        }
                        $tableQty = intval($addonData['quantity'] ?? 0);
                        $tableUnit = floatval($ticket->table_price ?? 0);
                        $ticketUnit = floatval($item['price'] ?? 0);
                        if ($tableQty > 0 && $tableUnit > 0) {
                            $shouldCharge = ($tableUnit !== $ticketUnit);
                            $tableTotal = $shouldCharge ? ($tableUnit * $tableQty) : 0;
                            if ($shouldCharge) {
                                $tablesTotal += $tableTotal;
                            }
                            // Always add to JSON for display
                            $tableAddonsJson[] = [
                                'section' => $secLabel,
                                'capacity' => $ticket->table_capacity ?? 0,
                                'quantity' => $tableQty,
                                'unit_price' => $tableUnit,
                                'total_price' => $tableTotal,
                            ];
                        }
                        continue;
                    }

                    // Drink addons: resolve by id, then name+category, then name any category, then package, then legacy
                    $addon = null;
                    if (isset($addonData['addon_id'])) {
                        $addon = $this->findAddonInTicket($ticket, $addonData['addon_id']);
                    }
                    if (!$addon && isset($addonData['name']) && isset($addonData['category'])) {
                        $addon = $this->findAddonByNameAndCategory($ticket, $addonData['name'], $addonData['category']);
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findAddonByNameAnyCategory($ticket, $addonData['name']);
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findAddonInPackageByName($ticket, $addonData['name']);
                    }
                    if (!$addon && isset($addonData['addon_id'])) {
                        $addon = $this->findLegacyAddonById($ticket, intval($addonData['addon_id']));
                    }
                    if (!$addon && isset($addonData['name'])) {
                        $addon = $this->findLegacyAddonByName($ticket, $addonData['name']);
                    }
                    if (!$addon) {
                        return back()->withErrors(['addon' => 'Invalid addon selected']);
                    }
                    $drinkQty = intval($addonData['quantity'] ?? 0);
                    $drinkUnitPrice = isset($addon['cost']) ? floatval($addon['cost']) : floatval($addon['price'] ?? 0);
                    $drinkTotalPrice = $drinkUnitPrice * $drinkQty;
                    $drinksTotal += $drinkTotalPrice;

                    // Add to JSON array
                    $drinkAddonsJson[] = [
                        'addon_id' => $addon['id'] ?? null,
                        'name' => $addon['name'],
                        'category' => $addon['category'] ?? null,
                        'section' => $addonData['section'] ?? '-',
                        'quantity' => $drinkQty,
                        'unit_price' => $drinkUnitPrice,
                        'total_price' => $drinkTotalPrice,
                    ];

                    // Calculate drink fees for this addon
                    if ($eventFeeSettings) {
                        $drinkFeePct = floatval($eventFeeSettings->drink_fee_pct ?? 0);
                        $bottleFeePct = floatval($eventFeeSettings->bottle_fee_pct ?? 0);
                        $category = $addon['category'] ?? '';
                        $isBottleType = ($category === 'bottles');
                        $isRegularDrinkType = in_array($category, ['mixDrinks', 'wines', 'beers', 'waters', 'softDrinks']);
                        if ($isRegularDrinkType) {
                            $totalDrinkFees += $drinkTotalPrice * ($drinkFeePct / 100);
                        }
                        if ($isBottleType) {
                            $totalBottleFees += $drinkTotalPrice * ($bottleFeePct / 100);
                        }
                    }
                }
            }

            // Package-included table handling: persist sections and only charge when table_price differs from discounted ticket price
            if ($hasPackage && !$hasExplicitTableAddon) {
                $tableUnit = floatval($ticket->table_price ?? 0);
                // Use discounted base ticket price for comparison to avoid double-charging
                $ticketUnit = $discounted;
                $packageTableQty = intval($item['qty'] ?? 0);
                $shouldChargePackageTable = ($tableUnit > 0 && $tableUnit !== $ticketUnit);
                $packageTableCharge = $shouldChargePackageTable ? ($tableUnit * $packageTableQty) : 0;

                if ($tableUnit > 0 && $packageTableQty > 0) {
                    if (!empty($tableSections)) {
                        foreach ($tableSections as $ts) {
                            $qty = intval($ts['quantity'] ?? 0);
                            if ($qty <= 0) continue;
                            $tableAddonsJson[] = [
                                'section' => $ts['section'] ?? '-',
                                'capacity' => $ticket->table_capacity ?? 0,
                                'quantity' => $qty,
                                'unit_price' => $tableUnit,
                                'total_price' => $shouldChargePackageTable ? ($tableUnit * $qty) : 0,
                            ];
                        }
                    } else {
                        $tableAddonsJson[] = [
                            'section' => '-',
                            'capacity' => $ticket->table_capacity ?? 0,
                            'quantity' => $packageTableQty,
                            'unit_price' => $tableUnit,
                            'total_price' => $packageTableCharge,
                        ];
                    }
                }

                if ($shouldChargePackageTable) {
                    $tablesTotal += $packageTableCharge;
                }
            }

            $item['tables_total'] = $tablesTotal;
            $item['drinks_total'] = $drinksTotal;
            $item['addon_total'] = $tablesTotal + $drinksTotal + ($item['cookout_total'] ?? 0) + ($item['wellness_total'] ?? 0);
            $item['drink_addons_json'] = $drinkAddonsJson;
            $item['table_addons_json'] = $tableAddonsJson;

            $itemSubtotal = ($item['price'] * $item['qty']) + $item['addon_total'];
            $total += $itemSubtotal;
            $cartSubtotal += $itemSubtotal;

            $typeStr = strtolower(trim((string) ($ticket->type ?? '')));
            $ticketTypeStr = strtolower(trim((string) ($ticket->ticket_type ?? '')));
            $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                || str_contains($ticketTypeStr, 'drink')
                || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);

            if ($isDrinkOnlyTicket) {
                // Drink-only ticket types should not be included in service/processing/vip fee base.
                // They only pay drink/bottle fees.
                continue;
            }

            // Percentage fees apply to TICKETS ONLY (no tables/drinks)
            $ticketOnlySubtotal += ($item['price'] * $item['qty']);

            $isTableSelected = $hasExplicitTableAddon || $hasPackage;
            if ($isTableSelected) {
                $tableUnit = floatval($ticket->table_price ?? 0);
                $ticketUnit = $item['price'];
                if ($tableUnit > 0 && $tableUnit !== $ticketUnit) {
                    $vipTicketSubtotal += $tableUnit * $item['qty'];
                } else {
                    $vipTicketSubtotal += $ticketUnit * $item['qty'];
                }
            } else {
                $standardTicketSubtotal += ($item['price'] * $item['qty']);
            }
        }

        // Calculate 4-component fee structure per order (mirror StripeController)
        if ($standardTicketSubtotal > 0 && $eventFeeSettings) {
            $totalServiceFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->service_fee_pct ?? 0) / 100);
            $totalServiceFeeFixed = floatval($eventFeeSettings->service_fee_fixed ?? 0);
            $totalProcessingFeePercent = $standardTicketSubtotal * (floatval($eventFeeSettings->processing_fee_pct ?? 0) / 100);
            $totalProcessingFeeFixed = floatval($eventFeeSettings->processing_fee_fixed ?? 0);
        }

        if ($vipTicketSubtotal > 0 && $eventFeeSettings) {
            $vipPct = floatval($eventFeeSettings->vip_fee_pct ?? 0) / 100;
            $totalVipFees = $vipTicketSubtotal * $vipPct;
        }

        // Tax calculation logic (mirror StripeController)
        $taxRatePercent = $eventTaxRate;
        $taxService = new TaxRateService();
        foreach ($cart['items'] as $item) {
            $ticket = $item['ticket'] ?? null;
            if ($ticket && $ticket->event && $ticket->event->state) {
                $stateCode = strtoupper($ticket->event->state);
                $taxRulesResponse = $taxService->getStateTaxRules(
                    $stateCode,
                    $ticket->event->zip ?? null,
                    $ticket->event->city ?? null,
                    $ticket->event->country ?? 'US'
                );

                if ($taxRulesResponse['success']) {
                    if ($taxRulesResponse['no_state_sales_tax'] ?? false) {
                        $hasNoSalesTax = true;
                        break;
                    }
                    if ($taxRulesResponse['tax_fees'] ?? false) {
                        $shouldTaxFees = true;
                    }
                    if ($taxRatePercent <= 0) {
                        $taxRatePercent = $taxRulesResponse['rate'] * 100;
                    }
                }
            }
        }

        if (!$hasNoSalesTax && $eventFeeSettings && $taxIncluded) {
            $taxBase = $standardTicketSubtotal + $vipTicketSubtotal;
            $taxBase += $totalServiceFeePercent + $totalServiceFeeFixed
                + $totalProcessingFeePercent + $totalProcessingFeeFixed;
            $event_tax = $taxBase * ($taxRatePercent / 100);
        } else {
            $event_tax = 0;
        }

        $orderTotalFees = $totalServiceFeePercent + $totalServiceFeeFixed + $totalProcessingFeePercent + $totalProcessingFeeFixed + $totalDrinkFees + $totalBottleFees + $totalVipFees + $totalMobileFee + $event_tax;
        $total += $orderTotalFees;

        // Apply coupons ONLY to base ticket subtotal, cap at subtotal, then subtract from total
        $couponAmount = 0;
        if (isset($cart['appliedCoupons']) && is_array($cart['appliedCoupons']) && count($cart['appliedCoupons']) > 0) {
            // Compute base ticket subtotal
            $baseTicketSubtotal = 0;
            foreach ($cart['items'] as $ci) {
                $baseTicketSubtotal += (floatval($ci['price'] ?? 0) * intval($ci['qty'] ?? 0));
            }
            foreach ($cart['appliedCoupons'] as $couponCode) {
                $eventId = $cart['items'][0]['ticket']->event_id;
                $coupon = \App\Models\Coupon::where('code', $couponCode)->where('link_up_event_id', $eventId)->first();
                if ($coupon) {
                    if ($coupon->discount_type === 'percentage') {
                        $discount = ($coupon->discount / 100) * $baseTicketSubtotal;
                    } else {
                        $discount = floatval($coupon->discount);
                    }
                    // Cap discount to remaining base ticket subtotal
                    $discount = min($discount, $baseTicketSubtotal - $couponAmount);
                    if ($discount > 0) {
                        $couponAmount += $discount;
                    }
                }
            }
            if ($couponAmount > 0) {
                $total -= $couponAmount;
            }
        }

        if ($total < 0) {
            $total = 0;
        }

        // Wallet balance check after applying fees and coupons
        $walletBalance = auth()->user()->balance('USD')->value->get();
        if ($walletBalance < $total) {
            return back()->withError("You do not have sufficient balance in your wallet");
        }

        if ($total > 0) {
            try {
                transfer($total, 'USD')->from(auth()->user())->to(custodian('e_money'))->commit();
            } catch (\Exception $e) {
                return back()->withError("Transaction failed");
            }
        }

        // Generate one unique wallet order id to group all records in this purchase
        $walletOrderId = 'WALLET-' . (string) Str::uuid();

        $isFirstTicket = true;
        $feesSummary = null;
        $ticketSaleIds = [];
        foreach ($cart['items'] as $item) {
            $addonTotal = $item['addon_total'] ?? 0;
            $uniqueCode = Str::random(20);

            // Generate QR Code
            $options = new QROptions([
                'eccLevel' => EccLevel::L,
                'scale' => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'imageBase64' => false,
            ]);

            $qrcode = new QRCode($options);
            $qrCodeImage = $qrcode->render($uniqueCode);

            // Save QR code to storage
            $qrCodePath = storage_path('app/public/qr-codes/' . $uniqueCode . '.png');
            if (!file_exists(dirname($qrCodePath))) {
                mkdir(dirname($qrCodePath), 0755, true);
            }
            file_put_contents($qrCodePath, $qrCodeImage);

            // Calculate totals per ticket with order-level fees allocated to FIRST ticket only (mirror Stripe)
            $itemSubtotal = $item['price'] * $item['qty'];
            $firstTicketFees = $isFirstTicket ? (
                $totalServiceFeePercent + $totalServiceFeeFixed +
                $totalProcessingFeePercent + $totalProcessingFeeFixed +
                $totalDrinkFees + $totalBottleFees + $totalVipFees + $totalMobileFee + $event_tax -
                $couponAmount
            ) : 0;

            if ($isFirstTicket) {
                $feesSummary = [
                    'service_fee_total' => $totalServiceFeePercent + $totalServiceFeeFixed,
                    'processing_fee_total' => $totalProcessingFeePercent + $totalProcessingFeeFixed,
                    'drink_fee_total' => $totalDrinkFees + $totalBottleFees,
                    'vip_fee_total' => $totalVipFees,
                    'mobile_fee_total' => $totalMobileFee,
                    'tax_total' => $event_tax,
                    'discount_total' => $couponAmount,
                    'subtotal' => $cartSubtotal,
                    'total' => $total,
                ];
            }

            $itemTotal = $itemSubtotal + $item['addon_total'] + $firstTicketFees;

            // Build package payload if ticket has an attached package
            $hasSelectedTable = false;
            if (!empty($item['table_addons_json']) && is_array($item['table_addons_json'])) {
                foreach ($item['table_addons_json'] as $tableAddon) {
                    if (intval($tableAddon['quantity'] ?? 0) > 0) {
                        $hasSelectedTable = true;
                        break;
                    }
                }
            }

            $packagePayload = null;
            if ($hasSelectedTable && !empty($item['ticket']->package_id) && !empty($item['ticket']->drinkPackage)) {
                $pkg = $item['ticket']->drinkPackage;
                $packagePayload = [
                    'id' => $item['ticket']->package_id,
                    'name' => $pkg->name ?? null,
                    'bottles' => is_array($pkg->bottles) ? $pkg->bottles : (is_string($pkg->bottles) ? json_decode($pkg->bottles, true) : []),
                    'chasers' => is_array($pkg->chasers) ? $pkg->chasers : (is_string($pkg->chasers) ? json_decode($pkg->chasers, true) : []),
                    'waters' => is_array($pkg->waters) ? $pkg->waters : (is_string($pkg->waters) ? json_decode($pkg->waters, true) : []),
                    'notes' => $pkg->notes ?? null,
                ];
            }

            $dbTicketName = $item['ticket']->name;
            $wellnessConfig = $item['ticket']->wellness ? (is_string($item['ticket']->wellness) ? json_decode($item['ticket']->wellness, true) : $item['ticket']->wellness) : null;
            if (is_array($wellnessConfig) && (($wellnessConfig['includeService'] ?? null) === 'yes')) {
                $dbTicketName .= ' (Wellness)';
            }

            $typeStr = strtolower(trim((string) ($item['ticket']->type ?? '')));
            $ticketTypeStr = strtolower(trim((string) ($item['ticket']->ticket_type ?? '')));
            $isDrinkOnlyTicket = str_contains($typeStr, 'drink')
                || str_contains($ticketTypeStr, 'drink')
                || (intval($item['qty'] ?? 0) <= 0 && floatval($item['drinks_total'] ?? 0) > 0 && floatval($item['tables_total'] ?? 0) <= 0);

            $fullFeeBreakdown = ($eventFeeSettings) ? [
                'service_fee_pct_rate' => floatval($eventFeeSettings->service_fee_pct ?? 0),
                'service_fee_pct_amount' => $isFirstTicket ? ($totalServiceFeePercent ?? 0) : 0,
                'service_fee_fixed' => $isFirstTicket ? ($totalServiceFeeFixed ?? 0) : 0,
                'processing_fee_pct_rate' => floatval($eventFeeSettings->processing_fee_pct ?? 0),
                'processing_fee_pct_amount' => $isFirstTicket ? ($totalProcessingFeePercent ?? 0) : 0,
                'processing_fee_fixed' => $isFirstTicket ? ($totalProcessingFeeFixed ?? 0) : 0,
                'vip_package_fee_pct_rate' => floatval($eventFeeSettings->vip_fee_pct ?? 0),
                'vip_package_fee_amount' => $isFirstTicket ? ($totalVipFees ?? 0) : 0,
                'mobile_fee_amount' => $isFirstTicket ? ($totalMobileFee ?? 0) : 0,
                'cookout_fee_pct_rate' => floatval($eventFeeSettings->cookout_platform_fee_percent ?? 0),
                'cookout_fee_fixed_rate' => floatval($eventFeeSettings->cookout_platform_fee_fixed ?? 0),
                'cookout_fee_amount' => $isFirstTicket ? ($totalCookoutFees ?? 0) : 0,
            ] : null;

            $ticketFeeBreakdown = null;
            if ($eventFeeSettings) {
                if ($hasSelectedTable) {
                    $ticketFeeBreakdown = [
                        'vip_package_fee_pct_rate' => floatval($eventFeeSettings->vip_fee_pct ?? 0),
                        'vip_package_fee_amount' => $isFirstTicket ? ($totalVipFees ?? 0) : 0,
                    ];
                } elseif ($isDrinkOnlyTicket) {
                    $ticketFeeBreakdown = [
                        'drink_fee_pct_rate' => floatval($eventFeeSettings->drink_fee_pct ?? 0),
                        'drink_fee_amount' => $isFirstTicket ? ($totalDrinkFees ?? 0) : 0,
                        'bottle_fee_pct_rate' => floatval($eventFeeSettings->bottle_fee_pct ?? 0),
                        'bottle_fee_amount' => $isFirstTicket ? ($totalBottleFees ?? 0) : 0,
                    ];
                } else {
                    $ticketFeeBreakdown = $this->filterFeeBreakdownByTicketType($item['ticket'], $fullFeeBreakdown);
                    if (floatval($item['drinks_total'] ?? 0) > 0) {
                        $ticketFeeBreakdown = [
                            'drink_fee_pct_rate' => floatval($eventFeeSettings->drink_fee_pct ?? 0),
                            'drink_fee_amount' => $isFirstTicket ? ($totalDrinkFees ?? 0) : 0,
                            'bottle_fee_pct_rate' => floatval($eventFeeSettings->bottle_fee_pct ?? 0),
                            'bottle_fee_amount' => $isFirstTicket ? ($totalBottleFees ?? 0) : 0,
                        ];
                    }
                }
            }

            $ticketSale = TicketSale::create([
                'ticket_id' => $item['ticket']->id,
                'ticket_type' => $item['ticket']->type,
                'ticket_name' => $dbTicketName,
                'ticket_qrcode' => $uniqueCode,
                'ticket_qrcode_id' => $uniqueCode,
                'no_of_tickets' => $item['qty'],
                'user_id' => auth()->id(),
                'link_up_event_id' => $item['ticket']->event_id,
                'ticket_status' => 'confirmed',
                'payment_method' => 'cash',
                'pay_type' => 'Wallet',
                'stripe_id' => $walletOrderId,
                'fee' => ($isFirstTicket && !$isDrinkOnlyTicket) ? ($totalServiceFeePercent + $totalServiceFeeFixed) : 0,
                'discount' => $isFirstTicket ? $couponAmount : 0,
                'tax' => ($isFirstTicket && !$isDrinkOnlyTicket) ? ($totalProcessingFeePercent + $totalProcessingFeeFixed) : 0,
                'coupan_amount' => $isFirstTicket ? $couponAmount : 0,
                'drink_fees' => $isFirstTicket ? ($totalDrinkFees + $totalBottleFees) : 0,
                'event_tax' => ($isFirstTicket && !$isDrinkOnlyTicket) ? $event_tax : 0,
                'drinks_total' => $item['drinks_total'] ?? 0,
                'tables_total' => $item['tables_total'] ?? 0,
                'drink_addons' => !empty($item['drink_addons_json']) ? $item['drink_addons_json'] : null,
                'table_addons' => !empty($item['table_addons_json']) ? $item['table_addons_json'] : null,
                'cookout_included_protein' => !empty($item['cookout_selection']) && is_array($item['cookout_selection']) ? ($item['cookout_selection']['includedProtein'] ?? null) : null,
                'cookout_included_sides' => !empty($item['cookout_selection']) && is_array($item['cookout_selection']) ? ($item['cookout_selection']['includedSides'] ?? null) : null,
                'cookout_addons' => !empty($item['cookout_addons_json']) ? $item['cookout_addons_json'] : null,
                'cookout_total' => floatval($item['cookout_total'] ?? 0),
                'wellness_addons' => !empty($item['wellness_addons_json']) ? $item['wellness_addons_json'] : null,
                'wellness_total' => floatval($item['wellness_total'] ?? 0),
                'wellness_slot_block_id' => !empty($item['wellness_slot_block_id']) ? $item['wellness_slot_block_id'] : null,
                'package_id' => $packagePayload['id'] ?? null,
                'package_data' => $packagePayload ? $packagePayload : null,
                'sub_total' => $itemSubtotal,
                'total' => $itemTotal,
                'stripe_price' => $itemTotal,
                'web_qrcode' => 'qr-codes/' . $uniqueCode . '.png',
                'fee_breakdown' => $ticketFeeBreakdown,
            ]);

            $ticketSaleIds[] = $ticketSale->id;

            // Decrement wellness slot count if applicable
            if (!empty($item['wellness_slot_block_id'])) {
                $block = \App\Models\WellnessSlotBlock::find($item['wellness_slot_block_id']);
                if ($block && $block->count > 0) {
                    $block->decrement('count');
                }
            }

            // Save table addon purchases
            if (!empty($item['table_addons_json']) && is_array($item['table_addons_json'])) {
                foreach ($item['table_addons_json'] as $tableAddon) {
                    if (($tableAddon['total_price'] ?? 0) > 0 && ($tableAddon['quantity'] ?? 0) > 0) {
                        TicketSaleAddon::create([
                            'ticket_sale_id' => $ticketSale->id,
                            'addon_name' => "Table - " . ($tableAddon['section'] ?? '-'),
                            'quantity' => $tableAddon['quantity'],
                            'unit_price' => $tableAddon['unit_price'],
                            'total_price' => $tableAddon['total_price'],
                            'category' => 'table',
                        ]);
                    }
                }
            }

            // Save drink addon purchases
            if (!empty($item['drink_addons_json']) && is_array($item['drink_addons_json'])) {
                foreach ($item['drink_addons_json'] as $drinkAddon) {
                    if (($drinkAddon['quantity'] ?? 0) > 0) {
                        $drinkFeePctRate = floatval($eventFeeSettings->drink_fee_pct ?? 0);
                        $bottleFeePctRate = floatval($eventFeeSettings->bottle_fee_pct ?? 0);
                        $vipFeePctRate = floatval($eventFeeSettings->vip_fee_pct ?? 0);
                        $lineTotal = floatval($drinkAddon['total_price']);
                        $isBottleType = isset($drinkAddon['category']) && ($drinkAddon['category'] === 'bottles');
                        $lineDrinkFee = $lineTotal * ($drinkFeePctRate / 100);
                        $lineBottleFee = $isBottleType ? ($lineTotal * ($bottleFeePctRate / 100)) : 0;
                        $lineVipFee = 0;

                        $section = $drinkAddon['section'] ?? null;
                        if ($section === '-') $section = null;

                        TicketSaleAddon::create([
                            'ticket_sale_id' => $ticketSale->id,
                            'addon_name' => collect([$drinkAddon['name'], $section])->filter()->implode(' - '),
                            'quantity' => $drinkAddon['quantity'],
                            'unit_price' => $drinkAddon['unit_price'],
                            'total_price' => $drinkAddon['total_price'],
                            'category' => $drinkAddon['category'] ?? 'generic',
                            'addon_id' => $drinkAddon['addon_id'] ?? null,
                            'addon_fee_breakdown' => $eventFeeSettings ? [
                                'drink_fee_pct_rate' => $drinkFeePctRate,
                                'drink_fee_amount' => $lineDrinkFee,
                                'bottle_fee_pct_rate' => $bottleFeePctRate,
                                'bottle_fee_amount' => $lineBottleFee,
                                'vip_package_fee_pct_rate' => $vipFeePctRate,
                                'vip_package_fee_amount' => $lineVipFee,
                            ] : null,
                        ]);
                    }
                }
            }

            if (!empty($item['cookout_addons_json']) && is_array($item['cookout_addons_json'])) {
                foreach ($item['cookout_addons_json'] as $addon) {
                    $qty = intval($addon['quantity'] ?? 0);
                    $unit = floatval($addon['unit_price'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    TicketSaleAddon::create([
                        'ticket_sale_id' => $ticketSale->id,
                        'addon_name' => $addon['name'] ?? 'Cookout Add-on',
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'total_price' => $unit * $qty,
                        'category' => $addon['category'] ?? 'cookout',
                    ]);
                }
            }

            if (!empty($item['wellness_included_service'])) {
                TicketSaleAddon::create([
                    'ticket_sale_id' => $ticketSale->id,
                    'addon_name' => $item['wellness_included_service'] . ' (Included Service)',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_price' => 0,
                    'category' => 'wellness_included_service',
                ]);
            }

            if (!empty($item['wellness_addons_json']) && is_array($item['wellness_addons_json'])) {
                foreach ($item['wellness_addons_json'] as $addon) {
                    $qty = intval($addon['quantity'] ?? 0);
                    $unit = floatval($addon['unit_price'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    TicketSaleAddon::create([
                        'ticket_sale_id' => $ticketSale->id,
                        'addon_name' => $addon['name'] ?? 'Wellness Add-on',
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'total_price' => $unit * $qty,
                        'category' => $addon['category'] ?? 'wellness',
                    ]);
                }
            }

            // If ticket has package, persist package table addon entry only when no explicit table addon and qty > 0
            if ($hasPackage) {
                $unit = floatval($item['ticket']->table_price ?? 0);
                $packageTableQty = intval($item['qty'] ?? 0);
                if (!$hasExplicitTableAddon && $unit > 0 && $packageTableQty > 0) {
                    TicketSaleAddon::create([
                        'ticket_sale_id' => $ticketSale->id,
                        'addon_name' => 'Table - Package',
                        'quantity' => $packageTableQty,
                        'unit_price' => $unit,
                        'total_price' => $unit * $packageTableQty,
                        'category' => 'table',
                    ]);
                }
            }

            // Update inventory: tickets, tables, and drinks
            if ($item['qty'] > 0) {
                $item['ticket']->quantity = max(0, $item['ticket']->quantity - $item['qty']);
            }
            // Deduct tables (explicit or package)
            if (!empty($item['table_addons_json'])) {
                foreach ($item['table_addons_json'] as $t) {
                    $tableQty = intval($t['quantity'] ?? 0);
                    if ($tableQty > 0) {
                        $item['ticket']->table_capacity = max(0, ($item['ticket']->table_capacity ?? 0) - $tableQty);
                    }
                }
            }
            $item['ticket']->save();

            $isFirstTicket = false;
        }

        $ticketSales = TicketSale::whereIn('id', $ticketSaleIds)->get();
        $eventId = $ticketSales->first()?->link_up_event_id;
        $event = $eventId ? LinkUpEvent::find($eventId) : null;
        $user_data = Auth::user();

        $organizerUserId = null;
        if ($event?->organizer_id && User::whereKey($event->organizer_id)->exists()) {
            $organizerUserId = $event->organizer_id;
        }

        if (!$organizerUserId && $event?->organizer_id) {
            $organizerUserId = OrganizerProfile::whereKey($event->organizer_id)->value('user_id');
        }

        if (!$organizerUserId && $event?->user_id && User::whereKey($event->user_id)->exists()) {
            $organizerUserId = $event->user_id;
        }

        if ($organizerUserId && !User::whereKey($organizerUserId)->exists()) {
            $organizerUserId = null;
        }

        if ($event && $organizerUserId) {
            $totalTickets = (int) $ticketSales->sum('no_of_tickets');
            if ($totalTickets <= 0) {
                $totalTickets = (int) $ticketSales->count();
            }

            Notification::create([
                'title' => 'New ticket purchase',
                'message' => ($user_data?->name ?? 'Someone') . " purchased {$totalTickets} ticket(s) for \"{$event->title}\".",
                'send_by' => (string) (Auth::id() ?? ''),
                'user_id' => $organizerUserId,
                'type' => 'payment',
                'context' => 'ticket_purchase',
                'unread' => true,
                'avatar' => $user_data?->avatar ?? null,
                'metadata' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'ticket_sale_ids' => $ticketSaleIds,
                    'tickets_count' => $totalTickets,
                    'amount_paid' => (float) $ticketSales->sum('stripe_price'),
                    'payment_method' => 'wallet',
                ],
            ]);
        }

        return Inertia::render('User/Event/SuccessPurchase', [
            'stripe_id' => $walletOrderId,
            'fees_summary' => $feesSummary,
            'ticketSale' => TicketSale::where('stripe_id', $walletOrderId)->get(),
            'event' => $event,
            'app_url' => config('app.url'),
        ]);
    }

    public function coinSystemhead()
    {
        return Inertia::render('User/Wallet/Coin');
    }


    // search by linkup_id or name
    public function searchByLinkupId(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'search_type' => 'nullable|string|in:name,linkup_id,all',
            'type' => 'nullable|string|in:user,merchant,all',
        ]);

        $query = $request->input('query');
        $searchType = $request->input('search_type', 'all');
        $type = $request->input('type', 'all');

        // Remove ~ prefix if present for linkup_id search
        if (str_starts_with($query, '~')) {
            $query = substr($query, 1);
        }

        $usersQuery = User::contactForWallet();

        // Search by name, linkup_id, or both
        if ($searchType === 'name') {
            $usersQuery->where('name', 'LIKE', '%' . $query . '%');
        } elseif ($searchType === 'linkup_id') {
            $usersQuery->where('linkup_id', 'LIKE', '%' . $query . '%');
        } else { // all
            $usersQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('linkup_id', 'LIKE', '%' . $query . '%');
            });
        }

        // Filter by type if specified
        if ($type !== 'all') {
            $usersQuery->where('type', $type);
        }

        $users = $usersQuery->select('id', 'name', 'linkup_id', 'type', 'avatar', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'linkup_id' => $user->linkup_id,
                    'type' => $user->type ?? 'user',
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'display_tag' => '~' . $user->linkup_id,
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    // add the user into contact
    public function AddToContact(Request $request)
    {
        $request->validate([
            'contact_user_id' => 'required|exists:users,id',
            'name' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $contactUserId = $request->contact_user_id;
        $customName = $request->name;

        // Prevent duplicate
        $exists = UserContact::where('user_id', $userId)
            ->where('contact_user_id', $contactUserId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Already present',
                'error' => 'Contact already exists'
            ], 422);
        }

        $contactData = [
            'user_id' => $userId,
            'contact_user_id' => $contactUserId,
            'name' => $customName,
        ];

        UserContact::create($contactData);

        // Verify the contact was created correctly
        $createdContact = UserContact::where('user_id', $userId)
            ->where('contact_user_id', $contactUserId)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Contact added successfully'
        ]);
    }

    public function sendCoins(Request $request, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
        ]);

        $sender = $request->user();
        $recipient = User::findOrFail($validated['recipient_id']);
        $amount = $validated['amount'];

        // Check balance
        if ($sender->coins < $amount) {
            return back()->withErrors(['amount' => 'Insufficient coin balance.']);
        }

        // Use transaction for safety
        DB::transaction(function () use ($sender, $recipient, $amount, $emailService) {
            // Deduct from sender
            $sender->decrement('coins', $amount);
            // Add to recipient
            $recipient->increment('coins', $amount);
            // Create notification for recipient
            Notification::create([
                'title'    => 'You received a gift!',
                'message'  => "{$sender->name} has sent you {$amount} coins.",
                'send_by'  => $sender->id,
                'user_id'  => $recipient->id,
                'type'     => 'gift',
                'context'  => 'coins_transfer',
                'unread'   => true,
                'avatar'   => $sender->avatar ?? null,
                'metadata' => json_encode([
                    'amount'     => $amount,
                    'sender_id'  => $sender->id,
                    'sender_name' => $sender->name,
                ]),
            ]);

            // Send email notifications
            $emailService->sendCoinsSentEmail($sender, $recipient, $amount);
            $emailService->sendCoinsReceivedEmail($sender, $recipient, $amount);
        });

        return back()->withSuccess("You sent {$amount} coins to {$recipient->name}.");
    }

    // send the monay request
    public function SendMoneyRequest(Request $request, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipientId' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = (int) Auth::id();
        if ($senderId <= 0) {
            return redirect()->route('login');
        }

        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        $globalKey = "money_request:sender:{$senderId}";
        if (RateLimiter::tooManyAttempts($globalKey, 10)) {
            $seconds = RateLimiter::availableIn($globalKey);
            return back()->withErrors(['transaction' => "Too many requests. Please try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($globalKey, 60);

        $pairKey = "money_request:pair:{$senderId}:{$recipientId}";
        if (RateLimiter::tooManyAttempts($pairKey, 3)) {
            $seconds = RateLimiter::availableIn($pairKey);
            return back()->withErrors(['transaction' => "You have requested money from this user too many times. Please try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($pairKey, 60);

        $sender = User::find($senderId);
        $recipient = User::find($recipientId);

        $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);

        try {
            app(InitiateWalletRechargeAction::class)->requestMoneyFromUser($dto);

            // Send email notifications
            $emailService->sendMoneyRequestSentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyRequestReceivedEmail($sender, $recipient, $amount, $note);

            return back()->withSuccess('Money Request sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    // Accept / Decline request
    public function respond(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);
        $moneyRequest = UserMoneyRequest::with('recipient', 'requester')->find($id);
        // Only recipient can accept/decline
        if ($moneyRequest->recipient_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        if ($request->status == 'declined') {
            $moneyRequest->update([
                'status' => 'rejected',
            ]);
        } else {
            // Accepted → handle money transfer
            $payer = $moneyRequest->recipient;       // user paying
            $payee = $moneyRequest->requester;       // user receiving
            $amount = $moneyRequest->amount;

            // Get wallet balance
            $payerBalance = $payer->balance('USD')->get();

            if ($payerBalance < $amount) {
                return back()->withErrors(['Insufficient balance to accept this request.']);
            }

            try {
                DB::transaction(function () use ($payer, $payee, $amount, $moneyRequest) {
                    // Deduct from payer → system custodian
                    transfer($amount, 'USD')->from($payer)->to(Custodian::of('e_money'))->commit();
                    // Deposit to payee ← system custodian
                    deposit($amount, 'USD')->from(Custodian::of('e_money'))->to($payee)->overcharge()->commit();
                    // Update request status
                    $moneyRequest->update(['status' => 'accepted']);
                    // for notification
                    Notification::create([
                        'title'    => "{$payer->name} Accept your request!",
                        'message'  => "{$payer->name} Accept your {$amount} request!",
                        'send_by'  => $payer->id,
                        'user_id'  => $payee->id,
                        'type'     => 'payemnt',
                        'context'  => 'payment_context',
                        'unread'   => true,
                        'avatar'   => $payer->avatar ?? null,
                    ]);
                });
                return back()->withSuccess("Request accepted and {$amount} USD transferred successfully.");
            } catch (\Exception $e) {
                return back()->withErrors('Transaction failed: ' . $e->getMessage());
            }
            $moneyRequest->update([
                'status' => 'accepted',
            ]);
        }
        return back()->withSuccess('Request ' . $request->status . ' successfully.');
    }

    // Cancel request
    public function cancel($id)
    {
        $moneyRequest = UserMoneyRequest::find($id);
        // Only requester can cancel
        if ($moneyRequest->requester_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $moneyRequest->delete();
        return back()->withSuccess('Request cancelled successfully.');
    }


    public function kyc()
    {
        return Inertia::render('User/UserWallet/WalletKyc');
    }

    public function storeKyc(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'id_type' => 'required|string',
            'id_number' => 'required|string',
            'address' => 'required|string',
            'tax_id' => 'nullable|string',
            'terms_acknowledged' => 'required|accepted',
            'kyc_documents' => 'required|array|min:1',
            'kyc_documents.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $user = auth()->user();

        $documentPaths = [];
        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $file) {
                $path = $file->store('kyc-documents', 'public');
                $documentPaths[] = $path;
            }
        }

        UserWalletKyc::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, [
                'status' => 'pending',
                'kyc_documents' => $documentPaths,
            ])
        );

        return back()->with('success', 'KYC data submitted successfully.');
    }

    private function findAddonInTicket($ticket, $addonId)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }

        $categories = $ticket->drink_addons['items'];
        $idCounter = 1;

        foreach ($categories as $category => $items) {
            foreach ($items as $item) {
                if ($idCounter == $addonId) {
                    return [
                        'id' => $idCounter,
                        'name' => $item['name'],
                        'cost' => $item['cost'],
                        'qty' => $item['qty'],
                        'price' => floatval($item['cost']) / $item['qty'], // Per item price
                        'category' => $category,
                    ];
                }
                $idCounter++;
            }
        }

        return null;
    }

    private function findLegacyAddonById($ticket, int $id)
    {
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['id']) && intval($b['id']) === $id) {
                    return [
                        'name' => $b['name'] ?? 'Bottle',
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['id']) && intval($c['id']) === $id) {
                    return [
                        'name' => $c['name'] ?? 'Mixer',
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['id']) && intval($w['id']) === $id) {
                    return [
                        'name' => $w['name'] ?? 'Water',
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    private function findLegacyAddonByName($ticket, string $name)
    {
        $search = trim($name);
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    private function findAddonByNameAndCategory($ticket, $name, $categoryKey)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $items = $ticket->drink_addons['items'];
        if (!isset($items[$categoryKey]) || !is_array($items[$categoryKey])) {
            return null;
        }
        foreach ($items[$categoryKey] as $addon) {
            if (isset($addon['name']) && strcasecmp(trim($addon['name']), trim($name)) === 0) {
                return $addon;
            }
        }
        return null;
    }

    private function findAddonByNameAnyCategory($ticket, $name)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $search = trim($name);
        foreach ($ticket->drink_addons['items'] as $category => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $addon) {
                if (isset($addon['name']) && strcasecmp(trim($addon['name']), $search) === 0) {
                    if (!isset($addon['category'])) {
                        $addon['category'] = $category;
                    }
                    return $addon;
                }
            }
        }
        return null;
    }

    private function findAddonInPackageByName($ticket, $name)
    {
        if (empty($ticket->drinkPackage)) {
            return null;
        }
        $search = trim($name);
        $pkg = $ticket->drinkPackage;
        if (!empty($pkg->bottles) && is_array($pkg->bottles)) {
            foreach ($pkg->bottles as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->chasers) && is_array($pkg->chasers)) {
            foreach ($pkg->chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->waters) && is_array($pkg->waters)) {
            foreach ($pkg->waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    public function sendMoney(Request $request, InitiateWalletRechargeAction $action, WalletEmailService $emailService)
    {
        $validated = $request->validate([
            'recipientId' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1|max:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = (int) Auth::id();
        if ($senderId <= 0) {
            return redirect()->route('login');
        }

        $recipientId = (int) $validated['recipientId'];
        $amount = (int) $validated['amount'];
        $note = $validated['note'] ?? null;

        $sender = User::find($senderId);
        $recipient = User::find($recipientId);

        $dto = new WalletRechargeDTO($senderId, $amount, null, $recipientId, $note);

        try {
            $action->sendMoneyToUser($dto);

            // Send email notifications
            $emailService->sendMoneySentEmail($sender, $recipient, $amount, $note);
            $emailService->sendMoneyReceivedEmail($sender, $recipient, $amount, $note);

            return back()->withSuccess('Money sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }

    public function storeAsue(AsueStoreRequest $request, AsueAction $asueAction)
    {
        try {
            $asueAction->execute($request->validated());
            return back()->withSuccess('Asue created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['asue_creation' => $e->getMessage()]);
        }
    }

    public function acceptAsueParticipation(Asue $asue, AsueService $asueService)
    {
        try {
            $result = $asueService->acceptParticipation($asue, Auth::id());
            return back()->withSuccess($result['message']);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function simulateAsueCycle(Asue $asue, SimulateAsueCycleAction $action)
    {
        try {
            $dto = SimulateAsueCycleDTO::fromRequest($asue, Auth::id());
            $result = $action->execute($dto);

            return back()->withSuccess($result['message']);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function simulateAsueAccept(Asue $asue, SimulateAsueAcceptAction $action)
    {
        try {
            $dto = SimulateAsueAcceptDTO::fromRequest($asue, Auth::id());
            $result = $action->execute($dto);

            return back()->withSuccess($result['message']);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function moneyRequestList()
    {
        $user = Auth::user();
        $balance = $user->balance('USD')->value->get();
        $transactions = $user->receivedTransactions()->latest()->get();
        $users = User::where('type', 'user')
            ->whereNotNull('linkup_id')
            ->where('id', '!=', $user->id)
            ->select(['id', 'name', 'linkup_id'])
            ->get();

        $contacts = UserContact::where('user_id', $user->id)
            ->with(['contactUser:id,name,linkup_id'])
            ->get();

        $sentRequests = UserMoneyRequest::with([
            'recipient:id,name,linkup_id',
            'requester:id,name,linkup_id',
        ])
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->latest()
            ->get();
        $subscriptions = $user->subscribed()->where('stripe_status', 'complete')->latest()->get();

        $moneyRequests = UserMoneyRequest::with([
            'requester:id,name,linkup_id',
            'recipient:id,name,linkup_id',
        ])
            ->where(function ($q) use ($user) {
                $q->where('recipient_id', $user->id)
                    ->orWhere('requester_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/UserWallet/components/MoneyRequestList', [
            'moneyRequests' => $moneyRequests,
            'currentBalance' => $balance,
            'transactions' => $transactions,
            'users' => $users,
            'contacts' => $contacts,
            'sentRequests' => $sentRequests,
            'coinBalance' => $user->coins ?? 0,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function sendRequestedMoney(Request $request, $id, WalletEmailService $emailService)
    {
        $payerId = (int) Auth::id();
        if ($payerId <= 0) {
            return redirect()->route('login');
        }

        // Get money request details for email
        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (!$moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            app(InitiateWalletRechargeAction::class)->fulfillMoneyRequest((int) $id, $payerId);

            // Send email notifications
            $emailService->sendMoneyRequestAcceptedEmail($moneyRequest->recipient, $moneyRequest->requester, $moneyRequest->amount);
            $emailService->sendMoneyReceivedEmail($moneyRequest->recipient, $moneyRequest->requester, $moneyRequest->amount);

            return back()->withSuccess('Requested money sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function rejectMoneyRequest($id, WalletEmailService $emailService)
    {
        $recipientId = (int) Auth::id();
        if ($recipientId <= 0) {
            return redirect()->route('login');
        }

        // Get money request details for email
        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (!$moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            app(InitiateWalletRechargeAction::class)->rejectMoneyRequest((int) $id, $recipientId);

            // Send email notification
            $emailService->sendMoneyRequestRejectedEmail($moneyRequest->requester, $moneyRequest->recipient, $moneyRequest->amount);

            return back()->withSuccess('Money request rejected successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function cancelMoneyRequest($id, WalletEmailService $emailService)
    {
        $requesterId = (int) Auth::id();
        if ($requesterId <= 0) {
            return redirect()->route('login');
        }

        // Get money request details for email
        $moneyRequest = UserMoneyRequest::with(['requester', 'recipient'])->find($id);
        if (!$moneyRequest) {
            return back()->withErrors(['transaction' => 'Money request not found.']);
        }

        try {
            app(InitiateWalletRechargeAction::class)->cancelMoneyRequest((int) $id, $requesterId);

            // Send email notification
            $emailService->sendMoneyRequestCancelledEmail($moneyRequest->requester, $moneyRequest->recipient, $moneyRequest->amount);

            return back()->withSuccess('Money request cancelled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['transaction' => $e->getMessage()]);
        }
    }

    public function removeContact($id)
    {
        $loginUserId = (int) Auth::id();
        try {
            $deleted = DB::transaction(function () use ($id, $loginUserId) {
                return UserContact::where(function ($q) use ($id, $loginUserId) {
                    $q->where('user_id', $loginUserId)
                        ->where('contact_user_id', $id);
                })->orWhere(function ($q) use ($id, $loginUserId) {
                    $q->where('user_id', $id)
                        ->where('contact_user_id', $loginUserId);
                })->delete();
            });

            return response()->json([
                'success' => (bool) $deleted,
                'message' => $deleted ? 'Contact removed' : 'No contact found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
