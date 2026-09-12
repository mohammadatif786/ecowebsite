<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Nationality;
use Illuminate\Http\Request;
use App\Models\CaribbeanIsland;
use App\Models\CountryPhoneCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\GiftPurchase;
use App\Models\GiftCoins;
use App\Models\News;
use App\Models\Order;
use App\Models\SubscribedPlan;
use App\Models\TicketSale;
use App\Models\UserMatch;
use App\Services\ProfileTabServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use O21\LaravelWallet\Models\Custodian;
use App\Models\Transaction;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileTabServices $service
    ) {}

    private function repairWalletBalance(User $user, string $currency = 'USD'): void
    {
        $trackStatuses = config('wallet.balance.tracking.value', []);
        $trackStatuses = array_values(array_filter($trackStatuses));

        if (empty($trackStatuses)) {
            return;
        }

        $incoming = Transaction::query()
            ->where('to_id', $user->id)
            ->where('to_type', User::class)
            ->where('currency', $currency)
            ->whereIn('status', $trackStatuses)
            ->sum('received');

        $outgoing = Transaction::query()
            ->where('from_id', $user->id)
            ->where('from_type', User::class)
            ->where('currency', $currency)
            ->whereIn('status', $trackStatuses)
            ->sum('amount');

        $calculated = max(0, (float) $incoming - (float) $outgoing);

        DB::table('balances')->updateOrInsert(
            [
                'payable_id' => $user->id,
                'payable_type' => User::class,
                'currency' => $currency,
            ],
            [
                'value' => $calculated,
                'value_pending' => 0,
                'value_on_hold' => 0,
            ]
        );
    }

    public function edit(Request $request)
    {
        $user = Auth::user();
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();
        $purchased_tickets = TicketSale::with('checkins')->where('user_id', $user->id)->where('ticket_status', 'confirmed')->count();
        $cancelled_tickets = TicketSale::with('checkins')->where('user_id', $user->id)->where('ticket_status', 'cancelled')->count();
        $market_place = Order::where('user_id', $user->id)->where('status', 'delivered')->count();
        $matches = UserMatch::where('user_id', $user->id)->count();
        $like_matches = UserMatch::where('user_id', $user->id)->where('status', 'like')->count();
        $gifts_purchased = GiftPurchase::where('user_id', $user->id)->count();
        $gifts_sent = GiftCoins::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->get();
        $gifts_received = GiftCoins::with(['sender', 'receiver'])
            ->where('recieved_id', $user->id)
            ->get();
        $all_gifts = $gifts_sent->concat($gifts_received);

        $news = News::where('user_id', $user->id)->get();
        
        $this->repairWalletBalance($user, 'USD');

        // Get user's wallet balance
        $walletBalance = (float) ($user->balance('USD')->value->get() ?? 0);

        return Inertia::render('User/Profile/view/Index', [
            'user' => $user,
            'purchased_tickets' => $purchased_tickets,
            'cancelled_tickets' => $cancelled_tickets,
            'market_place' => $market_place,
            'matches' => $matches,
            'like_matches' => $like_matches,
            'nationalities' => $nationalityList,
            'caribbean_island' => $caribbeanIslandList,
            'phone_codes' => $phoneCodes,
            'gifts_collected' => [],
            'gifts_purchased' => $gifts_purchased,
            'gifts_sent' => $gifts_sent,
            'gifts_received' => $gifts_received,
            'all_gifts' => $all_gifts,
            'user_wallet_balance' => $walletBalance,
            'news' => $news
        ]);
    }

    public function editUser()
    {
        $user = Auth::user();
        $morePhotos = $user->more_photos;
        $caribbeanIslandList = CaribbeanIsland::select(['name', 'name as value'])->get();
        return Inertia::render('User/Profile/EditProfile', [
            'user' => $user,
            'UserMorePhotos' => $morePhotos,
            'caribbeanIsland' => $caribbeanIslandList,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $existingPhotos = is_array($user->more_photos) ? $user->more_photos : [];

        // Only keep the uploaded files in the validation array
        $uploadedPhotos = [];
        if ($request->hasFile('more_photos')) {
            foreach ($request->file('more_photos') as $index => $file) {
                if ($file && $file->isValid()) {
                    $uploadedPhotos[$index] = $file;
                }
            }
        }

        // Validate only the uploaded files
        $validated = $request->validate(
            array_merge(
                [
                    'first_name' => 'required|string|max:255',
                    'last_name'  => 'required|string|max:255',
                    'gender'     => 'required|in:male,female,other',
                    'birthday'   => 'nullable|date|before:today',
                    'email'      => 'required|email|max:255',
                    'phone_number' => 'nullable|string|max:20',
                    'country'    => 'required|string|max:255',
                    'state'      => 'nullable|string|max:255',
                    'city'       => 'nullable|string|max:255',
                    'avatar'     => 'nullable',
                    'interests'  => 'nullable',
                    'age_filter' => 'nullable',
                    'distance_filter' => 'nullable',
                    'link_me_with' => 'nullable',
                    'language'   => 'nullable',
                    'caribbean_interest' => 'nullable',
                    // allow client to specify which indices to remove
                    'removed_more_photos' => 'nullable|array',
                    'removed_more_photos.*' => 'integer',
                ],
                // Add uploaded photo rules dynamically
                collect($uploadedPhotos)->mapWithKeys(fn($_, $index) => [
                    "more_photos.$index" => 'image|mimes:jpeg,png,jpg|max:5120'
                ])->toArray()
            ),
            [
                'more_photos.*.max'   => 'Your uploaded image size is more than 5MB.',
                'more_photos.*.image' => 'Only image files are allowed.',
                'more_photos.*.mimes' => 'Only jpeg, jpg, and png formats are supported.',
            ]
        );

        // Handle avatar
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('profiles', 'public');
        } else {
            $validated['avatar'] = $user->avatar;
        }

        // Start with existing photos and process removals
        $validated['more_photos'] = [];
        $toRemove = collect($request->input('removed_more_photos', []))
            ->filter(fn($i) => is_numeric($i))
            ->map(fn($i) => (int) $i)
            ->values()
            ->all();

        // Remove and delete files for indices marked for removal
        foreach ($existingPhotos as $index => $photoPath) {
            if (in_array((int)$index, $toRemove, true)) {
                if ($photoPath && !str_starts_with($photoPath, 'http')) {
                    try {
                        Storage::disk('public')->delete($photoPath);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to delete removed profile photo', [
                            'user_id' => $user->id,
                            'path' => $photoPath,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                continue; // skip preserving this index
            }
            // keep existing if not replaced below
            $validated['more_photos'][$index] = $photoPath;
        }

        // Merge new uploads into the photo set (override by index)
        foreach ($uploadedPhotos as $index => $photo) {
            $validated['more_photos'][$index] = $photo->store('more_photos', 'public');
        }

        // Reindex
        $validated['more_photos'] = array_values(array_filter($validated['more_photos']));

        // If birthday provided, compute and set age
        if ($request->filled('birthday')) {
            $validated['birthday'] = $request->input('birthday');
            try {
                $validated['age'] = Carbon::parse($validated['birthday'])->age;
            } catch (\Throwable $e) {
                Log::warning('Invalid birthday provided when updating profile', [
                    'user_id' => $user->id,
                    'birthday' => $request->input('birthday'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Save using direct assignments to avoid mass-assignment issues
        $user->fill($validated);
        if (array_key_exists('birthday', $validated)) {
            $user->birthday = $validated['birthday'];
        }
        if (array_key_exists('age', $validated)) {
            $user->age = $validated['age'];
        }
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function HideProfile(Request $request)
    {
        $request->validate([
            'hide_profile' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->hide_profile = $request->hide_profile;
        $user->save();
        return redirect()->back()->with('success', 'Profile Updated successfully.');
    }

    // Fetch all tickets purchased by the user
    public function ticketTab(User $user)
    {
        return response()->json(
            $this->service->ticketTab($user),
            200
        );
    }

    // Fetch all marketplace orders placed by the user
    public function marketPlaceTab(User $user)
    {
        return response()->json(
            $this->service->marketPlaceTab($user),
            200
        );
    }

    // Get match statistics including likes sent, received, and mutual matches
    public function matchesTab(User $user)
    {
        return response()->json(
            $this->service->matchesTab($user),
            200
        );
    }

    // Retrieve active subscription, remaining days, and subscription history
    public function subscriptionTab(User $user)
    {
        return response()->json(
            $this->service->subscriptionTab($user),
            200
        );
    }

    // Cancel an active subscription plan
    public function cancelSubscription(SubscribedPlan $plan)
    {
        $this->service->cancelSubscription($plan);
        return redirect()->back()->with('success', 'Subscription Cancelled successfully.');
    }

    // Fetch coin payment history for the user
    public function LinkUpCoinTab(User $user)
    {
        return response()->json(
            $this->service->LinkUpCoinTab($user),
            200
        );
    }

    // Fetch organizer dashboard data including events, ticket sales, and payouts
    public function OrganizerTab(User $user)
    {
        return response()->json(
            $this->service->OrganizerTab($user),
            200
        );
    }

    // Fetch user all transaction data
    public function walletTab(User $user)
    {
        return response()->json(
            $this->service->walletTab($user),
            200
        );
    }

    //Fetach user all linkup live data
    public function linkUpLive(User $user)
    {
        return response()->json(
            $this->service->linkUpLIve($user),
            200
        );
    }

    public function newsTab(User $user)
    {
        return response()->json(
            $this->service->newsTab($user),
            200
        );
    }

    /**
     * Convert received gift coins to cash
     */
    public function convertToCash(Request $request)
    {
        $request->validate([
            'coins' => 'required|integer|min:1',
            'minimum_cashout' => 'sometimes|integer|min:1'
        ]);

        $user = Auth::user();
        $coinsToConvert = $request->input('coins');
        $minimumCashout = $request->input('minimum_cashout', 25);
        $coinRate = 0.01;
        $userPayoutRatio = 0.5;

        $totalReceivedCoins = GiftCoins::where('recieved_id', $user->id)
            ->whereNull('status')
            ->sum('coins');
        
        $grossCashValue = $coinsToConvert * $coinRate;
        $userCashValue = $grossCashValue * $userPayoutRatio;
        $platformCashValue = $grossCashValue - $userCashValue;

        if ($userCashValue < $minimumCashout) {
            return response()->json([
                'success' => false,
                'message' => "Minimum cash-out amount is $" . number_format($minimumCashout, 2) . ". You need $" . number_format($minimumCashout - $userCashValue, 2) . " more to convert."
            ], 400);
        }

        if ($coinsToConvert > $totalReceivedCoins) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough received coins to convert this amount.'
            ], 400);
        }

        $rawWalletBefore = $user->balance('USD')->value->get();
        $currentWalletBalance = (float) ($rawWalletBefore ?? 0);

        try {
            DB::beginTransaction();

            try {
                deposit($userCashValue, 'USD')->from(Custodian::of('e_money'))->to($user)->overcharge()->commit();
            } catch (\Exception $e) {
                throw new \Exception('Wallet operation failed: ' . $e->getMessage());
            }

            $user->refresh();

            $remainingCoinsToConvert = $coinsToConvert;
            
            $receivedGifts = GiftCoins::where('recieved_id', $user->id)
                ->whereNull('status')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($receivedGifts as $gift) {
                if ($remainingCoinsToConvert <= 0) break;
                
                $coinsToDeduct = min($gift->coins, $remainingCoinsToConvert);
                
                if ($coinsToDeduct >= $gift->coins) {
                    $gift->status = now();
                    $gift->save();
                    $remainingCoinsToConvert -= $gift->coins;
                } else {
                    $newGift = $gift->replicate();
                    $newGift->coins = $gift->coins - $coinsToDeduct;
                    $newGift->status = null;
                    $newGift->save();
                    
                    $gift->coins = $coinsToDeduct;
                    $gift->status = now();
                    $gift->save();
                    
                    $remainingCoinsToConvert = 0;
                }
            }

            DB::commit();

            // Get updated wallet balance
            $rawWalletAfter = $user->balance('USD')->value->get();
            $newWalletBalance = (float) ($rawWalletAfter ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Successfully converted ' . $coinsToConvert . ' coins to $' . number_format($userCashValue, 2),
                'data' => [
                    'coins_converted' => $coinsToConvert,
                    'gross_cash_value' => $grossCashValue,
                    'cash_value' => $userCashValue,
                    'platform_fee' => $platformCashValue,
                    'payout_ratio' => $userPayoutRatio,
                    'previous_wallet_balance' => $currentWalletBalance,
                    'new_wallet_balance' => $newWalletBalance,
                    'remaining_received_coins' => $totalReceivedCoins - $coinsToConvert
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? ('Conversion failed: ' . $e->getMessage())
                    : 'Conversion failed. Please try again later.'
            ], 500);
        }
    }
}
