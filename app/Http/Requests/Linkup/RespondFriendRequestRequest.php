<?php

namespace App\Http\Requests\Linkup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondFriendRequestRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['sender_id' => ['required','integer','exists:users,id'], 'action' => ['required', Rule::in(['1','0'])]]; }
}
