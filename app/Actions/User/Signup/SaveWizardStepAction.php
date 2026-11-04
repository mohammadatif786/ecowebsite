<?php

namespace App\Actions\User\Signup;

use App\DTOs\User\Signup\WizardStep;
use App\DTOs\User\Signup\WizardStepData;
use App\Models\OnboardingProgress;
use Illuminate\Validation\ValidationException;

/**
 * Saves one wizard step ("Continue" or "Skip for now") and unlocks the next one.
 *
 * The server decides what counts as done, not the page:
 *   - a step can only be saved once every step before it is saved, so no step can be jumped;
 *   - only the optional steps can be skipped;
 *   - the phone step is done only when the number really is verified.
 */
class SaveWizardStepAction
{
    public function __construct(private readonly LoadWizardProgressAction $loadProgress) {}

    /**
     * @throws ValidationException
     */
    public function execute(WizardStepData $data): OnboardingProgress
    {
        $progress = $this->loadProgress->execute($data->user);
        $step = $data->step;

        if ($step->index() > $progress->current_step) {
            throw ValidationException::withMessages(['step' => 'Finish the earlier steps first.']);
        }

        if ($step->isLast()) {
            throw ValidationException::withMessages(['step' => 'Add your photos and tap Finish.']);
        }

        if ($data->skipped && ! $step->isOptional()) {
            throw ValidationException::withMessages(['step' => "This step can't be skipped."]);
        }

        $answers = $data->answers;

        if ($step === WizardStep::Phone) {
            $user = $data->user->fresh();
            if ($user->phone_verified_at === null || blank($user->phone_number)) {
                throw ValidationException::withMessages(['phone_number' => 'Verify your phone number to continue.']);
            }
            $answers = ['phone_number' => $user->phone_number];
        }

        $all = $progress->answers ?? [];
        $all[$step->value] = $data->skipped ? ['skipped' => true] : $answers;

        $progress->answers = $all;
        $progress->current_step = max($progress->current_step, $step->next()->index());
        $progress->save();

        return $progress;
    }
}
