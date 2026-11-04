<?php

namespace App\DTOs\User\Signup;

/**
 * The 16 wizard steps, in the order the "LinkUp Onboarding" design shows them.
 * Step 1 of 17 is "Create account", so wizard step N is shown as "Step N+2 of 17".
 *
 * The string value is what the URL and the saved progress use; it never changes when steps
 * are reordered.
 */
enum WizardStep: string
{
    case Home = 'home';
    case Languages = 'languages';
    case Phone = 'phone';
    case Birthday = 'birthday';
    case Interests = 'interests';
    case Gender = 'gender';
    case Distance = 'distance';
    case AgeRange = 'age_range';
    case ShowMe = 'show_me';
    case Roots = 'roots';
    case LinkWith = 'link_with';
    case Bio = 'bio';
    case Work = 'work';
    case University = 'university';
    case Intent = 'intent';
    case Photos = 'photos';

    /** 0-based position in the wizard. */
    public function index(): int
    {
        return array_search($this, self::cases(), true);
    }

    /** Steps the design lets the member skip with "Skip for now". */
    public function isOptional(): bool
    {
        return in_array($this, [self::Interests, self::Bio, self::Work, self::University], true);
    }

    public function next(): ?self
    {
        return self::cases()[$this->index() + 1] ?? null;
    }

    public static function fromIndex(int $index): self
    {
        $cases = self::cases();

        return $cases[max(0, min($index, count($cases) - 1))];
    }

    /** The last step is saved together with the photos by the final "Finish" request. */
    public function isLast(): bool
    {
        return $this === self::Photos;
    }
}
