<?php

namespace App\Http\Requests\User\Signup;

use App\DTOs\User\Signup\WizardOptions;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The last wizard step: the photos. Every other answer is already saved on the server.
 */
class CompleteWizardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // behind `auth` and the email gate
    }

    public function rules(): array
    {
        return [
            'more_photos' => ['required', 'array', 'min:1', 'max:'.WizardOptions::MAX_PHOTOS],
            'more_photos.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'more_photos.required' => 'Add at least one photo to finish.',
            'more_photos.min' => 'Add at least one photo to finish.',
            'more_photos.max' => 'You can add up to '.WizardOptions::MAX_PHOTOS.' photos.',
            'more_photos.*.image' => 'Only photos can be added here.',
            'more_photos.*.mimes' => 'Use a JPG, PNG or WebP photo.',
            'more_photos.*.max' => 'Each photo must be 5 MB or smaller.',
        ];
    }
}
