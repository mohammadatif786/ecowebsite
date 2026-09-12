<?php

namespace App\Http\Requests\LiveStreams;

use App\Models\LiveStreamGumlet;
use Illuminate\Foundation\Http\FormRequest;

class TransferLiveEarningsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('transferEarnings', LiveStreamGumlet::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
        ];
    }
}
