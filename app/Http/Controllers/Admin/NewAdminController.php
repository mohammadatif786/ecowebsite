<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetAdminCancelTicketsDataAction;
use App\Actions\Admin\GetAdminEventCategoriesDataAction;
use App\Actions\Admin\GetAdminEventCouponsDataAction;
use App\Actions\Admin\GetAdminOrganizerDirectoryDataAction;
use App\Actions\Admin\GetAdminScannersManagementDataAction;
use App\Actions\Admin\GetAdminEventSponsorsDataAction;
use App\Actions\Admin\GetAdminEventsDataAction;
use App\Actions\Admin\GetAdminTicketSalesDataAction;
use App\Actions\Admin\GetAdminOverviewDataAction;
use App\Actions\Admin\GetAdminUsersDataAction;
use App\Actions\Admin\GetAdminWalletMissionControlDataAction;
use App\Actions\Admin\SubscriptionPlans\GetSubscribedUsersAction;
use App\Contracts\SubscriptionPlanRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\WithdrawRequest;
use App\Models\Merchants;
use App\Models\User;
use App\Models\Product;
use App\Models\OrganizerProfile;
use App\Models\LinkUpEvent;
use App\Models\EventReview;
use App\Models\FlaggedUser;
use App\Models\EventFeeSetting;
use App\Models\OrganizerKyc;
use App\Models\Settings;
use App\Models\UserWalletKyc;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class NewAdminController extends Controller
{
    public function startTwoFactorSetup(Request $request)
    {
        $secret = $this->generateAdminSecret();
        $verificationCode = (string) random_int(100000, 999999);

        $request->session()->put('admin_two_factor_setup', [
            'secret' => $secret,
            'code_hash' => Hash::make($verificationCode),
            'created_at' => now()->toIso8601String(),
        ]);

        Log::info('Admin two-factor setup started', [
            'admin_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'secret' => $secret,
            'demo_code' => $verificationCode,
            'message' => 'Two-factor setup started.',
        ]);
    }

    public function verifyTwoFactorSetup(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $setup = $request->session()->get('admin_two_factor_setup');

        if (! is_array($setup) || ! Hash::check($data['code'], (string) ($setup['code_hash'] ?? ''))) {
            Log::warning('Admin two-factor setup verification failed', [
                'admin_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.',
            ]);
        }

        $request->user()->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        Log::info('Admin two-factor setup verified', [
            'admin_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'two_factor_enabled' => true,
            'message' => 'Two-factor authentication is active.',
        ]);
    }

    public function generateRecoveryCodes(Request $request)
    {
        $codes = collect(range(1, 8))
            ->map(fn () => Str::lower(Str::random(4) . '-' . Str::random(4)))
            ->values();

        $request->session()->put(
            'admin_recovery_code_hashes',
            $codes->map(fn (string $code): string => Hash::make($code))->all()
        );

        Log::info('Admin recovery codes generated', [
            'admin_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'code_count' => $codes->count(),
        ]);

        return response()->json([
            'recovery_codes' => $codes,
            'message' => 'Recovery codes generated.',
        ]);
    }

    public function dashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/NewOverview', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    private function generateAdminSecret(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return collect(range(1, 16))
            ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
            ->implode('');
    }

    public function feeRevenue(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/FeeRevenueDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function users(GetAdminUsersDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/UsersDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialUserProfiles' => $data['userProfiles'],
            'initialSubscriptionPlans' => $data['subscriptionPlans'],
            'initialGrowthStats' => $data['growthStats'],
        ]);
    }

    public function updateUserProfile(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'nullable|string|max:50',
            'kyc_status' => 'nullable|string|max:50',
            'status' => 'required|boolean',
            'avatar' => 'nullable|image|max:5120',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'country' => $validated['country'] ?? $user->country,
            'gender' => $validated['gender'] ?? $user->gender,
            'kyc_status' => $validated['kyc_status'] ?? $user->kyc_status,
            'status' => $validated['status'],
        ];

        if (array_key_exists('age', $validated) && $validated['age'] !== null) {
            $data['birthday'] = now()->subYears((int) $validated['age'])->format('Y-m-d');
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $data['avatar'] = null;
        }

        $user->update($data);

        return redirect()->back()->with('message', 'User updated successfully.');
    }

    public function destroyUserProfile(User $user)
    {
        $user->delete();

        return redirect()->back()->with('message', 'User deleted successfully.');
    }

    public function businessUnits()
    {
        return Inertia::render('admin/BusinessUnitsDashboard');
    }

    public function countries(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/CountriesDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function forecasting()
    {
        return Inertia::render('admin/ForecastingDashboard');
    }

    public function askAI()
    {
        return Inertia::render('admin/AskAIDashboard');
    }

    public function settingsDashboard(Request $request)
    {
        $adsSettings = Settings::firstOrCreate(
            ['key' => 'adsSettings'],
            ['value' => [
                'admob_native_ad_id' => null,
                'admob_interstitial_ad_id' => null,
                'admob_banner_ad_id' => null,
                'admob_rewarded_video_id' => null,
                'home_feed_ads_enabled' => true,
                'marketplace_ads_enabled' => true,
                'news_sponsored_ads_enabled' => true,
                'live_stream_ads_enabled' => false,
                'event_ticket_ads_enabled' => true,
            ]]
        )->value;

        $smtpSettings = Settings::firstOrCreate(
            ['key' => 'smtpSettings'],
            ['value' => [
                'host' => null,
                'port' => null,
                'encryption' => null,
                'from_name' => null,
                'username' => null,
                'password' => null,
            ]]
        )->value;
        $smtpSettings['has_password'] = ! empty($smtpSettings['password']);
        unset($smtpSettings['password']);

        return Inertia::render('admin/SettingsDashboard', [
            'activeView' => $request->query('view', 'settingsAppCommand'),
            'initialAdsSettings' => $adsSettings,
            'initialSmtpSettings' => $smtpSettings,
        ]);
    }

    public function eWalletDashboard(
        GetAdminWalletMissionControlDataAction $walletAction,
        \App\Actions\Admin\Ads\GetAdminAdManagementDataAction $adAction,
        \App\Actions\Admin\Ads\CampaignTypeAction $campaignTypeAction,
        \App\Actions\Admin\Ads\TerritoryTierAction $territoryTierAction,
        \App\Actions\Admin\Ads\DeliveryChannelAction $deliveryChannelAction,
        \App\Actions\Admin\Ads\ExclusivityUpgradeAction $exclusivityUpgradeAction,
        NewsRepository $newsRepository,
        Request $request
    ) {
        $walletData = $walletAction->execute()->toArray();
        $adData = $adAction->execute()->toArray();
        $newsData = $newsRepository->getAll($request);

        return Inertia::render('admin/EWalletDashboard', [
            'initialUnits' => $walletData['units'],
            'initialCountries' => $walletData['countries'],
            'initialWalletMovements' => $walletData['walletMovements'],
            'initialWalletUsers' => $walletData['walletUsers'],
            'initialWalletUserStats' => $walletData['walletUserStats'],
            'initialWalletCountryBalances' => $walletData['walletCountryBalances'],
            'initialCurrencyExposure' => $walletData['currencyExposure'],
            'initialWalletBalanceSummary' => $walletData['walletBalanceSummary'],
            'initialSettlements' => $walletData['settlements'],
            'initialSettlementSummary' => $walletData['settlementSummary'],
            'initialAsueCircles' => $walletData['asueCircles'],
            'initialAsueSummary' => $walletData['asueSummary'],
            'initialPayoutQueue' => $walletData['payoutQueue'],
            'initialPayoutQueueSummary' => $walletData['payoutQueueSummary'],
            'initialAds' => $adData['ads'],
            'initialEmailAds' => $adData['emailAds'],
            'initialAdStats' => $adData['stats'],
            'initialAdRevenueByChannel' => $adData['revenueByChannel'],
            'initialAdActivityFeed' => $adData['activityFeed'],
            'initialAdNeedsAttention' => $adData['needsAttention'],
            'initialEmailAdCategories' => $adData['emailCategories'],
            'initialEmailCountries' => $adData['emailCountries'],
            'initialC360NewsAds' => $adData['c360NewsAds'],
            'initialCampaignTypes' => $campaignTypeAction->list(),
            'initialTerritoryTiers' => $territoryTierAction->list(),
            'initialDeliveryChannels' => $deliveryChannelAction->list(),
            'initialExclusivityUpgrades' => $exclusivityUpgradeAction->list(),
            'initialAdIndustries' => $adData['adIndustries'],
            'initialAdSurgeOptions' => $adData['adSurgeOptions'],
            'initialCampaignLaunches' => \App\Models\AdCampaignLaunch::latest('launched_at')->limit(20)->get(),
            'initialReviewsConcerns' => $this->getReviewsConcernsData(),
            'initialKycReviews' => $this->getKycReviewData(),
            'initialFlaggedUsers' => $this->getFlaggedUsersData(),
            'initialAdminUsers' => $this->getAdminUsersData(),
            'initialAdminRoles' => $this->getRolesPermissionsData(),
            'initialPermissionModules' => \App\Http\Controllers\Admin\NewAdmin\RolePermissionController::MODULES,
            'initialEventFeeSettings' => EventFeeSetting::firstOrCreate(['id' => 1]),
            'news' => $newsData['news'],
            'news_source' => $newsData['news_source'],
            'filters' => $newsData['filters'],
            'message' => session('message'),
        ]);
    }

    private function getAdminUsersData()
    {
        return User::query()
            ->where('type', 'admin')
            ->with('roles:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->name ?? 'Unassigned',
                'scope' => $user->scope ?? 'Global',
                'lastLogin' => $user->last_login_at?->diffForHumans() ?? 'Never',
                'twofa' => $user->two_factor_enabled ? 'Enabled' : 'Pending',
                'status' => $user->status ? 'Active' : 'Suspended',
            ]);
    }

    private function getRolesPermissionsData()
    {
        $modules = \App\Http\Controllers\Admin\NewAdmin\RolePermissionController::MODULES;
        $actions = \App\Http\Controllers\Admin\NewAdmin\RolePermissionController::ACTIONS;
        $tones = ['purple', 'sky', 'green', 'orange', 'indigo', 'pink', 'amber', 'rose', 'slate'];

        return Role::where('guard_name', 'web')
            ->withCount('users')
            ->with('permissions:id,name')
            ->get()
            ->map(function (Role $role) use ($modules, $actions, $tones) {
                $grantedNames = $role->permissions->pluck('name');

                $matrix = collect($modules)->map(function ($module) use ($actions, $grantedNames) {
                    $slug = Str::slug($module);
                    $row = ['module' => $module];
                    foreach ($actions as $action) {
                        $row[$action] = $grantedNames->contains("{$slug}.{$action}");
                    }

                    return $row;
                });

                $primary = $matrix->first(fn ($row) => $row['view']) ?? $matrix->first();
                $moduleIndex = array_search($primary['module'] ?? null, $modules, true);

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'purpose' => $role->description ?? '',
                    'users' => $role->users_count,
                    'coreAccess' => $primary['module'] ?? 'Custom',
                    'coreTone' => $tones[$moduleIndex !== false ? $moduleIndex % count($tones) : 0],
                    'risk' => $role->risk_level ?? 'Medium',
                    'status' => 'Active',
                    'permissions' => $matrix->values(),
                ];
            });
    }

    private function getReviewsConcernsData()
    {
        return EventReview::with([
                'event:id,title,country,venue',
                'user:id,name,email,first_name,last_name,country',
            ])
            ->latest()
            ->limit(100)
            ->get()
            ->map(function (EventReview $review) {
                $user = $review->user;
                $event = $review->event;
                $nameFromUser = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                $reviewerName = $review->reviewer_name ?: ($nameFromUser ?: ($user->name ?? 'Anonymous'));
                $rating = (int) $review->rating;

                return [
                    'id' => $review->id,
                    'service' => 'Events & Tickets',
                    'rating' => $rating,
                    'user' => $reviewerName,
                    'email' => $user->email ?? null,
                    'country' => $event->country ?? $user->country ?? 'Unknown',
                    'source' => $event->title ?? 'Event review',
                    'category' => $rating <= 2 ? 'Complaint' : ($rating === 3 ? 'Concern' : 'Praise'),
                    'status' => $review->status,
                    'date' => optional($review->created_at)->toDateString(),
                    'comment' => $review->review_text,
                    'event_id' => $review->event_id,
                    'event_title' => $event->title ?? null,
                ];
            })
            ->values();
    }

    public function updateReviewConcernStatus(Request $request, EventReview $review)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $review->update(['status' => $data['status']]);

        return back()->with('message', 'Review status updated successfully.');
    }

    public function destroyReviewConcern(EventReview $review)
    {
        $review->delete();

        return back()->with('message', 'Review deleted successfully.');
    }

    private function getKycReviewData()
    {
        $walletRecords = UserWalletKyc::with('user:id,name,email,first_name,last_name,country')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (UserWalletKyc $kyc) => $this->mapWalletKycRecord($kyc));

        $organizerRecords = OrganizerKyc::with('organizer.user:id,name,email,first_name,last_name,country')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (OrganizerKyc $kyc) => $this->mapOrganizerKycRecord($kyc));

        $userRecords = User::query()
            ->where(function ($query) {
                $query->whereNotNull('kyc_status')
                    ->where('kyc_status', '!=', '')
                    ->orWhereNotNull('front_side')
                    ->orWhereNotNull('back_side')
                    ->orWhereNotNull('address_proof')
                    ->orWhereNotNull('selfie')
                    ->orWhere('kyc_submitted', 1);
            })
            ->latest()
            ->limit(100)
            ->get(['id', 'name', 'email', 'first_name', 'last_name', 'country', 'kyc_status', 'front_side', 'back_side', 'address_proof', 'selfie', 'kyc_submitted', 'created_at', 'updated_at'])
            ->map(fn (User $user) => $this->mapUserKycRecord($user));

        return $walletRecords
            ->concat($organizerRecords)
            ->concat($userRecords)
            ->sortByDesc('submitted')
            ->values();
    }

    private function mapWalletKycRecord(UserWalletKyc $kyc): array
    {
        $status = $this->displayKycStatus($kyc->status);
        $documents = collect($kyc->kyc_documents ?: [])
            ->values()
            ->map(fn ($path, $index) => [
                'key' => 'document_' . ($index + 1),
                'label' => 'Document ' . ($index + 1),
                'status' => $status,
                'raw_status' => $kyc->status ?: 'pending',
                'url' => $this->kycFileUrl($path),
            ])
            ->values()
            ->all();

        if (empty($documents)) {
            $documents[] = [
                'key' => 'document_1',
                'label' => $kyc->id_type ?: 'ID Document',
                'status' => $status,
                'raw_status' => $kyc->status ?: 'pending',
                'url' => null,
            ];
        }

        return [
            'id' => 'KYC-WAL-' . $kyc->id,
            'source' => 'wallet',
            'source_id' => $kyc->id,
            'profileType' => 'User Wallet',
            'name' => $kyc->full_name ?: $this->userDisplayName($kyc->user),
            'email' => $kyc->user->email ?? '',
            'country' => $kyc->user->country ?? 'Unknown',
            'submitted' => optional($kyc->created_at)->toDateTimeString(),
            'status' => $status,
            'raw_status' => $kyc->status ?: 'pending',
            'risk' => $this->kycRisk($status),
            'notes' => trim(($kyc->id_type ? $kyc->id_type . ' ' : '') . ($kyc->id_number ? '#' . $kyc->id_number . '. ' : '') . ($kyc->address ?: 'Wallet KYC submission.')),
            'documents' => $documents,
            'updated_at' => optional($kyc->updated_at)->toDateTimeString(),
        ];
    }

    private function mapOrganizerKycRecord(OrganizerKyc $kyc): array
    {
        $organizer = $kyc->organizer;
        $user = $organizer?->user;
        $status = $this->displayKycStatus($kyc->status);

        return [
            'id' => 'KYC-ORG-' . $kyc->id,
            'source' => 'organizer',
            'source_id' => $kyc->id,
            'profileType' => 'Organizer',
            'name' => $organizer->organizer_name ?? $this->userDisplayName($user),
            'email' => $user->email ?? '',
            'country' => $user->country ?? 'Unknown',
            'submitted' => optional($kyc->created_at)->toDateTimeString(),
            'status' => $status,
            'raw_status' => $kyc->status ?: 'pending',
            'risk' => $this->kycRisk($status, [$kyc->p_front_status, $kyc->p_back_status, $kyc->p_o_add_status]),
            'notes' => 'Organizer identity verification documents.',
            'documents' => [
                ['key' => 'passport_front', 'label' => 'Passport Front', 'status' => $this->displayKycStatus($kyc->p_front_status), 'raw_status' => $kyc->p_front_status ?: 'pending', 'url' => $this->kycFileUrl($kyc->passport_front)],
                ['key' => 'passport_back', 'label' => 'Passport Back', 'status' => $this->displayKycStatus($kyc->p_back_status), 'raw_status' => $kyc->p_back_status ?: 'pending', 'url' => $this->kycFileUrl($kyc->passport_back)],
                ['key' => 'proof_of_address', 'label' => 'Proof of Address', 'status' => $this->displayKycStatus($kyc->p_o_add_status), 'raw_status' => $kyc->p_o_add_status ?: 'pending', 'url' => $this->kycFileUrl($kyc->proof_of_address)],
            ],
            'updated_at' => optional($kyc->updated_at)->toDateTimeString(),
        ];
    }

    private function mapUserKycRecord(User $user): array
    {
        $status = $this->displayKycStatus($user->kyc_status ?: ($user->kyc_submitted ? 'pending' : 'pending'));

        return [
            'id' => 'KYC-USR-' . $user->id,
            'source' => 'user',
            'source_id' => $user->id,
            'profileType' => 'User Profile',
            'name' => $this->userDisplayName($user),
            'email' => $user->email ?? '',
            'country' => $user->country ?? 'Unknown',
            'submitted' => optional($user->updated_at ?? $user->created_at)->toDateTimeString(),
            'status' => $status,
            'raw_status' => $user->kyc_status ?: 'pending',
            'risk' => $this->kycRisk($status),
            'notes' => $user->kyc_submitted ? 'User profile KYC submitted.' : 'User profile has KYC documents or status.',
            'documents' => [
                ['key' => 'front_side', 'label' => 'ID Front', 'status' => $status, 'raw_status' => $user->kyc_status ?: 'pending', 'url' => $this->kycFileUrl($user->front_side)],
                ['key' => 'back_side', 'label' => 'ID Back', 'status' => $status, 'raw_status' => $user->kyc_status ?: 'pending', 'url' => $this->kycFileUrl($user->back_side)],
                ['key' => 'address_proof', 'label' => 'Address Proof', 'status' => $status, 'raw_status' => $user->kyc_status ?: 'pending', 'url' => $this->kycFileUrl($user->address_proof)],
                ['key' => 'selfie', 'label' => 'Selfie', 'status' => $status, 'raw_status' => $user->kyc_status ?: 'pending', 'url' => $this->kycFileUrl($user->selfie)],
            ],
            'updated_at' => optional($user->updated_at)->toDateTimeString(),
        ];
    }

    private function displayKycStatus(?string $status): string
    {
        return match ($status) {
            'approved' => 'Verified',
            'rejected', 'canceled' => 'Rejected',
            'na', 'n/a', 'not_applicable' => 'N/A',
            default => 'Pending Review',
        };
    }

    private function kycRisk(string $status, array $docStatuses = []): string
    {
        if ($status === 'Rejected' || collect($docStatuses)->contains(fn ($item) => in_array($item, ['rejected', 'canceled'], true))) {
            return 'High';
        }

        return $status === 'Verified' ? 'Low' : 'Medium';
    }

    private function userDisplayName(?User $user): string
    {
        if (! $user) return 'Unknown';

        $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $fullName ?: ($user->name ?: 'Unknown');
    }

    private function kycFileUrl(?string $path): ?string
    {
        if (! $path) return null;
        $path = str_replace('\\', '/', trim($path));
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        if (str_starts_with($path, '/storage/')) return asset(ltrim($path, '/'));
        if (str_starts_with($path, 'storage/')) return asset($path);
        if (str_starts_with($path, '/')) return asset(ltrim($path, '/'));

        return asset('storage/' . ltrim($path, '/'));
    }

    public function updateKycReviewStatus(Request $request, string $source, int $id)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $approvedStatus = 'approved';
        $userToUpgrade = null;

        if ($source === 'wallet') {
            $record = UserWalletKyc::findOrFail($id);
            $record->update(['status' => $data['status']]);
            if ($data['status'] === $approvedStatus) {
                $userToUpgrade = $record->user;
            }
        } elseif ($source === 'organizer') {
            $status = $data['status'] === 'rejected' ? 'canceled' : $data['status'];
            $kyc = OrganizerKyc::findOrFail($id);
            $payload = ['status' => $status];

            if (in_array($status, ['approved', 'pending', 'canceled'], true)) {
                $payload['p_front_status'] = $status;
                $payload['p_back_status'] = $status;
                $payload['p_o_add_status'] = $status;
            }

            $kyc->update($payload);
            if ($status === $approvedStatus) {
                $userToUpgrade = $kyc->organizer?->user;
            }
        } elseif ($source === 'user') {
            $user = User::findOrFail($id);
            $user->update(['kyc_status' => $data['status']]);
            if ($data['status'] === $approvedStatus) {
                $userToUpgrade = $user;
            }
        } else {
            abort(404);
        }

        // AUTO-UPGRADE TO ORGANIZER
        if ($userToUpgrade && $userToUpgrade->type !== 'admin') {
            $userToUpgrade->update(['type' => 'organizer']);
            $userToUpgrade->syncRoles(['organizer']);
            Log::info("User upgraded to organizer via KYC approval", [
                'user_id' => $userToUpgrade->id,
                'source' => $source
            ]);
        }

        return back()->with('message', 'KYC status updated successfully.');
    }

    public function updateKycDocumentStatus(Request $request, string $source, int $id, string $document)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,na'],
        ]);

        $status = $data['status'];
        $recordStatus = $status === 'na' ? 'pending' : $status;

        if ($source === 'organizer') {
            $field = [
                'passport_front' => 'p_front_status',
                'passport_back' => 'p_back_status',
                'proof_of_address' => 'p_o_add_status',
            ][$document] ?? null;

            if (! $field) abort(404);

            OrganizerKyc::findOrFail($id)->update([
                $field => $recordStatus === 'rejected' ? 'canceled' : $recordStatus,
            ]);
        } elseif ($source === 'wallet') {
            UserWalletKyc::findOrFail($id)->update(['status' => $recordStatus]);
        } elseif ($source === 'user') {
            User::findOrFail($id)->update(['kyc_status' => $recordStatus]);
        } else {
            abort(404);
        }

        return back()->with('message', 'KYC document status updated successfully.');
    }

    private function getFlaggedUsersData()
    {
        return FlaggedUser::with([
                'fromUser:id,name,email,first_name,last_name,country,new_country,status,is_active',
                'toUser:id,name,email,first_name,last_name,country,new_country,status,is_active',
            ])
            ->latest()
            ->limit(150)
            ->get()
            ->map(fn (FlaggedUser $report) => $this->mapFlaggedUserRecord($report))
            ->values();
    }

    private function mapFlaggedUserRecord(FlaggedUser $report): array
    {
        $category = $this->flaggedCategory($report);
        $status = $report->status ? 'Resolved' : 'Active';
        $reportedUser = $report->toUser;
        $reporter = $report->fromUser;
        $country = $reportedUser->country ?: ($reportedUser->new_country ?: 'Unknown');

        return [
            'id' => $report->uid ?: 'FLG-' . $report->id,
            'source_id' => $report->id,
            'reportedBy' => $this->flaggedDisplayName($report->from_first_name, $report->from_last_name, $reporter),
            'reportedByEmail' => $reporter->email ?? '',
            'reportedUser' => $this->flaggedDisplayName($report->to_first_Name, $report->to_last_name, $reportedUser),
            'reportedUserEmail' => $reportedUser->email ?? '',
            'country' => $country ?: 'Unknown',
            'message' => $report->message ?: 'No report message provided.',
            'category' => $category,
            'severity' => $this->flaggedSeverity($category, $report->message),
            'status' => $status,
            'raw_status' => $report->status ? 'resolved' : 'active',
            'activeUser' => (bool) ($reportedUser->status ?? false),
            'reportedAt' => optional($report->created_at)->format('Y-m-d H:i'),
            'evidence' => $this->flaggedEvidence($report->selected_reason),
            'action' => $status === 'Resolved' ? 'Case Resolved' : 'Pending Review',
            'notes' => $report->reason ?: 'User-generated flagged account report.',
            'updated_at' => optional($report->updated_at)->format('Y-m-d H:i'),
        ];
    }

    private function flaggedDisplayName(?string $firstName, ?string $lastName, ?User $user): string
    {
        $storedName = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

        return $storedName ?: $this->userDisplayName($user);
    }

    private function flaggedCategory(FlaggedUser $report): string
    {
        $reason = trim((string) $report->reason);
        if ($reason !== '') return $reason;

        $selectedReason = $this->normalizeFlaggedSelectedReason($report->selected_reason);

        return $selectedReason ?: 'User Report';
    }

    private function flaggedEvidence($selectedReason): string
    {
        return $this->normalizeFlaggedSelectedReason($selectedReason) ?: 'Report details submitted by user.';
    }

    private function normalizeFlaggedSelectedReason($selectedReason): string
    {
        if (is_array($selectedReason)) {
            return collect($selectedReason)
                ->filter()
                ->map(fn ($item) => is_array($item) ? implode(', ', array_filter($item)) : (string) $item)
                ->implode(', ');
        }

        if (is_string($selectedReason)) {
            $decoded = json_decode($selectedReason, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeFlaggedSelectedReason($decoded);
            }

            return trim($selectedReason);
        }

        return '';
    }

    private function flaggedSeverity(string $category, ?string $message): string
    {
        $text = strtolower($category . ' ' . $message);

        if (str_contains($text, 'fraud') || str_contains($text, 'scam') || str_contains($text, 'aml') || str_contains($text, 'financial') || str_contains($text, 'threat') || str_contains($text, 'abuse')) {
            return 'High';
        }

        if (str_contains($text, 'harass') || str_contains($text, 'fake') || str_contains($text, 'spam') || str_contains($text, 'inappropriate')) {
            return 'Medium';
        }

        return 'Low';
    }

    public function updateFlaggedUserStatus(Request $request, FlaggedUser $flaggedUser)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,investigating,resolved'],
        ]);

        $flaggedUser->update([
            'status' => $data['status'] === 'resolved',
        ]);

        return back()->with('message', 'Flagged user case updated successfully.');
    }

    public function updateFlaggedUserActivation(Request $request, FlaggedUser $flaggedUser)
    {
        $data = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        $flaggedUser->toUser?->update([
            'status' => $data['active'],
        ]);

        return back()->with('message', 'Reported user activation updated successfully.');
    }

    public function remittanceDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        return Inertia::render('admin/RemittanceDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function settlementsDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        return Inertia::render('admin/SettlementsDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function payoutOpsDashboard(GetAdminOverviewDataAction $action)
    {
        $data = (new \App\Services\Admin\AdminOverviewService(new \App\Repositories\Admin\AdminOverviewRepository()))->getPayoutOpsDashboardData()->toArray();

        return Inertia::render('admin/PayoutOpsDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialPayoutRequests' => $data['payoutRequests'],
            'initialAuditTrails' => $data['auditTrails'],
            'initialTotals' => $data['totals'],
        ]);
    }

    public function payrollDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        return Inertia::render('admin/PayrollDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function pricingDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        return Inertia::render('admin/PricingDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function foundationDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        return Inertia::render('admin/FoundationDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function eventsList(GetAdminEventsDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/EventsDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialEvents' => $data['events'],
            'initialStats' => $data['stats'],
        ]);
    }

    public function eventCategories(GetAdminEventCategoriesDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/EventCategories', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialCategories' => $data['categories'],
            'initialStats' => $data['stats'],
        ]);
    }

    public function ticketSales(GetAdminTicketSalesDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/TicketSales', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialRecords' => $data['records'],
            'initialStats' => $data['stats'],
        ]);
    }

    public function eventSponsors(GetAdminEventSponsorsDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/EventSponsors', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialSponsors' => $data['sponsors'],
            'events' => $data['events'],
            'initialStats' => $data['stats'],
            'appURL' => $data['appURL'],
        ]);
    }

    public function eventCoupons(GetAdminEventCouponsDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/EventCoupons', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialCoupons' => $data['coupons'],
            'events' => $data['events'],
            'initialStats' => $data['stats'],
        ]);
    }

    public function ticketsReport(GetAdminTicketSalesDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/TicketsReport', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialRecords' => $data['records'],
            'initialStats' => $data['stats'],
        ]);
    }

    public function cancelTickets(GetAdminCancelTicketsDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/events/CancelTickets', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialRequests' => $data['requests'],
            'initialOrders' => $data['orders'],
            'initialUsers' => $data['users'],
        ]);
    }

    public function organizerDirectory(GetAdminOrganizerDirectoryDataAction $action)
    {
        $data = $action->execute()->toArray();
        $categories = \App\Models\EventCategory::select('id', 'name')->get();

        return Inertia::render('admin/Organizers/OrganizerDirectory', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialOrganizers' => $data['organizers'],
            'initialStats' => $data['stats'],
            'appURL' => $data['appURL'],
            'categories' => $categories,
        ]);
    }

    public function promoteEvents()
    {
        return Inertia::render('admin/Organizers/PromoteEvents');
    }

    public function scannersManagement(GetAdminScannersManagementDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/Organizers/ScannersManagement', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'initialScanners' => $data['scanners'],
            'organizers' => $data['organizers'],
            'initialStats' => $data['stats'],
            'appURL' => $data['appURL'],
        ]);
    }

    public function storeScanner(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:scan_sign_users,email',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable',
            'status' => 'required|string',
        ]);

        if ($request->hasFile('scanner_image_object')) {
            $data['scanner_image_object'] = $request->file('scanner_image_object')->store('scanners', 'public');
        }

        $data['org_id'] = $data['user_id'] !== null ? (string) $data['user_id'] : '';

        // Map string status to integer for database compatibility
        $data['status'] = $data['status'] === 'Active' ? 1 : 0;

        \App\Models\ScanSignUser::create($data);

        return redirect()->route('admin.scanners-management')->with('message', 'Scanner created successfully.');
    }

    public function updateScanner(Request $request, string $id)
    {
        $scanner = \App\Models\ScanSignUser::findOrFail($id);
        $data = $request->validate([
            'user_id' => 'nullable',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:scan_sign_users,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable',
            'status' => 'required|string',
        ]);

        if ($request->hasFile('scanner_image_object')) {
            $data['scanner_image_object'] = $request->file('scanner_image_object')->store('scanners', 'public');
        }

        $data['org_id'] = $data['user_id'] !== null ? (string) $data['user_id'] : '';

        // Map string status to integer for database compatibility
        $data['status'] = $data['status'] === 'Active' ? 1 : 0;

        $scanner->update($data);

        return redirect()->route('admin.scanners-management')->with('message', 'Scanner updated successfully.');
    }

    public function destroyScanner(string $id)
    {
        $scanner = \App\Models\ScanSignUser::findOrFail($id);
        $scanner->delete();

        return redirect()->route('admin.scanners-management')->with('message', 'Scanner deleted successfully.');
    }

    public function payoutList(Request $request)
    {
        // Get filter parameters
        $filters = $request->only(['search', 'status', 'method', 'event_id']);

        // Build query with filters
        $query = Payout::with(['event', 'organizer.user', 'organizer.bankAccounts'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhereHas('event', function ($q) use ($search) {
                            $q->where('title', 'like', "%{$search}%");
                        })
                        ->orWhereHas('organizer.user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                if ($status !== 'All') {
                    $q->where('status', $status);
                }
            })
            ->when($filters['method'] ?? null, function ($q, $method) {
                $q->where('method', $method);
            })
            ->when($filters['event_id'] ?? null, function ($q, $eventId) {
                $q->where('event_id', $eventId);
            });

        // Get filtered payouts
        $payouts = $query->orderBy('created_at', 'desc')->get();

        // Format payouts for frontend display
        $formattedPayouts = $payouts->map(function ($payout) {
            return [
                'id' => $payout->id,
                'reference' => $payout->reference,
                'amount' => $payout->amount ?? 0,
                'fee_amount' => $payout->fee_amount ?? 0,
                'net_amount' => $payout->net_amount ?? $payout->amount,
                'method' => $payout->method,
                'status' => $payout->status,
                'notes' => $payout->notes,
                'created_at' => $payout->created_at,
                'updated_at' => $payout->updated_at,
                'author' => $payout->author,
                'organizer_id' => $payout->organizer_id,
                'event_id' => $payout->event_id,
                'event' => $payout->event ? [
                    'id' => $payout->event->id,
                    'title' => $payout->event->title ?? '-',
                ] : null,
                'organizer' => $payout->organizer ? [
                    'id' => $payout->organizer->id,
                    'name' => $payout->organizer->user->name ?? '-',
                    'bank' => $payout->organizer->bank ?? '-',
                ] : null,
                'organizer_bank' => $payout->organizer && $payout->organizer->bankAccounts->isNotEmpty() ? [
                    'name' => $payout->organizer->bankAccounts->first()->bank_name ?? '-',
                    'account_number' => $payout->organizer->bankAccounts->first()->account_number ?? '-',
                    'routing_number' => $payout->organizer->bankAccounts->first()->routing_number ?? '-',
                    'paypal_id' => $payout->organizer->bankAccounts->first()->paypal_id ?? '-',
                ] : null,
                'timeline' => [
                    [
                        'step' => 'Payout Requested',
                        'status' => 'pending',
                        'time' => $payout->created_at
                    ],
                    [
                        'step' => 'Moved to Processing',
                        'status' => 'ok',
                        'time' => $payout->updated_at
                    ],
                    [
                        'step' => 'Sent to Bank',
                        'status' => 'pending',
                        'time' => now()->toISOString()
                    ]
                ]
            ];
        });

        // Calculate stats
        $payoutStats = Payout::query()
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as failed")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN COALESCE(net_amount, 0) ELSE 0 END) as paid_total")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN COALESCE(fee_amount, 0) ELSE 0 END) as fees_total")
            ->first();

        $stats = [
            'pending' => (int) ($payoutStats->pending ?? 0),
            'processing' => (int) ($payoutStats->processing ?? 0),
            'completed' => (int) ($payoutStats->completed ?? 0),
            'failed' => (int) ($payoutStats->failed ?? 0),
            'paidTotal' => (float) ($payoutStats->paid_total ?? 0),
            'feesTotal' => (float) ($payoutStats->fees_total ?? 0),
        ];

        // Get organizers and events for filter dropdowns
        $organizers = OrganizerProfile::select('id', 'user_id')->with('user:id,name')->get();
        $events = LinkUpEvent::select('id', 'title')->get();

        return Inertia::render('admin/Organizers/PayoutList', [
            'initialPayouts' => $formattedPayouts,
            'stats' => $stats,
            'organizers' => $organizers,
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    public function vibesManagement(Request $request)
    {
        return Inertia::render('admin/Vibes/VibesManagement', [
            'tab' => $request->query('tab', 'feedDashboardCommand')
        ]);
    }

    public function liveDashboard()
    {
        return Inertia::render('admin/events/LinkUpLive');
    }

    public function topEarners()
    {
        return Inertia::render('admin/events/TopEarners');
    }

    public function wellnessDashboard()
    {
        return Inertia::render('admin/events/WellnessProviders');
    }

    // LinkUp Eats
    public function eatsDashboard()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsDashboard');
    }

    public function eatsLiveApp()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsAppLive');
    }

    public function eatsRestaurants()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsRestaurants');
    }

    public function eatsMenus()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsMenus');
    }

    public function eatsOrders()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsOrders');
    }

    public function eatsDrivers()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsDrivers');
    }

    public function eatsDriverPayouts()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsDriverPayouts');
    }

    public function eatsRestaurantPayouts()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsRestaurantPayouts');
    }

    public function eatsPromotions()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsPromotions');
    }

    public function eatsDeliveryZones()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsDeliveryZones');
    }

    public function eatsFeesPricing()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsFeesPricing');
    }

    public function eatsSupport()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsSupport');
    }

    public function eatsReviews()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsReviews');
    }

    public function eatsAnalytics()
    {
        return Inertia::render('admin/Commerce/LinkUpEats/EatsAnalytics');
    }

    public function restaurantOversight()
    {
        return Inertia::render('admin/Commerce/RestaurantOversight');
    }

    public function restaurantOnboarding()
    {
        return Inertia::render('admin/Commerce/RestaurantOnboarding');
    }

    public function merchantPayOnboarding()
    {
        return Inertia::render('admin/Commerce/MerchantPayOnboarding');
    }

    public function deliveriesDispatch()
    {
        return Inertia::render('admin/Commerce/DeliveriesDispatch');
    }

    public function driverOnboarding()
    {
        return Inertia::render('admin/Commerce/DriverOnboarding');
    }

    public function driverPolicies()
    {
        return Inertia::render('admin/Commerce/DriverPolicies');
    }

    public function shippingManagement()
    {
        return Inertia::render('admin/Commerce/ShippingManagement');
    }

    public function subscriptionSuite(GetAdminOverviewDataAction $action, SubscriptionPlanRepositoryInterface $subRepository, GetSubscribedUsersAction $subUsersAction)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/Commerce/SubscriptionSuite', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'plans' => $subRepository->all(),
            'subscribers' => $subUsersAction->execute(),
        ]);
    }

    public function swipesManagement()
    {
        return Inertia::render('admin/Commerce/SwipesManagement');
    }

    // Marketplace
    public function marketplaceDashboard(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        $orders = \App\Models\Order::with('customer')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->number,
                    'customer' => $order->customer?->name ?? 'Guest',
                    'email' => $order->customer?->email ?? '—',
                    'total' => (float) $order->total,
                    'status' => ucfirst($order->status ?? 'Paid'),
                    'payment' => $order->payment_method ?? 'Card',
                    'date' => $order->created_at->format('M j, Y'),
                    'location' => trim(($order->city ?? '') . (($order->city && $order->country) ? ', ' : '') . ($order->country ?? '')) ?: '—',
                ];
            });

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceDashboard', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'orders' => $orders,
        ]);
    }

    public function marketplaceProducts(GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceProducts', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
        ]);
    }

    public function marketplaceCategories()
    {
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceCategories');
    }

    public function marketplaceFees()
    {
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceFees');
    }

    public function marketplaceOrders()
    {
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceOrders');
    }

    public function marketplaceEscrow()
    {
        return Inertia::render('admin/Commerce/Marketplace/MarketplaceEscrow');
    }

    public function marketplaceSellerTransfers(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        $transfers = WithdrawRequest::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            })
            ->when($request->status && $request->status !== 'All Status', function ($q, $status) {
                $q->where('request_status', strtolower($status));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalPayouts = WithdrawRequest::where('request_status', 'approved')->sum('amount');

        // Simple average fee calculation (total fees / total volume)
        $totalVolume = Order::where('status', '!=', 'cancelled')->sum('total');
        $totalFees = Order::where('status', '!=', 'cancelled')->sum('fee_amount');
        $avgFeePercent = $totalVolume > 0 ? ($totalFees / $totalVolume) * 100 : 0;

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceSellerTransfers', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'transfers' => [
                'data' => collect($transfers->items())->map(fn($t) => [
                    'id' => 'TR-' . (9000 + $t->id),
                    'seller' => $t->user?->name ?? 'Unknown',
                    'method' => 'Bank', // Default for now
                    'amount' => (float)$t->amount,
                    'fee' => 0.0, // WithdrawRequests might not have individual fees tracked yet
                    'net' => (float)$t->amount,
                    'status' => ucfirst($t->request_status)
                ]),
                'meta' => [
                    'total' => $transfers->total(),
                    'links' => $transfers->linkCollection()->toArray()
                ]
            ],
            'stats' => [
                'total_payouts' => (float)$totalPayouts,
                'avg_fee' => (float)$avgFeePercent
            ],
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function marketplaceWallets(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        $wallets = Merchants::query()
            ->where('is_active', true)
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(function ($merchant) {
                $user = User::find($merchant->user_id);

                // Escrow Held (Orders not yet delivered)
                $held = OrderItem::whereHas('product', function($q) use ($merchant) {
                    $q->where('seller_owner', $merchant->id);
                })->whereHas('order', function($q) {
                    $q->whereNotIn('status', ['completed', 'received_buyer', 'delivered', 'cancelled', 'refunded']);
                })->sum('sub_total');

                // Total Payouts (Approved WithdrawRequests)
                $paidOut = WithdrawRequest::where('user_id', $merchant->user_id)
                    ->where('request_status', 'approved')
                    ->sum('amount');

                return [
                    'seller' => $merchant->name,
                    'available' => (float)($user ? $user->balance : 0),
                    'held' => (float)$held,
                    'paidOut' => (float)$paidOut,
                    'currency' => 'USD', // Default
                    'status' => 'Active'
                ];
            });

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceWallets', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'wallets' => $wallets,
            'filters' => $request->only(['search']),
        ]);
    }

    public function marketplaceSellers(Request $request, GetAdminOverviewDataAction $action)
    {
        $data = $action->execute()->toArray();

        $sellers = Merchants::query()
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $formattedSellers = collect($sellers->items())->map(function ($merchant) {
            $revenue = OrderItem::whereHas('product', function($q) use ($merchant) {
                $q->where('seller_owner', $merchant->id);
            })->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })->sum('sub_total');

            return [
                'id' => 'SEL-' . str_pad($merchant->id, 3, '0', STR_PAD_LEFT),
                'name' => $merchant->name,
                'email' => $merchant->email ?? '—',
                'country' => $merchant->country ?? '—',
                'products' => Product::where('seller_owner', $merchant->id)->count(),
                'revenue' => (float)$revenue,
                'kyc' => $merchant->is_active ? 'Verified' : 'Investigating',
                'status' => $merchant->is_active ? 'Active' : 'Inactive',
                'raw_id' => $merchant->id
            ];
        });

        return Inertia::render('admin/Commerce/Marketplace/MarketplaceSellers', [
            'initialUnits' => $data['units'],
            'initialCountries' => $data['countries'],
            'sellers' => [
                'data' => $formattedSellers,
                'meta' => [
                    'total' => $sellers->total(),
                    'links' => $sellers->linkCollection()->toArray()
                ]
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    // Merchants
    public function merchantDashboard()
    {
        return Inertia::render('admin/Commerce/Merchants/MerchantDashboard');
    }

    public function merchantDirectory()
    {
        return Inertia::render('admin/Commerce/Merchants/MerchantDirectory');
    }

    public function merchantOnboarding()
    {
        return Inertia::render('admin/Commerce/Merchants/MerchantOnboarding');
    }

    public function merchantKyc()
    {
        return Inertia::render('admin/Commerce/Merchants/MerchantKyc');
    }

    public function merchantTax()
    {
        return Inertia::render('admin/Commerce/Merchants/MerchantTax');
    }

    private function getDefaultLegalPages()
    {
        return [
            ['key' => 'privacy-policy', 'title' => 'Privacy Policy'],
            ['key' => 'terms-of-service', 'title' => 'Terms of Service'],
            ['key' => 'acceptable-use-policy', 'title' => 'Acceptable Use Policy'],
            ['key' => 'prohibited-activities', 'title' => 'Prohibited Activities'],
            ['key' => 'refund-policy', 'title' => 'Refund Policy'],
            ['key' => 'law-enforcement-guidelines', 'title' => 'Law Enforcement Guidelines'],
            ['key' => 'pricing-and-fees', 'title' => 'Pricing & Fees'],
            ['key' => 'contact-and-customer-support', 'title' => 'Contact & Support'],
            ['key' => 'vibes-acceptable-use-policy', 'title' => 'Vibes Acceptable Use Policy'],
        ];
    }

    public function getLegalPages()
    {
        $dbPages = Settings::where('key', 'like', 'legal_page_%')->get()->keyBy(function($item) {
            return str_replace('legal_page_', '', $item->key);
        });

        $defaults = $this->getDefaultLegalPages();
        $pages = [];

        foreach ($defaults as $d) {
            $key = $d['key'];
            if (isset($dbPages[$key])) {
                $val = $dbPages[$key]->value;
                $pages[] = [
                    'key' => $key,
                    'title' => is_array($val) ? ($val['title'] ?? $d['title']) : $d['title'],
                    'content' => is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : ''),
                    'is_default' => false,
                    'is_visible' => is_array($val) ? ($val['is_visible'] ?? true) : true,
                    'updated_at' => is_array($val) ? ($val['updated_at'] ?? null) : null,
                ];
            } else {
                $pages[] = [
                    'key' => $key,
                    'title' => $d['title'],
                    'content' => '',
                    'is_default' => true,
                    'is_visible' => true,
                    'updated_at' => null,
                ];
            }
        }

        // Add custom (non-default) pages from database
        foreach ($dbPages as $key => $setting) {
            $found = false;
            foreach ($defaults as $d) {
                if ($d['key'] === $key) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $val = $setting->value;
                $pages[] = [
                    'key' => $key,
                    'title' => is_array($val) ? ($val['title'] ?? $key) : $key,
                    'content' => is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : ''),
                    'is_default' => false,
                    'is_custom' => true,
                    'is_visible' => is_array($val) ? ($val['is_visible'] ?? true) : true,
                    'updated_at' => is_array($val) ? ($val['updated_at'] ?? null) : null,
                ];
            }
        }

        return response()->json($pages);
    }

    public function saveLegalPage(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'title' => 'required|string',
            'content' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
        ]);

        $key = $request->input('key');
        $settingKey = 'legal_page_' . $key;

        Settings::updateOrCreate(
            ['key' => $settingKey],
            ['value' => [
                'title' => $request->input('title'),
                'content' => $request->input('content', ''),
                'is_visible' => $request->input('is_visible', true),
                'updated_at' => now()->toDateTimeString(),
            ]]
        );

        return response()->json(['success' => true]);
    }

    public function deleteLegalPage($key)
    {
        $settingKey = 'legal_page_' . $key;
        Settings::where('key', $settingKey)->delete();
        return response()->json(['success' => true]);
    }
}
