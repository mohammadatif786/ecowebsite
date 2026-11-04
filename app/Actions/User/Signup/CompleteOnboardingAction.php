<?php

namespace App\Actions\User\Signup;

use App\Actions\WizardUpdateAction;
use App\DTOs\User\Signup\CompleteOnboardingData;
use App\DTOs\User\Signup\OnboardingProfileData;
use App\DTOs\User\Signup\OnboardingStage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * The wizard's final save ("Finish and start linking up").
 *
 * The profile is built from the answers saved step by step on the server, never from what the
 * browser sends at the end. Before anything is written the server checks again that
 *   - the email is confirmed,
 *   - every required step was answered,
 *   - the phone is verified (the saved number is always the verified one),
 *   - the member is old enough.
 * The existing WizardUpdateAction (shared with the mobile app) then stores the profile and the
 * photos and marks the wizard complete.
 */
class CompleteOnboardingAction
{
    public function __construct(
        private readonly ResolveOnboardingStateAction $resolveState,
        private readonly LoadWizardProgressAction $loadProgress,
        private readonly ResolveHomeCoordinatesAction $resolveCoordinates,
        private readonly WizardUpdateAction $wizardUpdate,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(CompleteOnboardingData $data): User
    {
        $user = $data->user;

        if ($this->resolveState->execute($user)->stage === OnboardingStage::Email) {
            throw ValidationException::withMessages([
                'email' => 'Confirm your email before finishing your profile.',
            ]);
        }

        $answers = $this->loadProgress->execute($user)->answers ?? [];

        $missing = OnboardingProfileData::missingSteps($answers);
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'step' => 'Some steps are not finished yet. Go back and answer them first.',
            ]);
        }

        if ($user->phone_verified_at === null || blank($user->phone_number)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Verify your phone number to continue.',
            ]);
        }

        $profile = OnboardingProfileData::fromAnswers($answers);
        $columns = $profile->columns;

        $minimumAge = (int) config('signup.minimum_age');
        if (! $columns['birthday'] || Carbon::parse($columns['birthday'])->age < $minimumAge) {
            throw ValidationException::withMessages([
                'birthday' => "You need to be {$minimumAge} or older to join LinkUp.",
            ]);
        }

        $columns['phone_number'] = $user->phone_number;

        // Distance matching needs a map position. Use the browser's when the member shared it,
        // otherwise look up the home city. Set before the profile writer runs, so it uses this
        // position instead of guessing one from the IP address.
        $position = $profile->latitude !== null
            ? ['latitude' => $profile->latitude, 'longitude' => $profile->longitude]
            : $this->resolveCoordinates->execute($columns['city'], $columns['country']);

        if ($position) {
            $user->forceFill($position)->save();
        }

        return $this->wizardUpdate->update($columns, $data->request);
    }
}
