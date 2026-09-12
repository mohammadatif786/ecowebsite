<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisteredUserAction;
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

    public function store(RegisteredUserFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->registeredAction->execute($validated);

        // return redirect(route('frontend.home.matches', absolute: false));
        return redirect(route('new_frontend.home', absolute: false));
    }
}
