<?php

namespace App\Http\Controllers\User\Signup;

use App\Actions\User\Signup\CompleteOnboardingAction;
use App\Actions\User\Signup\LoadWizardProgressAction;
use App\Actions\User\Signup\LocateMemberAction;
use App\Actions\User\Signup\SaveWizardStepAction;
use App\DTOs\User\Signup\CompleteOnboardingData;
use App\DTOs\User\Signup\WizardDonePageData;
use App\DTOs\User\Signup\WizardPageData;
use App\DTOs\User\Signup\WizardStep;
use App\DTOs\User\Signup\WizardStepData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Signup\CompleteWizardRequest;
use App\Http\Requests\User\Signup\SaveWizardStepRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The 16-step profile wizard after "Verify email". Answers are saved on the server step by step,
 * so the member can leave and pick up where they stopped, on any device.
 */
class WizardController extends Controller
{
    public function show(Request $request, LoadWizardProgressAction $loadProgress): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->is_wizard_completed) {
            return redirect()->route('user.feed');
        }

        $page = WizardPageData::make($user, $loadProgress->execute($user), $request->query('step'));

        return Inertia::render('User/Signup/Wizard', $page->toArray());
    }

    public function saveStep(SaveWizardStepRequest $request, SaveWizardStepAction $saveStep): RedirectResponse
    {
        $step = $request->step();
        $saveStep->execute(WizardStepData::fromValidated($request->user(), $step, $request->validated()));

        return redirect()->route('frontend.users.wizard', ['step' => $step->next()->value]);
    }

    public function locate(Request $request, LocateMemberAction $locate): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $place = $locate->execute((float) $validated['latitude'], (float) $validated['longitude']);

        if (! $place) {
            return response()->json(['message' => "We couldn't find your city. Pick it below instead."], 422);
        }

        return response()->json($place);
    }

    public function complete(CompleteWizardRequest $request, CompleteOnboardingAction $complete): RedirectResponse
    {
        $complete->execute(new CompleteOnboardingData($request->user(), $request));

        return redirect()->route('frontend.wizard.done');
    }

    public function done(Request $request, LoadWizardProgressAction $loadProgress): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user->is_wizard_completed) {
            return redirect()->route('frontend.users.wizard');
        }

        $intents = $loadProgress->execute($user)->answers[WizardStep::Intent->value]['intents'] ?? [];

        return Inertia::render('User/Signup/WizardDone', (new WizardDonePageData($user, $intents))->toArray());
    }
}
