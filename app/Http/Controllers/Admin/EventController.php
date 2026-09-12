<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Models\CaribbeanIsland;
use App\Models\CountryPhoneCode;
use App\Models\Coupon;
use App\Models\EventCategory;
use App\Models\EventDetails;
use App\Models\EventFeeSetting;
use App\Models\EventOrganizer;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\ScanSignUser;
use App\Models\Sponsor;
use App\Services\EventServices;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $linkupEvents = LinkUpEvent::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $linkupEvents->load('eventDetails');
        //dd($linkupEvents);
        return Inertia::render('admin/events/Index', [
            'linkupEvents' => $linkupEvents,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function create(GetAdminOverviewDataAction $action)
    {
        $categories = EventCategory::all();
        $caribbeans = CaribbeanIsland::all();
        $scanners = ScanSignUser::all();
        $events = LinkUpEvent::all();
        $allEvent = LinkUpEvent::all();
        $organizer = OrganizerProfile::all();

        $dashboardData = $action->execute()->toArray();

        return Inertia::render('admin/events/CreateEvent', [
            'categories' => $categories,
            'caribbeans' => $caribbeans,
            'scanners' => $scanners,
            'events' => $events,
            'allEvents' => $allEvent,
            'organizer' => $organizer,
            'initialUnits' => $dashboardData['units'],
            'initialCountries' => $dashboardData['countries'],
        ]);
    }

    public function show(LinkUpEvent $event)
    {
        //
    }

    public function store(StoreEventRequest $request, EventServices $eventService)
    {
        $validated = $request->validated();
        $adminId = Auth::id();
        try {
            $event = $eventService->createEvent($validated, $validated['organizer_id'], $adminId);

            return redirect()->route('admin.event.index')
                ->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(LinkUpEvent $event, GetAdminOverviewDataAction $action)
    {
        $allEvent = LinkUpEvent::all();
        $events = LinkUpEvent::where('id', $event->id)->first();
        $eventDetails = EventDetails::where('event_id', $events->id)->first();
        $appURL = env('APP_URL') . "/storage/";
        $sponsor = Sponsor::where('link_up_event_id', $events->id)->first();
        $coupon = Coupon::where('link_up_event_id', $events->id)->first();
        $organizer = OrganizerProfile::all();

        $dashboardData = $action->execute()->toArray();

        return Inertia::render('admin/events/EditEvent', [
            'allEvent' => $allEvent,
            'categories' => EventCategory::all(),
            'caribbeans' => CaribbeanIsland::all(),
            'scanners'   => ScanSignUser::all(),
            'events'     => $events,
            'event_detail'      => $eventDetails,
            'appURL' => $appURL,
            'sponsor' => $sponsor,
            'coupon' => $coupon,
            'organizer' => $organizer,
            'initialUnits' => $dashboardData['units'],
            'initialCountries' => $dashboardData['countries'],
        ]);
    }

    public function update(StoreEventRequest $request, EventServices $eventService, $event_id)
    {
        $validated = $request->validated();

        try {
            $event = $eventService->updateEvent($validated, $event_id);

            return redirect()->route('admin.event.index')
                ->with('success', 'Event Updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(LinkUpEvent $event)
    {
        $event->delete();
        return redirect()->back()->with('success', 'Linkup Event deleted Successfully.');
    }

    public function categories(Request $request)
    {
        $allcategories = EventCategory::query()
            ->withCount('linkupEvents')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/events/Categories', [
            'allcategories' => $allcategories,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    private function getFormDropdownsData()
    {
        return [
            'phoneCodes' => CountryPhoneCode::select([
                'code as value',
                DB::raw("CONCAT(code, ', ', name) as label")
            ])->get(),
            'eventCategories' => EventCategory::select(['id as value', 'name as label'])->get(),
            'organizerUsers' => User::where('type', 'organizer')->select(['id as value', 'name as label'])->get(),
        ];
    }

    // private function saveImages($request, $event)
    // {
    //     if ($request->hasFile('cover_image_file')) {
    //         $path = $request->file('cover_image_file')->store('images/events', 'public');
    //         $url = Storage::url($path);
    //         $event->image_object = $url;
    //         $event->save();
    //     }
    //     if ($request->hasFile('organizer_image_file')) {
    //         $path = $request->file('organizer_image_file')->store('images/events', 'public');
    //         $url = Storage::url($path);
    //         $event->organizer_image_object = $url;
    //         $event->save();
    //     }
    // }

    public function feeSettings()
    {
        $settings = EventFeeSetting::first();
        return Inertia::render('admin/events/EventFee/Index', [
            'settings' => $settings,
        ]);
    }

    public function storefeeSettings(Request $request)
    {
        $data = $request->validate([
            'serviceFeePct' => 'nullable|numeric|min:0',
            'serviceFeeFixed' => 'nullable|numeric|min:0',
            'processingFeePct' => 'nullable|numeric|min:0',
            'processingFeeFixed' => 'nullable|numeric|min:0',
            'taxRate' => 'nullable|numeric|min:0',
            'taxInclusive' => 'nullable|boolean',
            'currency' => 'nullable|string|max:10',
            'drinkFeePct' => 'nullable|numeric|min:0',
            'bottleFeePct' => 'nullable|numeric|min:0',
            'vipFeePct' => 'nullable|numeric|min:0',
            'spaPlatformFeePct' => 'nullable|numeric|min:0',
            'spaPlatformFeeFixed' => 'nullable|numeric|min:0',
            'spaGratuityEnabled' => 'nullable|boolean',
            'spaGratuityDefaultPct' => 'nullable|numeric|min:0',
            'spaUseGlobalTax' => 'nullable|boolean',
            'spaTaxRate' => 'nullable|numeric|min:0',
            'spaTaxInclusive' => 'nullable|boolean',
            'wireProcessingFeePct' => 'nullable|numeric|min:0',
            'cookoutPlatformFeePercent' => 'nullable|numeric|min:0',
            'cookoutPlatformFeeFixed' => 'nullable|numeric|min:0',
            'cookoutDefaultGratuity' => 'nullable|numeric|min:0',
            'cookoutEnableGratuity' => 'nullable|boolean',
        ]);

        // Map camelCase (frontend) to snake_case (db)
        $mapped = [
            'service_fee_pct' => $data['serviceFeePct'] ?? 0,
            'service_fee_fixed' => $data['serviceFeeFixed'] ?? 0,
            'processing_fee_pct' => $data['processingFeePct'] ?? 0,
            'processing_fee_fixed' => $data['processingFeeFixed'] ?? 0,
            'tax_rate' => $data['taxRate'] ?? 0,
            'tax_inclusive' => $data['taxInclusive'] ?? false,
            'currency' => $data['currency'] ?? 'USD',
            'drink_fee_pct' => $data['drinkFeePct'] ?? 0,
            'bottle_fee_pct' => $data['bottleFeePct'] ?? 0,
            'vip_fee_pct' => $data['vipFeePct'] ?? 0,
            'spa_platform_fee_pct' => $data['spaPlatformFeePct'] ?? 0,
            'spa_platform_fee_fixed' => $data['spaPlatformFeeFixed'] ?? 0,
            'spa_gratuity_enabled' => $data['spaGratuityEnabled'] ?? false,
            'spa_gratuity_default_pct' => $data['spaGratuityDefaultPct'] ?? 0,
            'spa_use_global_tax' => $data['spaUseGlobalTax'] ?? false,
            'spa_tax_rate' => $data['spaTaxRate'] ?? 0,
            'spa_tax_inclusive' => $data['spaTaxInclusive'] ?? false,
            'wire_processing_fee_pct' => $data['wireProcessingFeePct'] ?? 0,
            'cookout_platform_fee_percent' => $data['cookoutPlatformFeePercent'] ?? 0,
            'cookout_platform_fee_fixed' => $data['cookoutPlatformFeeFixed'] ?? 0,
            'cookout_default_gratuity' => $data['cookoutDefaultGratuity'] ?? 0,
            'cookout_enable_gratuity' => $data['cookoutEnableGratuity'] ?? false,
        ];

        EventFeeSetting::updateOrCreate(
            ['id' => 1], // or use any unique identifier
            $mapped
        );

        return back()->withSuccess('Fee settings updated!');
    }

    public function storeSponsor(Request $request, ImageService $imageService)
    {
        // Validate input
        $validated = $request->validate([
            'event_id' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('sponsor/images', $request->file('image'));
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
        // Validate input
        $validated = $request->validate([
            'event_id' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('sponsor/images', $request->file('image'));
        }

        // Save to DB
        $sponsor = \App\Models\Sponsor::findOrFail($sponsor_id);

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
            'message' => 'Sponsor saved successfully.',
        ]);
    }

    public function couponStore(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'link_up_event_id' => 'required',
            'code' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:amount,percentage',
            'discount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
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
            'expiry_date' => 'required|date',
            'status' => 'required|in:live,draft,expired',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $imageService->single('coupons/images', $request->file('image'));
        }

        $coupon = \App\Models\Coupon::findOrFail($coupons_id);

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

        return redirect()->back()->with('success', 'Coupon Updated successfully.');
    }
}
