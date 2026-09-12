<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\CaribbeanIsland;
use App\Models\CountryPhoneCode;
use App\Models\GiftPurchase;
use App\Models\Nationality;
use App\Models\Order;
use App\Models\SubscribedPlan;
use App\Models\TicketSale;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\ProfileTabServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function __construct(
        protected ProfileTabServices $service
    ) {}

    public function index(Request $request)
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })->where('type', 'user')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }


    public function create()
    {
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();
        return Inertia::render('admin/users/Create', [
            'nationalities' => $nationalityList,
            'caribbean_island' => $caribbeanIslandList,
            'phone_codes' => $phoneCodes
        ]);
    }

    public function show(User $user)
    {
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();
        $purchased_tickets = TicketSale::where('user_id', $user->id)->where('ticket_status', 'confirmed')->count();
        $cancelled_tickets = TicketSale::where('user_id', $user->id)->where('ticket_status', 'cancelled')->count();
        $market_place = Order::where('user_id', $user->id)->where('status', 'delivered')->count();
        $matches = UserMatch::where('user_id', $user->id)->count();
        $like_matches = UserMatch::where('user_id', $user->id)->where('status', 'like')->count();
        $gifts_purchased = GiftPurchase::where('user_id', $user->id)->count();
        $gifts_collected_amount = GiftPurchase::where('user_id', $user->id)->sum('total_coins');

        return Inertia::render('admin/users/view/Index', [
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
            'gifts_collected_amount' => $gifts_collected_amount
        ]);
    }

    public function store(UserRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make('12345678');
        if (isset($data['avatar']) && $request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $data['avatar'] = null;
        }

        User::create($data);
        return redirect()->route('admin.users.index')->with('message', 'User created successfully.');
    }


    public function edit(User $user)
    {
        $phoneCodes = CountryPhoneCode::select([
            'code as value',
            DB::raw("CONCAT(code, ', ', name) as label")
        ])->get();
        $nationalityList = Nationality::select(['name as value', 'name as label'])->get();
        $caribbeanIslandList = CaribbeanIsland::select(['name as value', 'name as label'])->get();

        return Inertia::render('admin/users/Edit', [
            'user' => $user,
            'nationalities' => $nationalityList,
            'caribbean_island' => $caribbeanIslandList,
            'phone_codes' => $phoneCodes
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->all();

        if (isset($data['avatar']) && $request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $data['avatar'] = $user->avatar;
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('message', 'User Updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->scanSignUser()->delete();
        $user->delete();
        return redirect()->back()->with('success', 'User successfully deleted.');
    }

    public function changeStatus(User $user, Request $request)
    {
        $user->update([
            'status' => (bool) $request->status
        ]);
        return response()->json(['message' => 'Status changed successfully']);
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
}
