<?php

namespace App\DTOs\User\Signup;

use App\Models\User;

/**
 * One wizard step's answers, already validated, ready to be saved.
 */
final readonly class WizardStepData
{
    /**
     * @param  array<string, mixed>  $answers
     */
    public function __construct(
        public User $user,
        public WizardStep $step,
        public array $answers,
        public bool $skipped = false,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(User $user, WizardStep $step, array $validated): self
    {
        $skipped = (bool) ($validated['skip'] ?? false);
        unset($validated['skip']);

        return new self($user, $step, $skipped ? [] : $validated, $skipped);
    }
}
