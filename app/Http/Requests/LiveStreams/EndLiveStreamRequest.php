<?php

namespace App\Http\Requests\LiveStreams;

use Illuminate\Foundation\Http\FormRequest;

class EndLiveStreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
