<?php

namespace App\Http\Requests\Linkup;

use Illuminate\Foundation\Http\FormRequest;

class AgoraTokenRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['recipient_id' => ['required', 'integer', 'exists:users,id']]; }
}
