<?php

namespace App\Http\Requests\User\Signup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The live "is this LinkUp ID free?" lookup on the Create account tab.
 * The ID arrives without the leading @.
 */
class CheckLinkupIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('linkup_id'))) {
            $this->merge(['linkup_id' => ltrim(strtolower(trim($this->input('linkup_id'))), '@')]);
        }
    }

    public function rules(): array
    {
        return [
            'linkup_id' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/'],
        ];
    }
}
