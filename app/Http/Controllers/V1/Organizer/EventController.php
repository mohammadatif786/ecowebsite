<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\SendNewEventToFollowersJob;
use App\Models\CaribbeanIsland;
use App\Models\Coupon;
use App\Models\Tax;
use App\Models\EventCategory;
use App\Models\EventDetails;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\Sponsor;
use App\Models\TicketSale;
use App\Services\EventServices;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\TaxRateService;

class EventController extends Controller
{
    public function index()
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $events = LinkUpEvent::with('organizer','eventDetails')->where('organizer_id', $organizer_id->id)->get();
        $appURL = env('APP_URL') . "/storage/";

        return response()->json([
            'success' => true,
            'events' => $events,
            'appURL' => $appURL
        ]);
    }

    public function createData($event_id = null)
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $categories = EventCategory::all();
        $caribbeans = CaribbeanIsland::all();
        $scanners = ScanSignUser::where('user_id', Auth::user()->id)->where('status', true)->get();
        $events = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $localTax = Tax::all();

        return response()->json([
            'categories' => $categories,
            'caribbeans' => $caribbeans,
            'scanners' => $scanners,
            'events' => $events,
            'allEvents' => $allEvent,
            'localTax' => $localTax
        ]);
    }

    public function store(StoreEventRequest $request, EventServices $eventService)
    {
        try {
            $event = $eventService->createEvent($request->validated());

            if ($event?->id) {
                SendNewEventToFollowersJob::dispatch((int) $event->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $event,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($event_id)
    {
        $event = LinkUpEvent::with(['organizer', 'category', 'eventDetails', 'tickets'])->findOrFail($event_id);
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $ticketStats = $this->getEventStatistics($event);
        $appURL = env('APP_URL') . "/storage/";
        return response()->json([
            'success' => true,
            'event' => $event,
            'ticketStats' => $ticketStats,
            'appURL' => $appURL,
        ]);
    }
    private function getEventStatistics(LinkUpEvent $event)
    {
        $ticketSales = TicketSale::where('link_up_event_id', $event->id)->get();
        return [
            'total_sold' => $ticketSales->count(),
            'revenue' => $ticketSales->where('ticket_status', 'confirmed')->sum('total'),
            'checked_in' => $ticketSales->where('ticket_status', 'checked_in')->count(),
            'pending' => $ticketSales->where('ticket_status', 'pending')->count(),
        ];
    }
    public function edit($event)
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();
        $allEvent = LinkUpEvent::where('organizer_id', $organizer_id->id)->get();
        $events = LinkUpEvent::where('id', $event)->first();
        $eventDetails = EventDetails::where('event_id', $events->id)->first();
        $appURL = env('APP_URL') . "/storage/";
        $sponsor = Sponsor::where('link_up_event_id', $events->id)->first();
        $coupon = Coupon::where('link_up_event_id', $events->id)->first();

        return response()->json([
            'allEvent' => $allEvent,
            'categories' => EventCategory::all(),
            'caribbeans' => CaribbeanIsland::all(),
            'scanners'   => ScanSignUser::where('user_id', Auth::user()->id)->where('status', true)->get(),
            'events'     => $events,
            'event_detail'      => $eventDetails,
            'appURL' => $appURL,
            'sponsor' => $sponsor,
            'coupon' => $coupon,

        ]);
    }

    public function update(StoreEventRequest $request, EventServices $eventService, $event_id)
    {
        try {
            $event = $eventService->updateEvent($request->validated(), $event_id);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($event_id)
    {
        $event = LinkUpEvent::findOrFail($event_id);
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
        ]);
    }

    public function toggleStatus(Request $request, $event_id)
    {
        $event = LinkUpEvent::findOrFail($event_id);
        $newStatus = $request->input('status');

        $event->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'published'
                ? 'Event published successfully!'
                : 'Event moved to draft successfully!',
        ]);
    }

    public function duplicate($event_id)
    {
        $original = LinkUpEvent::with('eventDetails')->findOrFail($event_id);

        $data = $original->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['title'] = $original->title . ' (Copy)';
        $data['status'] = 'draft';

        $newEvent = LinkUpEvent::create($data);

        if ($original->eventDetails) {
            $details = $original->eventDetails->toArray();
            unset($details['id'], $details['created_at'], $details['updated_at']);
            $details['event_id'] = $newEvent->id;
            EventDetails::create($details);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event duplicated successfully!',
            'event' => $newEvent,
        ]);
    }

    // ================= SPONSOR =================

    public function sponsorIndex()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();
        $sponsor = Sponsor::with('event')->whereIn('link_up_event_id', $eventIds)->get();
        $appURL = env('APP_URL') . "/storage/";
        return response()->json([
            'success' => true,
            'allEvent' => $allEvent,
            'sponsor' => $sponsor,
            'appURL' => $appURL,
        ]);
    }

    public function sponsorStore(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif',
        ]);

        $imagePath = $request->hasFile('image')
            ? $imageService->single('sponsor/images', $request->file('image'))
            : null;

        $sponsor = Sponsor::create([
            'link_up_event_id' => $validated['event_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] === 'active' ? 1 : 0,
            'image_object' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor created successfully!',
            'sponsor' => $sponsor,
        ], 201);
    }

    public function sponsorUpdate(Request $request, ImageService $imageService, $sponsor_id)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:link_up_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        $sponsor = Sponsor::findOrFail($sponsor_id);
        $imagePath = $sponsor->image_object;

        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('sponsor/images', $request->file('image'));
        }

        $sponsor->update([
            'link_up_event_id' => $validated['event_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] === 'active' ? 1 : 0,
            'image_object' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor updated successfully!',
            'sponsor' => $sponsor,
        ]);
    }

    public function sponsorDestroy($sponsor_id)
    {
        $sponsor = Sponsor::findOrFail($sponsor_id);
        if ($sponsor->image_object && Storage::exists($sponsor->image_object)) {
            Storage::delete($sponsor->image_object);
        }
        $sponsor->delete();

        return response()->json(['success' => true, 'message' => 'Sponsor deleted successfully.']);
    }

    public function sponsorToggleStatus($sponsor_id)
    {
        $sponsor = Sponsor::findOrFail($sponsor_id);
        $newStatus = !$sponsor->status;
        $sponsor->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor status updated successfully.',
            'status' => $newStatus ? 'active' : 'inactive',
        ]);
    }

    // ================= COUPON =================

    public function couponIndex()
    {
        $organizer = OrganizerProfile::where('user_id', Auth::id())->first();
        $eventIds = LinkUpEvent::where('organizer_id', $organizer->id)->pluck('id');
        $allEvent = LinkUpEvent::where('organizer_id', $organizer->id)->get();
        $coupon = Coupon::whereIn('link_up_event_id', $eventIds)->get();
        $appURL = env('APP_URL') . "/storage/";
        return response()->json([
            'success' => true,
             'allEvent' => $allEvent,
            'coupon' => $coupon,
            'appURL' => $appURL,
        ]);
    }

    public function couponStore(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'link_up_event_id' => 'required|exists:link_up_events,id',
            'code' => 'required|string|max:255|unique:coupons,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:amount,percentage',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'status' => 'required|in:live,draft,expired',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        $imagePath = $request->hasFile('image')
            ? $imageService->single('coupons/images', $request->file('image'))
            : null;

        $coupon = Coupon::create(array_merge($validated, [
            'image_object' => $imagePath,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully.',
            'coupon' => $coupon,
        ], 201);
    }

    public function couponUpdate(Request $request, ImageService $imageService, $coupon_id)
    {
        $validated = $request->validate([
            'link_up_event_id' => 'required',
            'code' => 'required|string|max:255|unique:coupons,code,' . $coupon_id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:amount,percentage',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'status' => 'required|in:live,draft,expired',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $coupon = Coupon::findOrFail($coupon_id);
        $imagePath = $coupon->image_object;

        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('coupons/images', $request->file('image'));
        }

        $coupon->update(array_merge($validated, ['image_object' => $imagePath]));

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully.',
            'coupon' => $coupon,
        ]);
    }

    public function couponDestroy($coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);
        if ($coupon->image_object && Storage::exists($coupon->image_object)) {
            Storage::delete($coupon->image_object);
        }
        $coupon->delete();

        return response()->json(['success' => true, 'message' => 'Coupon deleted successfully.']);
    }

    public function couponToggleStatus($coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);
        $newStatus = $coupon->status === 'live' ? 'draft' : 'live';
        $coupon->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon status updated successfully.',
            'status' => $newStatus,
        ]);
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

        $isLocal = isset($result['data']['source']) && $result['data']['source'] === 'local_database';
        $numericRate = is_numeric($result['rate'] ?? null) ? (float)$result['rate'] : 0.0;
        $ratePercent = $isLocal ? $numericRate : ($numericRate * 100);
        $rateDisplay = number_format($ratePercent, 2) . '%';

        $payload = array_merge([
            'rate' => $rateDisplay,
        ]);

        return response()->json($payload);
    }

    public function getCategoriesScanners()
    {
        $organizer_id = OrganizerProfile::where('user_id', Auth::user()->id)->first();

        $scanners = ScanSignUser::where('org_id',$organizer_id->id)->get();
        $categories = EventCategory::get();

        return response()->json([
            'scanners' => $scanners,
            'categories' => $categories,
        ]);
    }
}
