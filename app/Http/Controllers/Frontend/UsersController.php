<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\UserNotificationAction;
use App\Actions\WizardUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\WizardUpdateFormRequest;
use App\Models\CaribbeanIsland;
use App\Models\FlaggedUser;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\KycService;
use App\Services\StoreOptimizedImage;

class UsersController extends Controller
{
    protected $wizardAction;
    protected $storeOptimized;

    public function __construct(WizardUpdateAction $wizardAction, StoreOptimizedImage $storeOptimized)
    {
        $this->wizardAction = $wizardAction;
        $this->storeOptimized = $storeOptimized;
    }


    public function index()
    {
        return Inertia::render('User/Users');
    }
    public function wizard()
    {
        $caribbeans = CaribbeanIsland::all();
        return Inertia::render('User/Wizard', [
            'caribbeans' => $caribbeans
        ]);
    }

    public function wizardUpdate(WizardUpdateFormRequest $request)
    {
        $validated = $request->validated();

        $response = $this->wizardAction->update($validated, $request);

        return redirect()->intended(route('new_frontend.home', absolute: false));
    }

    public function verifyKyc()
    {
        $userKycSubmitted = Auth::user()->kyc_submitted ?? false;
        return Inertia::render('User/VerifyKyc', [
            'userKycSubmitted' => $userKycSubmitted,
        ]);
    }

    public function submitVerifyKyc(Request $request, KycService $kycService)
    {
        $validated = $request->validate([
            'front_side' => 'required|image|mimes:jpeg,png|max:5120',
            'back_side' => 'required|image|mimes:jpeg,png|max:5120',
            'address_proof' => 'required|image|mimes:jpeg,png|max:5120',
            'selfie' => 'nullable|image|mimes:jpeg,png|max:5120',
        ]);

        $kycService->submitKyc($validated, $request->allFiles());

        return redirect()->route('frontend.user.verify.kyc')
            ->with('success', 'KYC documents submitted successfully.');
    }
    public function userNotifation(Request $request, UserNotificationAction $action)
    {
        $notifications = $action->getNotications($request);

        return Inertia::render('User/Notification/Index', [
            'allnotifications' => $notifications['allnotifications'],
            'appURL' => $notifications['appURL']
        ]);
    }

    // for flaged user entrires
    public function falgedUser(Request $request)
    {
        $request->validate([
            'reported_user_id' => 'required|integer',
            'message' => 'required|string',
            'reason' => 'required|string',
            'selected_reason' => 'required|string',
        ], [
            'reported_user_id.required' => 'User ID is required',
            'message.required' => 'Please provide details about the report',
            'reason.required' => 'Please select a reason for reporting',
            'selected_reason.required' => 'Please select context areas',
        ]);

        $user = User::where('id', $request->reported_user_id)->where('status', true)->first();
        if ($user) {
            $from_user = Auth::user();
            $flaggedUser = FlaggedUser::create([
                'from_user_id' => $from_user->id,
                'to_user_id' => $user->id,
                'from_first_name' => $from_user->first_name,
                'from_last_name' => $from_user->last_name,
                'to_first_Name' => $user->first_name,
                'to_last_name' => $user->last_name,
                'message' => $request->message,
                'reason' => $request->reason,
                'selected_reason' => $request->selected_reason,
            ]);

            if ($flaggedUser) {
                return back()->with('success', 'User Reported Successfully');
            }
        } else {
            return back()->with('error', 'User not found or inactive');
        }
    }
    public function destroy($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return back()->with('error', 'Notification not found.');
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found.'], 404);
        }

        $notification->update(['unread' => false]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        Notification::where('unread', true)->update(['unread' => false]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:notifications,id'
        ]);

        Notification::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected notifications deleted.');
    }

    public function bulkMarkAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:notifications,id'
        ]);

        Notification::whereIn('id', $request->ids)->update(['unread' => false]);

        return back()->with('success', 'Selected notifications marked as read.');
    }
}
