<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\CheckLinkupIdAction;
use App\Actions\User\Signup\RegisterMemberAction;
use App\DTOs\User\Signup\AccountPageData;
use App\DTOs\User\Signup\RegisterMemberData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Signup\CheckLinkupIdRequest;
use App\Http\Requests\User\Signup\RegisterMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The "Create account" tab. Everything after the click happens on the Verify email page.
 */
class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('User/Signup/Account', AccountPageData::create()->toArray());
    }

    public function store(RegisterMemberRequest $request, RegisterMemberAction $registerMember): RedirectResponse
    {
        $registerMember->execute(RegisterMemberData::fromValidated($request->validated()));

        return redirect()->route('verification.notice');
    }

    public function checkLinkupId(CheckLinkupIdRequest $request, CheckLinkupIdAction $checkLinkupId): JsonResponse
    {
        return response()->json($checkLinkupId->execute($request->validated('linkup_id'))->toArray());
    }
}
