<?php

namespace App\Http\Requests\User\Signup;

use App\Actions\User\Signup\CheckLinkupIdAction;
use App\Http\Requests\RegisteredUserFormRequest;
use Illuminate\Validation\Validator;

/**
 * "Create account" on the website. Same rules as the shared sign-up request (which the mobile
 * app also uses), plus two the website enforces on the server:
 *  - the member must agree to the Terms, Privacy Policy and Acceptable Use Policy;
 *  - the LinkUp ID must pass the same reserved-word and format checks as the live availability
 *    check, so skipping the page and posting directly does not get around them.
 */
class RegisterMemberRequest extends RegisteredUserFormRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'terms' => ['accepted'],
        ]);
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => "Agree to LinkUp's Terms of Service, Privacy Policy and Acceptable Use Policy to continue.",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('linkup_id')) {
                return;
            }

            $availability = app(CheckLinkupIdAction::class)->execute((string) $this->input('linkup_id'));

            if (! $availability->available) {
                $validator->errors()->add('linkup_id', $availability->reason ?? 'That LinkUp ID is not available.');
            }
        });
    }
}
