<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisteredUserAction;
use App\Actions\User\Signup\RegisterMemberAction;
use App\DTOs\User\Signup\RegisterMemberData;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisteredUserFormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    protected $registeredAction;

    public function __construct(RegisteredUserAction $registeredAction)
    {
        $this->registeredAction = $registeredAction;
    }
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('User/Auth/Register');
    }

    /**
     * Check if linkup_id is available.
     */
    public function checkLinkupId(Request $request)
    {
        $request->validate([
            'linkup_id' => 'required|string|min:3|max:20|regex:/^[a-z0-9_]+$/',
        ]);

        $exists = $this->registeredAction->validateLinkUpId($request);

        return response()->json([
            'available' => $exists['available'],
            'reason' => $exists['reason']
        ], 200);
    }

    /**
     * "Create account". The member is created and signed in, then held on the Verify email page
     * until the 6-digit code is entered. The mobile app's register endpoint keeps using
     * RegisteredUserAction; this is the website's path.
     */
    public function store(RegisteredUserFormRequest $request, RegisterMemberAction $registerMember): RedirectResponse
    {
        $registerMember->execute(RegisterMemberData::fromValidated($request->validated()));

        return redirect()->route('verification.notice');
    }
}
