<?php

namespace App\DTOs\User\Signup;

use App\Models\OnboardingProgress;
use App\Models\User;

/**
 * Props for the wizard page (User/Signup/Wizard).
 */
final readonly class WizardPageData
{
    public function __construct(
        public User $user,
        public OnboardingProgress $progress,
        public WizardStep $step,
    ) {}

    /**
     * The step to show: the one asked for, unless it is still locked, then the furthest one
     * the member has reached.
     */
    public static function make(User $user, OnboardingProgress $progress, ?string $requested): self
    {
        $furthest = WizardStep::fromIndex($progress->current_step);
        $step = $requested ? WizardStep::tryFrom($requested) : null;

        if (! $step || $step->index() > $furthest->index()) {
            $step = $furthest;
        }

        return new self($user, $progress, $step);
    }

    public function toArray(): array
    {
        return [
            'step' => $this->step->value,
            'furthest' => WizardStep::fromIndex($this->progress->current_step)->value,
            'steps' => array_map(fn (WizardStep $s) => ['key' => $s->value, 'optional' => $s->isOptional()], WizardStep::cases()),
            'answers' => (object) ($this->progress->answers ?? []),
            'member' => [
                'firstName' => (string) $this->user->first_name,
                'lastName' => (string) $this->user->last_name,
                'phone' => $this->user->phone_number,
                'phoneVerified' => $this->user->phone_verified_at !== null,
            ],
            'options' => WizardOptions::toArray(),
            'minimumAge' => (int) config('signup.minimum_age'),
            'phoneResendSeconds' => (int) config('signup.phone_otp.resend_cooldown_seconds'),
            'urls' => [
                'wizard' => route('frontend.users.wizard'),
                'saveStep' => route('frontend.wizard.step', ['step' => '__STEP__']),
                'complete' => route('frontend.wizard.update'),
                'locate' => route('frontend.wizard.locate'),
                'phoneSend' => route('frontend.wizard.phone.send'),
                'phoneVerify' => route('frontend.wizard.phone.verify'),
                'logout' => route('logout'),
            ],
        ];
    }
}
