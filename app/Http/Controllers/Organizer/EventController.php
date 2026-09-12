<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\SendNewEventToFollowersJob;
use App\Models\CaribbeanIsland;
use App\Models\Coupon;
use App\Models\EventCategory;
use App\Models\EventDetails;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\Sponsor;
use App\Models\TicketCheckin;
use App\Models\TicketSale;
use App\Services\EventServices;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\TaxRateService;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $events = LinkUpEvent::with('organizer')->where('organizer_id', $organizer_id->id)->get();
        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/Index', [
            'events' => $events,
            'appURL' => $appURL
        ]);
    }

    public function create($event_id = null)
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();

        $categories = EventCategory::all();
        $caribbeans = CaribbeanIsland::all();
        $scanners = ScanSignUser::where('user_id', Auth::user()->id)->where('status', true)->get();
        $events = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/Create', [
            'categories' => $categories,
            'caribbeans' => $caribbeans,
            'scanners' => $scanners,
            'events' => $events,
            'allEvents' => $allEvent,
            'appURL' => $appURL
        ]);
    }

    public function store(StoreEventRequest $request, EventServices $eventService)
    {
        $validated = $request->validated();
        try {
            $event = $eventService->createEvent($validated);

            if ($event?->id) {
                SendNewEventToFollowersJob::dispatch((int) $event->id);
            }

            return redirect()->route('organizer.event.index')
                ->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function edit(LinkUpEvent $event)
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $eventDetails = EventDetails::where('event_id', $event->id)->first();
        $appURL = env('APP_URL') . "/storage/";
        $sponsor = Sponsor::where('link_up_event_id', $event->id)->first();
        $coupon = Coupon::where('link_up_event_id', $event->id)->first();

        return Inertia::render('organizer/event/Edit', [
            'allEvent' => $allEvent,
            'categories' => EventCategory::all(),
            'caribbeans' => CaribbeanIsland::all(),
            'scanners'   => ScanSignUser::where('user_id', Auth::user()->id)->where('status', true)->get(),
            'events'     => $event,
            'event_detail'      => $eventDetails,
            'appURL' => $appURL,
            'sponsor' => $sponsor,
            'coupon' => $coupon,

        ]);
    }

    public function update(StoreEventRequest $request, EventServices $eventService, $event_id)
    {
        $validated = $request->validated();
        try {
            $event = $eventService->updateEvent($validated, $event_id);

            return redirect()->route('organizer.event.index')
                ->with('success', 'Event Updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delete($event_id)
    {
        LinkUpEvent::findOrFail($event_id)->delete();

        return back()->with('success', 'Event Deleted successfully.');
    }

    public function toggleStatus(Request $request, $event_id)
    {
        $event = LinkUpEvent::findOrFail($event_id);

        // Check if user owns this event
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();

        $newStatus = $request->input('status');

        $event->update(['status' => $newStatus]);
    }

    public function duplicate(Request $request, $event_id)
    {
        $originalEvent = LinkUpEvent::with(['eventDetails'])->findOrFail($event_id);

        // Check if user owns this event
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();

        // Create new event with duplicated data
        $duplicateData = $originalEvent->toArray();
        unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);

        // Modify title to indicate it's a copy
        $duplicateData['title'] = $originalEvent->title . ' (Copy)';
        $duplicateData['status'] = 'draft';

        $newEvent = LinkUpEvent::create($duplicateData);

        // Duplicate event details if they exist
        if ($originalEvent->eventDetails) {
            $detailsData = $originalEvent->eventDetails->toArray();
            unset($detailsData['id'], $detailsData['created_at'], $detailsData['updated_at']);
            $detailsData['event_id'] = $newEvent->id;

            EventDetails::create($detailsData);
        }

        return redirect()->route('organizer.event.edit', $newEvent->id)
            ->with('success', 'Event duplicated successfully! You can now edit the new event.');
    }

    public function show(LinkUpEvent $event)
    {
        $event->load(['organizer', 'category', 'eventDetails', 'tickets']);

        // Check if user owns this event
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();

        // Get ticket statistics (you can enhance this based on your TicketSale model)
        $ticketStats = $this->getEventStatistics($event);

        $soldByTicketId = TicketSale::query()
            ->where('link_up_event_id', $event->id)
            ->whereIn('ticket_status', ['confirmed', 'served'])
            ->select('ticket_id', \Illuminate\Support\Facades\DB::raw('SUM(no_of_tickets) as sold'))
            ->groupBy('ticket_id')
            ->pluck('sold', 'ticket_id');

        $checkedInByTicketId = TicketCheckin::query()
            ->join('ticket_sales', 'ticket_sales.id', '=', 'ticket_checkins.ticket_sale_id')
            ->where('ticket_checkins.event_id', $event->id)
            ->select('ticket_sales.ticket_id', \Illuminate\Support\Facades\DB::raw('COUNT(ticket_checkins.id) as checked_in'))
            ->groupBy('ticket_sales.ticket_id')
            ->pluck('checked_in', 'ticket_sales.ticket_id');

        $event->tickets->transform(function ($ticket) use ($soldByTicketId, $checkedInByTicketId) {
            $ticket->sold = (int) ($soldByTicketId[$ticket->id] ?? 0);
            $ticket->checked_in = (int) ($checkedInByTicketId[$ticket->id] ?? 0);
            return $ticket;
        });

        $appURL = env('APP_URL') . "/storage/";

        return Inertia::render('organizer/event/Show', [
            'event' => $event,
            'ticketStats' => $ticketStats,
            'appURL' => $appURL,
        ]);
    }

    private function getEventStatistics(LinkUpEvent $event)
    {
        $ticketSales = TicketSale::where('link_up_event_id', $event->id)->get();

        $checkedIn = TicketCheckin::query()
            ->where('event_id', $event->id)
            ->count();

        if ($checkedIn <= 0) {
            $checkedIn = TicketSale::query()
                ->where('link_up_event_id', $event->id)
                ->whereHas('checkins')
                ->count();
        }

        return [
            'total_sold' => $ticketSales->count(),
            'revenue' => $ticketSales->where('ticket_status', 'confirmed')->sum('total'),
            'checked_in' => (int) $checkedIn,
            'pending' => $ticketSales->where('ticket_status', 'pending')->count(),
        ];
    }

    public function sponsorCreate()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/CreateSponsor', [
            'allEvent' => $allEvent,
            'sponsor' => null, // Pass null so form is empty
            'appURL' => $appURL,
        ]);
    }

    public function getSponsor($event_id)
    {
        $sponsor = Sponsor::where('link_up_event_id', $event_id)->first();
        return response()->json($sponsor);
    }

    public function sponsorStore(Request $request, ImageService $imageService)
    {
        // Validate input
        $validated = $request->validate([
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'sponsor_image_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,webm,ogg|max:20480',
        ]);

        // Handle file upload (image or video) and store relative path
        $imagePath = null;
        if ($request->hasFile('sponsor_image_file')) {
            $imagePath = $request->file('sponsor_image_file')->store('images/eventSponsors', 'public');
        }

        // Save to DB
        $sponsor = \App\Models\Sponsor::create([
            'sponsor_image_object' => $imagePath,
            'image_object' => $imagePath,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] === 'active' ? 1 : 0,
            'link_up_event_id' => $validated['event_id'],
        ]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Sponsor saved successfully.',
        ]);
    }

    public function sponsorUpdate(Request $request, ImageService $imageService, $sponsor_id)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'sponsor_image_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,webm,ogg|max:20480',
        ]);

        $sponsor = \App\Models\Sponsor::findOrFail($sponsor_id);

        // Preserve existing media unless new one uploaded
        $imagePath = $sponsor->image_object;

        if ($request->hasFile('sponsor_image_file')) {
            $imagePath = $request->file('sponsor_image_file')->store('images/eventSponsors', 'public');
        }

        $sponsor->update([
            'sponsor_image_object' => $imagePath,
            'image_object' => $imagePath,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] === 'active' ? 1 : 0,
            'link_up_event_id' => $validated['event_id'],
        ]);

        return redirect()->back()->with([
            'success' => true,
            'message' => 'Sponsor updated successfully.',
        ]);
    }

    public function couponsCreate()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/CreateCoupons', [
            'allEvent' => $allEvent,
            'coupon' => null, // Pass null so form is empty
            'appURL' => $appURL,
        ]);
    }

    public function getCoupons($event_id)
    {
        $coupon = Coupon::where('link_up_event_id', $event_id)->first();
        return response()->json($coupon);
    }
    public function couponStore(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'link_up_event_id' => 'required|exists:link_up_events,id',
            'code' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:amount,percentage',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'status' => 'required|in:live,draft,expired',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('coupons/images', $request->file('image'));
        }

        $coupon = \App\Models\Coupon::create([
            'link_up_event_id' => $validated['link_up_event_id'],
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount' => $validated['discount'],
            'expiry_date' => $validated['expiry_date'],
            'status' => $validated['status'],
            'image_object' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Coupon saved successfully.');
    }

    public function couponUpdate($coupons_id, Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'link_up_event_id' => 'required',
            'code' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:amount,percentage',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'status' => 'required|in:live,draft,expired',
            'image' => 'nullable|max:2048',
        ]);

        $coupon = \App\Models\Coupon::findOrFail($coupons_id);

        // Preserve existing image unless new one uploaded
        $imagePath = $coupon->image_object;
        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('coupons/images', $request->file('image'));
        }

        $coupon->update([
            'link_up_event_id' => $validated['link_up_event_id'],
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount' => $validated['discount'],
            'expiry_date' => $validated['expiry_date'],
            'status' => $validated['status'],
            'image_object' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Coupon updated successfully.');
    }

    // Get all coupons for an organizer
    public function couponIndex(Request $request)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $coupons = Coupon::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/coupon/Index', [
            'coupons' => $coupons,
            'appURL' => $appURL,
            'filters' => $request->only(['search'])
        ]);
    }

    // Show single coupon
    public function couponShow($coupon_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $coupon = Coupon::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->findOrFail($coupon_id);

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/coupon/Show', [
            'coupon' => $coupon,
            'appURL' => $appURL,
        ]);
    }

    // Edit coupon form
    public function couponEdit($coupon_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $coupon = Coupon::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->findOrFail($coupon_id);
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/CreateCoupons', [
            'allEvent' => $allEvent,
            'coupon' => $coupon,
            'appURL' => $appURL,
            'edit' => true,
        ]);
    }

    // Delete coupon
    public function couponDestroy($coupon_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $coupon = Coupon::whereIn('link_up_event_id', $eventIds)->findOrFail($coupon_id);

        // Delete image if exists
        if ($coupon->image_object && Storage::exists($coupon->image_object)) {
            Storage::delete($coupon->image_object);
        }

        $coupon->delete();

        return redirect()->back()->with('success', 'Coupon deleted successfully.');
    }

    // Toggle coupon status
    public function couponToggleStatus($coupon_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $coupon = Coupon::whereIn('link_up_event_id', $eventIds)->findOrFail($coupon_id);

        $newStatus = $coupon->status === 'live' ? 'draft' : 'live';
        $coupon->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon status updated successfully.',
            'status' => $newStatus
        ]);
    }

    // ============== SPONSOR CRUD METHODS ==============

    // Get all sponsors for an organizer
    public function sponsorIndex(Request $request)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $sponsors = Sponsor::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/sponsor/Index', [
            'sponsors' => $sponsors,
            'appURL' => $appURL,
            'filters' => $request->only(['search'])
        ]);
    }

    // Show single sponsor
    public function sponsorShow($sponsor_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $sponsor = Sponsor::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->findOrFail($sponsor_id);

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/sponsor/Show', [
            'sponsor' => $sponsor,
            'appURL' => $appURL,
        ]);
    }

    // Edit sponsor form
    public function sponsorEdit($sponsor_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $sponsor = Sponsor::whereIn('link_up_event_id', $eventIds)
            ->with('event')
            ->findOrFail($sponsor_id);
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();

        $appURL = env('APP_URL') . "/storage/";
        return Inertia::render('organizer/event/CreateSponsor', [
            'allEvent' => $allEvent,
            'sponsor' => $sponsor,
            'appURL' => $appURL,
            'edit' => true,
        ]);
    }

    // Delete sponsor
    public function sponsorDestroy($sponsor_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $sponsor = Sponsor::whereIn('link_up_event_id', $eventIds)->findOrFail($sponsor_id);

        // Delete image if exists
        if ($sponsor->image_object && Storage::exists($sponsor->image_object)) {
            Storage::delete($sponsor->image_object);
        }

        $sponsor->delete();

        return redirect()->back()->with('success', 'Sponsor deleted successfully.');
    }

    // Toggle sponsor status
    public function sponsorToggleStatus($sponsor_id)
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');

        $sponsor = Sponsor::whereIn('link_up_event_id', $eventIds)->findOrFail($sponsor_id);

        $newStatus = !$sponsor->status;
        $sponsor->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor status updated successfully.',
            'status' => $newStatus ? 'active' : 'inactive'
        ]);
    }

    public function getApiSupportedCountries()
    {
        try {
            // Fetch from configuration file - easier to maintain than hardcoded
            $countries = config('api_countries.taxjar_supported', []);

            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch API supported countries'
            ], 500);
        }
    }

    public function getLocalTaxCountries()
    {
        try {
            $countries = \App\Models\Tax::pluck('country')->filter()->unique()->values();

            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch local tax countries'
            ], 500);
        }
    }

    public function getTaxRate(Request $request, TaxRateService $taxRateService)
    {
        $validated = $request->validate([
            'zip' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'street' => 'nullable|string',
        ]);

        $result = $taxRateService->getRate($validated);
        return response()->json($result, $result['status'] ?? 200);
    }
}
