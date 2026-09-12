<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrganizerKyc;
use App\Models\OrganizerProfile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KycUserController extends Controller
{
    // Declare to satisfy linter; not used in methods we modified
    protected $policy;
    protected $db;
    public function users(Request $request)
    {
        $users = User::query()
            ->where('type', 'user')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/kycusers/Users', [
            'users' => $users,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function changeStatus(Request $request, OrganizerKyc $kyc)
    {
        Gate::authorize('updateStatus', $kyc);

        $kyc->update([ 'status' =>   $request->status ]);

        $organizerProfile = OrganizerProfile::findOrFail($kyc->organizer_id);
        $userToUpdate = User::findOrfail($organizerProfile->user_id);
        
        if($request->status === 'approved') {
            $userToUpdate->syncRoles(['organizer']);
        }else {
            $userToUpdate->syncRoles(['user']);
        }

        $userToUpdate->update([
            'type' => $request->status === 'approved' ? 'organizer' : 'user'
        ]);

        return redirect()->back()->with(['success' => 'Status updated successfully']);
    }

    public function organizers(Request $request)
    {
        $appURL = asset('storage').'/';
        
        $users = OrganizerProfile::query()->with([
            'user',
            'media',
            'contacts',
            'settings',
            'bankAccounts',
            'kyc'
        ])->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/kycusers/Organizers', [
            'users' => $users,
            'filters' => $request->only('search'),
            'message' => session('message'),
            'appURL' => $appURL
        ]);
    }

    public function updateOrganizerKycFieldStatus(Request $request, OrganizerKyc $organizerKyc)
    {
        Gate::authorize('updateStatus', $organizerKyc);
        
        $validated = $request->validate([
            'field' => ['required', Rule::in(['p_front_status', 'p_back_status', 'p_o_add_status'])],
            'status' => ['required', Rule::in(['pending', 'approved', 'canceled'])],
        ]);
        
        $field = $validated['field'];
        $organizerKyc->{$field} = $validated['status'];
        $organizerKyc->save();

        return redirect()->back()->with([
            'messages' => [
                'title' => 'Status updated successfully',
            ],
        ]);
    }

    public function user_status($uid, $status)
    {
        if (!$this->policy->write()) {
            abort(403);
        }

        // Update Status
        $userRef = $this->db->collection('users')->document($uid);
        $userRef->update([
            ['path' => 'kyc_status', 'value' => $status]
        ]);
        return redirect()->back()->with(['success' => 'Status updated successfully']);
    }
    public function organizer(Request $request)
    {
        $users = User::query()
            ->where('type', 'organizer')
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/kycusers/Organizer', [
            'users' => $users,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }
    public function organizer_status($uid, $status)
    {
        if (!$this->policy->write()) {
            abort(403);
        }

        // Update Status
        $userRef = $this->db->collection('orgSignUsers')->document($uid);
        $userRef->update([
            ['path' => 'kyc_status', 'value' => $status]
        ]);
        return redirect()->back()->with(['success' => 'Status updated successfully']);
    }
    public function destroy(string $id, OrganizerKyc $kyc)
    {
        Gate::authorize('updateStatus', $kyc);

        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with(['success' => 'KYC user deleted successfully']);
    }
}
