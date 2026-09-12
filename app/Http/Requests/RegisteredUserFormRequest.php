<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisteredUserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize the linkup_id before validation so the 'unique' rule
     * checks against the actual stored format (@handle).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('linkup_id')) {
            $raw = strtolower(trim($this->linkup_id));
            $raw = ltrim($raw, '@');              // strip any leading @
            $this->merge(['linkup_id' => '@' . $raw]);  // store WITH @ for unique check
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'linkup_id' => 'required|string|min:4|max:21|regex:/^@[a-z0-9_]+$/|unique:users,linkup_id',
            'password' => ['required', 'confirmed', Rules\Password::min(8)
                ->letters()
                ->numbers()
                ->symbols()
                ->mixedCase()
                ->uncompromised()],
        ];
    }
}
