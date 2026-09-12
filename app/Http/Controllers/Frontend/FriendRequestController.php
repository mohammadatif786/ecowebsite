<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\FriendRequestAction;
use App\Domain\Linkup\Actions\RespondFriendRequestAction;
use App\Http\Controllers\Controller;
use App\Models\Frontend\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Notification;

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

        return Inertia::render('User/FriendRequest/Index', [
            'friendRequests' => $friendRequests,
            'sentRequests' => $sentRequests,
        ]);
    }

    public function sendRequest(string $slug, string $user)
    {
        $this->requestAction->sendRequest($slug, $user);

        return back()->with('message', 'Friend request sent successfully!');
    }

    public function acceptRequest(FriendRequest $friendRequest)
    {
        $user = Auth::user();
        abort_unless($user->can('respond', $friendRequest), 403);
        $this->respondAction->execute($user, $friendRequest, true);

        return back()->with('message', 'Friend request accepted successfully!');
    }
    public function rejectRequest(FriendRequest $friendRequest)
    {
        abort_unless(Auth::user()->can('respond', $friendRequest), 403);
        $this->respondAction->execute(Auth::user(), $friendRequest, false);

        return back()->with('message', 'Friend request rejected successfully!');
    }
}
