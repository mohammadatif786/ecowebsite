<?php

namespace App\DTOs\User\Signup;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * The wizard's final save: the member, and the request that carries the photo files
 * (more_photos). Every other answer is read from the saved progress, never from the browser.
 */
final readonly class CompleteOnboardingData
{
    public function __construct(
        public User $user,
        public Request $request,
    ) {}
}
