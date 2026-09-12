<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizerProfileRequest;
use App\Models\EventCategory;
use App\Models\EventOrganizer;
use App\Models\OrganizerBankAccount;
use App\Models\OrganizerContact;
use App\Models\OrganizerKyc;
use App\Models\OrganizerMedia;
use App\Models\OrganizerProfile;
use App\Models\OrganizerSetting;
use App\Models\User;
use App\Services\ImageService;
use App\Services\OrganizerProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventOrganizerController extends Controller
{
    protected $image_service;

    public function __construct(ImageService $image_service)
    {
        $this->image_service = $image_service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appURL = env('APP_URL') . "/storage/";
        $organizer_detail = OrganizerProfile::query()->filter($request->only('search'))->with([
            'user',
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->orderBy('created_at', 'DESC')->paginate(10);

        return Inertia::render('admin/organizer/Index', [
            'organizers' => $organizer_detail,
            'appURL' => $appURL,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $appURL = env('APP_URL') . "/storage/";
        $categories = EventCategory::get();
        return Inertia::render('admin/organizer/CreateEdit', [
            'categories' => $categories,
            'appURL' => $appURL
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizerProfileRequest $request)
    {
        $validate_data = $request->validated();

        $accountEmail = $validate_data['organizerMedia']['email'] ?? null;

        if ($accountEmail && User::where('email', $accountEmail)->exists()) {
            return back()
                ->withErrors(['organizerMedia.email' => 'An account with this email already exists.'])
                ->withInput();
        }

        $newUser = User::create([
            'name' => $validate_data['profileDetail']['organizer_name'] ?? null,
            'email' => $accountEmail,
            'password' => Hash::make($validate_data['accountPassword']),
            'type' => 'organizer',
            'status' => 1,
        ]);

        if ($validate_data['profileDetail']['organizer_profile_type'] === "profileDetail") {
            $validate_data['user_id'] = $newUser->id;

            $organizer = OrganizerProfile::create([
                'user_id' => $validate_data['user_id'],
                'organizer_name' => $validate_data['profileDetail']['organizer_name'],
                'date_of_birth' => $validate_data['profileDetail']['date_of_birth'],
                'place_of_birth' => $validate_data['profileDetail']['place_of_birth'],
                'nationality' => $validate_data['profileDetail']['nationality'],
                'address' => $validate_data['profileDetail']['address'],
                'telephone' => $validate_data['profileDetail']['telephone'],
                'about_the_organizer' => $validate_data['profileDetail']['about_the_organizer'],
                'ssn' => $validate_data['profileDetail']['ssn'],
            ]);
        }

        if ($validate_data['additionalDetails']['organizer_profile_type'] === "AdditionalDetails") {

            if (isset($validate_data['additionalDetails']['logo'])) {
                $validate_data['logo'] = $this->image_service->single('organizer/logo', $validate_data['additionalDetails']['logo']);
            }

            if (isset($validate_data['additionalDetails']['cover_photo'])) {
                $validate_data['cover_photo'] = $this->image_service->single('organizer/cover', $validate_data['additionalDetails']['cover_photo']);
            }

            if (isset($validate_data['additionalDetails']['profile_photo'])) {
                $validate_data['profile_photo'] = $this->image_service->single('organizer/profile', $validate_data['additionalDetails']['profile_photo']);
            }

            $validate_data['organizer_id'] = $organizer->id;

            $profile = OrganizerMedia::create($validate_data);
        }

        if ($validate_data['organizerMedia']['organizer_profile_type'] === "OrganizerMedia") {

            $validate_data['organizer_id'] = $organizer->id;

            $profile = OrganizerContact::create([
                'organizer_id' => $validate_data['organizer_id'],
                'facebook' => $validate_data['organizerMedia']['facebook'],
                'twitter' => $validate_data['organizerMedia']['twitter'],
                'instagram' => $validate_data['organizerMedia']['instagram'],
                'linkedin' => $validate_data['organizerMedia']['linkedin'],
                'youtube' => $validate_data['organizerMedia']['youtube'],
                'country' => $validate_data['organizerMedia']['country'],
                'state' => $validate_data['organizerMedia']['state'],
                'city' => $validate_data['organizerMedia']['city'],
                'website' => $validate_data['organizerMedia']['website'],
                'email' => $validate_data['organizerMedia']['email'],
                'phone' => $validate_data['organizerMedia']['phone'],
            ]);
        }

        if ($validate_data['profileVisibility']['organizer_profile_type'] === "profileVisibility") {
            $validate_data['organizer_id'] = $organizer->id;

            $profile = OrganizerSetting::create([
                'organizer_id' => $validate_data['organizer_id'],
                'show_venues_map' => $validate_data['profileVisibility']['show_venues_map'],
                'show_followers' => $validate_data['profileVisibility']['show_followers'],
                'show_reviews' => $validate_data['profileVisibility']['show_reviews'],
            ]);
        }

        if ($validate_data['bankingInformation']['organizer_profile_type'] === "BankingInformation") {
            $validate_data['organizer_id'] = $organizer->id;

            foreach ($validate_data['bankingInformation']['banks'] ?? [] as $bank) {
                if (empty($bank['bank_name']) && empty($bank['account_number']) && empty($bank['routing_number'])) {
                    continue;
                }

                OrganizerBankAccount::create([
                    'organizer_id'    => $validate_data['organizer_id'],
                    'bank_name'       => $bank['bank_name'] ?? null,
                    'account_number'  => $bank['account_number'] ?? null,
                    'routing_number'  => $bank['routing_number'] ?? null,
                    'paypal_id'       => $validate_data['bankingInformation']['paypal_id'] ?? null,
                ]);
            }
        }
        if ($validate_data['organizerKYC']['organizer_profile_type'] === "organizerKYC") {

            $validate_data['organizer_id'] = $organizer->id;
            $passportFront    = $validate_data['organizerKYC']['passport_front']
                ? $this->image_service->single('organizer/kyc', $validate_data['organizerKYC']['passport_front'])
                : $organizer->passport_front;

            $passportBack     = $validate_data['organizerKYC']['passport_back']
                ? $this->image_service->single('organizer/kyc', $validate_data['organizerKYC']['passport_back'])
                : $organizer->passport_back;

            $proofOfAddress   = $validate_data['organizerKYC']['proof_of_address']
                ? $this->image_service->single('organizer/kyc', $validate_data['organizerKYC']['proof_of_address'])
                : $organizer->proof_of_address;
            $profile = OrganizerKyc::create([
                'organizer_id' => $validate_data['organizer_id'],
                'passport_front' => $passportFront,
                'passport_back' => $passportBack,
                'proof_of_address' => $proofOfAddress,
            ]);
        }

        return redirect()->route($this->redirectRoute($request))
            ->with('message', 'Event Organizer created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appURL = env('APP_URL') . "/storage/";
         $organizer_detail = OrganizerProfile::with([
            'user',
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->findOrfail($id);
        return Inertia::render('admin/organizer/ViewOrganizer', [
            'organizer' => $organizer_detail,
            'appURL' => $appURL
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $appURL = env('APP_URL') . "/storage/";
        $categories = EventCategory::get();
        $organizer_detail = OrganizerProfile::with([
            'user',
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->findOrfail($id);
        return Inertia::render('admin/organizer/Edit', [
            'organizer_detail' => $organizer_detail,
            'appURL' => $appURL,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrganizerProfileRequest $request, string $id)
    {

        $validate_data = $request->validated();
        if ($validate_data['profileDetail']['organizer_profile_type'] === "profileDetail") {
            $organizer = OrganizerProfile::findOrfail($id);
            $organizer->update([
                'organizer_name' => $validate_data['profileDetail']['organizer_name'],
                'date_of_birth' => $validate_data['profileDetail']['date_of_birth'],
                'place_of_birth' => $validate_data['profileDetail']['place_of_birth'],
                'nationality' => $validate_data['profileDetail']['nationality'],
                'address' => $validate_data['profileDetail']['address'],
                'telephone' => $validate_data['profileDetail']['telephone'],
                'about_the_organizer' => $validate_data['profileDetail']['about_the_organizer'],
                'ssn' => $validate_data['profileDetail']['ssn'],
            ]);
        }

        if ($validate_data['additionalDetails']['organizer_profile_type'] === "AdditionalDetails") {

            $additionalDetails = OrganizerMedia::where('organizer_id', $id)->first();
            if ($additionalDetails) {
                $updateData = [];

                $updateData['logo'] = $request->hasFile('additionalDetails.logo')
                    ? $this->image_service->single('organizer/logo', $request->file('additionalDetails.logo'))
                    : $additionalDetails->logo;

                $updateData['cover_photo'] = $request->hasFile('additionalDetails.cover_photo')
                    ? $this->image_service->single('organizer/cover', $request->file('additionalDetails.cover_photo'))
                    : $additionalDetails->cover_photo;

                $updateData['profile_photo'] = $request->hasFile('additionalDetails.profile_photo')
                    ? $this->image_service->single('organizer/profile', $request->file('additionalDetails.profile_photo'))
                    : $additionalDetails->profile_photo;

                $additionalDetails->update($updateData);
            }
        }

        if ($validate_data['organizerMedia']['organizer_profile_type'] === "OrganizerMedia") {

            OrganizerContact::updateOrCreate(
                ['organizer_id' => $id],
                [
                    'facebook' => $validate_data['organizerMedia']['facebook'],
                    'twitter'  => $validate_data['organizerMedia']['twitter'],
                    'instagram' => $validate_data['organizerMedia']['instagram'],
                    'linkedin' => $validate_data['organizerMedia']['linkedin'],
                    'youtube'  => $validate_data['organizerMedia']['youtube'],
                    'country'  => $validate_data['organizerMedia']['country'],
                    'state'    => $validate_data['organizerMedia']['state'] ?? null,
                    'city'     => $validate_data['organizerMedia']['city'] ?? null,
                    'website'  => $validate_data['organizerMedia']['website'],
                    'email'    => $validate_data['organizerMedia']['email'],
                    'phone'    => $validate_data['organizerMedia']['phone'],
                ]
            );
        }

        if ($validate_data['profileVisibility']['organizer_profile_type'] === "profileVisibility") {

            OrganizerSetting::updateOrCreate(
                ['organizer_id' => $id],
                [
                    'show_venues_map' => $validate_data['profileVisibility']['show_venues_map'],
                    'show_followers'  => $validate_data['profileVisibility']['show_followers'],
                    'show_reviews'    => $validate_data['profileVisibility']['show_reviews'],
                ]
            );
        }

        if ($validate_data['bankingInformation']['organizer_profile_type'] === "BankingInformation") {

            $requestBanks = collect($validate_data['bankingInformation']['banks']);

            $accountNumbers = $requestBanks->pluck('account_number')->toArray();

            OrganizerBankAccount::where('organizer_id', $id)
                ->whereNotIn('account_number', $accountNumbers)
                ->delete();

            foreach ($requestBanks as $bank) {
                OrganizerBankAccount::updateOrCreate(
                    [
                        'organizer_id'   => $id,
                        'account_number' => $bank['account_number'],
                    ],
                    [
                        'bank_name'      => $bank['bank_name'],
                        'routing_number' => $bank['routing_number'],
                        'paypal_id'      => $validate_data['bankingInformation']['paypal_id'],
                    ]
                );
            }
        }


        if ($validate_data['organizerKYC']['organizer_profile_type'] === "organizerKYC") {

            $existingKyc = OrganizerKyc::where('organizer_id', $id)->first();

            OrganizerKyc::updateOrCreate(
                ['organizer_id' => $id],
                [
                    'passport_front'  => $request->hasFile('organizerKYC.passport_front')
                        ? $this->image_service->single('organizer/kyc', $request->file('organizerKYC.passport_front'))
                        : $existingKyc?->passport_front,
                    'passport_back'   => $request->hasFile('organizerKYC.passport_back')
                        ? $this->image_service->single('organizer/kyc', $request->file('organizerKYC.passport_back'))
                        : $existingKyc?->passport_back,
                    'proof_of_address' => $request->hasFile('organizerKYC.proof_of_address')
                        ? $this->image_service->single('organizer/kyc', $request->file('organizerKYC.proof_of_address'))
                        : $existingKyc?->proof_of_address,
                ]
            );
        }

        return redirect()->route($this->redirectRoute($request))
            ->with('message', 'Event Organizer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $organizer = OrganizerProfile::findOrFail($id);
        $organizer->delete();
        return redirect()->route($this->redirectRoute($request))
            ->with('message', 'Event Organizer deleted successfully.');
    }

    /**
     * Send admins back to whichever organizer list they came from (old admin vs New Admin).
     */
    private function redirectRoute(Request $request): string
    {
        return $request->query('source') === 'new_admin'
            ? 'admin.organizer-directory'
            : 'admin.organizer.profile.index';
    }
}
