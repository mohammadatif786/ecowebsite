<?php

namespace App\Actions\User\Signup;

use App\Models\OnboardingProgress;
use App\Models\User;

/**
 * The member's saved wizard answers, created empty on the first visit.
 */
class LoadWizardProgressAction
{
    public function execute(User $user): OnboardingProgress
    {
        return OnboardingProgress::firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 0, 'answers' => []],
        );
    }
}
