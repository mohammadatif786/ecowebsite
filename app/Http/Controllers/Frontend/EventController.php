<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\FilterEventsAction;
use App\Actions\GetEventIndexDataAction;
use App\Actions\ShowEventAction;
use App\DTOs\EventFilterCriteria;
use App\DTOs\EventIndexFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFilterRequest;
use App\Http\Requests\EventIndexRequest;
use App\Mail\EventInvitationMail;
use App\Models\EventCategory;
use App\Models\Frontend\FavouriteEvent;
use App\Models\LinkUpEvent;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EventController extends Controller
{

    public function index(EventIndexRequest $request, GetEventIndexDataAction $action)
    {
        $filters = EventIndexFilters::fromRequest($request);

        return Inertia::render('User/Event/Index', $action->handle(Auth::user(), $filters));
    }


    public function filterEvents(EventFilterRequest $request, FilterEventsAction $action)
    {
        $criteria = EventFilterCriteria::fromRequest($request);

        return Inertia::render('User/Event/Index', $action->handle($criteria));
    }

    public function create()
    {
        $categories = EventCategory::all();
        return Inertia::render('User/Event/CreateEvent', [
            'categories' => $categories
        ]);
    }

    public function show(LinkUpEvent $event, ShowEventAction $action)
    {
        return Inertia::render('User/Event/Show', $action->handle($event));
    }


    public function getCurrency($value)
    {
        $countryValues = ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'CH', 'AE', 'SA', 'PK', 'IN', 'CN', 'JP', 'SG', 'MY', 'BS', 'ZA'];
        $available = in_array($value, $countryValues);
        return $available == true ? $value . "$" : $value;
    }

    public function eventTickets(LinkUpEvent $event)
    {
        $tickets = $event->tickets()->get();
        $otherEvents = LinkUpEvent::where('organizer_id', $event->organizer_id)->take(8)->get();

        // Get tax rules
        $taxService = new \App\Services\TaxRateService();
        $taxRules = $taxService->getStateTaxRules(
            strtoupper($event->state ?? ''),
            $event->zip ?? null,
            $event->city ?? null,
            $event->country ?? 'US'
        );

        $taxRate = $taxRules['success'] ? ($taxRules['rate'] * 100) : 0;
        $taxConfig = [
            'rate' => $taxRate,
            'tax_fees' => $taxRules['tax_fees'] ?? false,
            'no_state_sales_tax' => $taxRules['no_state_sales_tax'] ?? false
        ];

        return Inertia::render('User/Event/Tickets', [
            'tickets' => $tickets,
            'coupons' => $event->coupons()->active()->get(),
            'fee' => floatval(Settings::where('key', 'eventFee')->first()?->value),
            'tax' => $taxRate,
            'taxConfig' => $taxConfig,
            'event' => $event,
            'otherEvents' => $otherEvents
        ]);
    }

    public function toggleFavorite(LinkUpEvent $event)
    {
        $user = Auth::user();

        $favorite = FavouriteEvent::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $favorite) {
            FavouriteEvent::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
            ]);

            return back()->withSuccess("Added to favorite");
        } else {
            $favorite->delete();
            return back()->withSuccess("Removed from favorite");
        }
    }

    public function favoriteEvents()
    {
        $events = Auth::user()->favoriteEvents()->with('authUserFavorite')->get();

        return Inertia::render('User/Event/Favorite', [
            'events' => $events,
        ]);
    }

    public function searchEvents()
    {
        $eventsQuery = LinkUpEvent::with(['authUserFavorite']);

        match ($request->status ?? 'all') {
            'upcomming' => $eventsQuery->where('start_time', '>', now()),
            'live' => $eventsQuery->where('start_time', '<=', now())->where('end_time', '>=', now()),
            'completed' => $eventsQuery->where('end_time', '<', now()),
            default => $eventsQuery
        };
        $allCategories = EventCategory::get();
        return Inertia::render('User/Event/Index', [
            'events' => $eventsQuery->get(),
            'search' => true,
            'allCategories' => $allCategories
        ]);
    }

    public function fetchUserForInvitation()
    {
        $users = User::latest()->get();

        return response()->json([
            'users' => $users
        ]);
    }

    public function sendUserInvitationEmail(Request $request)
    {
        $emails = [];
        if (!empty($request->email)) {
            $emails = array_merge($emails, explode(',', $request->email));
        }
        if (!empty($request->userEmail)) {
            $emails = array_merge($emails, $request->userEmail);
        }

        $emails = array_filter(array_map('trim', $emails));

        foreach ($emails as $email) {
            Mail::to($email)->send(new EventInvitationMail($request->event, $request->eventLink));
        }

        return response()->json(['message' => 'Invitations sent successfully!']);
    }

    public function toggleFollowOrganizer(Request $request)
    {
        $user = Auth::user();
        $organizerId = $request->organizer_id;

        if (!$organizerId) {
            return back()->withErrors(['message' => 'Organizer not found']);
        }

        $existingFollow = \App\Models\OrganizerFollower::where('user_id', $user->id)
            ->where('organizer_id', $organizerId)
            ->first();

        if ($existingFollow) {
            $existingFollow->delete();
            return back()->with('success', 'Unfollowed organizer successfully');
        } else {
            $follow = \App\Models\OrganizerFollower::create([
                'user_id' => $user->id,
                'organizer_id' => $organizerId,
            ]);
            return back()->with('success', 'Following organizer successfully');
        }
    }
}
