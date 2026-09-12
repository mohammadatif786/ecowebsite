<?php

namespace App\Http\Requests\LiveStreams;

use App\Models\LiveStreamGumlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LiveAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAnalytics', LiveStreamGumlet::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'range' => ['sometimes', Rule::in(['today', '7d', '30d', '90d'])],
        ];
    }
}
