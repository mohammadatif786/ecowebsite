<?php

namespace App\Http\Requests\User\Signup;

use App\Http\Requests\WizardUpdateFormRequest;

/**
 * The wizard's final save on the website. Same fields as the shared WizardUpdateFormRequest
 * (the mobile app keeps using that one) plus the age rule, enforced on the server so a crafted
 * request cannot skip the birthday check the page shows.
 */
class CompleteOnboardingRequest extends WizardUpdateFormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['birthday'] = [
            'required',
            'date',
            'before_or_equal:'.now()->subYears((int) config('signup.minimum_age'))->toDateString(),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'birthday.before_or_equal' => 'You need to be '.config('signup.minimum_age').' or older to join LinkUp.',
        ]);
    }
}
