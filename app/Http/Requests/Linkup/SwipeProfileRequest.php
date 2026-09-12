<?php

namespace App\Http\Requests\Linkup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwipeProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return ['target_id' => ['required', 'integer', 'exists:users,id'], 'action' => ['required', Rule::in(['like', 'pass', 'dislike', 'loveit'])]];
    }
}
