<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Models\OrganizerProfile;
use App\Services\OrganizerProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(OrganizerProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get organizer profile with all related data
     */
    public function index()
    {
        $user = Auth::user();

        $organizerProfile = OrganizerProfile::with([
            'user',
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found',
                'organizer_detail' => null
            ], 404);
        }

        $categories = EventCategory::all();

        return response()->json([
            'success' => true,
            'organizer_detail' => $organizerProfile,
            'categories' => $categories
        ]);
    }

    /**
     * Update profile details
     */
    public function updateProfileDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'organizer_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'about_the_organizer' => 'required|string',
            'ssn' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        $profile = $this->profileService->profileDetails($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully',
            'profile' => $profile
        ]);
    }

    /**
     * Update additional details (media: logo, cover, profile photo)
     */
    public function updateAdditionalDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = $this->profileService->additionalDetails($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Additional details updated successfully',
            'media' => $profile
        ]);
    }

    /**
     * Update profile contacts
     */
    public function updateContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = $this->profileService->profileContacts($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
            'contacts' => $profile
        ]);
    }

    /**
     * Update profile settings
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'show_venues_map' => 'required|in:0,1',
            'show_followers' => 'required|in:0,1',
            'show_reviews' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = $this->profileService->profileSetting($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => $profile
        ]);
    }

    /**
     * Update bank accounts
     */
    public function updateBankAccounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banks' => 'required|array|min:1',
            'banks.*.id' => 'nullable|integer',
            'banks.*.bank_name' => 'required|string|max:255',
            'banks.*.account_number' => 'required|string|max:255',
            'banks.*.routing_number' => 'required|string|max:255',
            'paypal_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = $this->profileService->profileBankAccounts($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bank accounts updated successfully',
            'bank_account' => $profile
        ]);
    }

    /**
     * Update KYC documents
     */
    public function updateKYC(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'passport_front' => 'nullable|image|mimes:jpeg,png,jpg,pdf',
            'passport_back' => 'nullable|image|mimes:jpeg,png,jpg,pdf',
            'proof_of_address' => 'nullable|image|mimes:jpeg,png,jpg,pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $kyc = $this->profileService->profileKYC($request->all());

        return response()->json([
            'success' => true,
            'message' => 'KYC documents updated successfully. Status set to pending for review.',
            'kyc' => $kyc
        ]);
    }
}
