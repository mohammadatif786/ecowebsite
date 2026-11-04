<?php

namespace App\DTOs\User\Signup;

final readonly class OnboardingState
{
    public function __construct(
        public OnboardingStage $stage,
        public bool $emailVerified,
        public bool $phoneVerified,
    ) {}

    public function isComplete(): bool
    {
        return $this->stage === OnboardingStage::Complete;
    }

    /** Named route a member in this stage must be sent to, or null when nothing blocks them. */
    public function redirectRoute(): ?string
    {
        return match ($this->stage) {
            OnboardingStage::Email => 'verification.notice',
            OnboardingStage::Profile => 'frontend.users.wizard',
            OnboardingStage::Complete => null,
        };
    }
}
