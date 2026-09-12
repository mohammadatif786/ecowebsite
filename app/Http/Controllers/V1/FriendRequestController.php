<?php

namespace App\Http\Controllers\V1;

use App\Actions\FriendRequestAction;
use App\Domain\Linkup\Actions\RespondFriendRequestAction;
use App\Http\Controllers\Controller;
use App\Models\FlaggedUser;
use App\Models\Frontend\FriendRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendRequestController extends Controller
{
    protected $requestAction;

    public function __construct(FriendRequestAction $requestAction, protected RespondFriendRequestAction $respondAction)
    {
        $this->requestAction = $requestAction;
    }

    public function index()
    {
        $friendRequests = FriendRequest::where('receiver_id', Auth::id())
            ->whereIn('status', [0, 1])
            ->with(['user:id,name,avatar,country,age,linkup_id'])
            ->latest()
            ->get();

        $sentRequests = FriendRequest::where('user_id', Auth::id())
            ->whereIn('status', [0, 1])
            ->with(['receiver:id,name,avatar,country,age,linkup_id'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'friendRequests' => $friendRequests,
            'sentRequests' => $sentRequests,
        ]);
    }

    public function sendRequest(string $slug, string $user)
    {
        $this->requestAction->sendRequest($slug, $user);

        return response()->json(['status' => true, 'message' => 'Friend request sent successfully!']);
    }

    public function acceptRequest(FriendRequest $friendRequest)
    {
        $user = Auth::user();
        abort_unless($user->can('respond', $friendRequest), 403);
        $this->respondAction->execute($user, $friendRequest, true);

        return response()->json([
            'status' => true,
            'message' => 'Friend request accepted successfully!',
        ]);
    }

    public function rejectRequest(FriendRequest $friendRequest)
    {
        abort_unless(Auth::user()->can('respond', $friendRequest), 403);
        $this->respondAction->execute(Auth::user(), $friendRequest, false);

        return response()->json([
            'status' => true,
            'message' => 'Friend request rejected successfully!',
        ]);
    }

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
                return response()->json(['status' => true, 'message' => 'User Reported Successfully'], 200);
            }
        } else {
            return response()->json(['status' => false,  'error' => 'User not found or inactive'], 402);
        }
    }
}
