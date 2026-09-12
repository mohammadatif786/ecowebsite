<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizerUserProfileRequest;
use App\Models\EventCategory;
use App\Models\OrganizerProfile;
use App\Services\OrganizerProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $appURL = asset('storage');
        $categories = EventCategory::get();
        $userId = Auth::id();
        $organizer_detail = OrganizerProfile::with([
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->where('user_id', $userId)->first();

        return Inertia::render('organizer/profile/Index', [
            'organizer_detail' => $organizer_detail,
            'categories' => $categories,
            'appURL' => $appURL
        ]);
    }

    public function update(OrganizerUserProfileRequest $request, $organizer_profile_type, OrganizerProfileService $OrganizerProfileService)
    {
        $validate_data = $request->validated();
        $userId = Auth::id();
        $profileExists = OrganizerProfile::where('user_id', $userId)->exists();
        $hasBasicFields = !empty($validate_data['organizer_name']) && !empty($validate_data['country']);

        if (! $profileExists && ! $hasBasicFields) {
            return redirect()->back()->with('error', 'Please complete profile details section first.');
        }
        switch ($organizer_profile_type) {

            case 'profileDetail':
                $validate_data['user_id'] = $userId;
                $OrganizerProfileService->profileDetails($validate_data);
                return redirect()->back()->with("success", "Profile Detail Update.");

            case 'AdditionalDetails':
                $OrganizerProfileService->additionalDetails($validate_data);
                return redirect()->back()->with("success", "Profile Additional Detail Update.");

            case 'profileContacts':
                $OrganizerProfileService->profileContacts($validate_data);
                return redirect()->back()->with("success", "Profile Contact Update.");

            case 'ProfileSetting':
                $OrganizerProfileService->profileSetting($validate_data);
                return redirect()->back()->with("success", "Profile Setting Update.");

            case 'profileBankAccounts':
                $OrganizerProfileService->profileBankAccounts($validate_data);
                return redirect()->back()->with("success", "Profile Bank Account Update.");

            case 'profileKYC':
                $OrganizerProfileService->profileKYC($validate_data);
                return redirect()->back()->with("success", "Profile KYC Update.");

            default:
                return redirect()->back()->with("error", "Invalid Profile Type.");
        }
    }

}
