<?php

namespace App\Http\Requests\Vibes;

use App\Domain\Vibes\Enums\VibeMediaSource;
use App\Domain\Vibes\Enums\VibePublisherType;
use App\Domain\Vibes\Enums\VibeVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVibeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['product_ids', 'event_ids', 'media_sources'] as $field) {
            if (is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);
                $this->merge([$field => is_array($decoded) ? $decoded : []]);
            }
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $imageTypes = implode(',', config('vibes.image_mimetypes'));
        $videoTypes = implode(',', config('vibes.video_mimetypes'));

        return [
            'publisher_type' => ['required', Rule::enum(VibePublisherType::class)],
            'publisher_id' => ['required', 'integer', 'min:1'],
            'caption' => ['nullable', 'string', 'max:'.config('vibes.caption_max')],
            'location_name' => ['nullable', 'string', 'max:255'],
            'location_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'allow_coin_gifts' => ['sometimes', 'boolean'],
            'visibility' => ['sometimes', Rule::enum(VibeVisibility::class)],
            'media' => ['nullable', 'array', 'max:'.config('vibes.max_media')],
            'media.*' => ['file', 'mimetypes:'.$imageTypes.','.$videoTypes, function (string $attribute, mixed $file, \Closure $fail) {
                $limit = str_starts_with((string) $file->getMimeType(), 'image/')
                    ? config('vibes.max_image_kb')
                    : config('vibes.max_video_kb');
                if ($file->getSize() > $limit * 1024) {
                    $fail("The {$attribute} file is too large.");
                }
            }],
            'media_sources' => ['nullable', 'array'],
            'media_sources.*' => [Rule::enum(VibeMediaSource::class)],
            'product_ids' => ['nullable', 'array', 'max:'.config('vibes.max_attachments_per_type')],
            'product_ids.*' => ['integer', 'distinct', Rule::exists('products', 'id')->where('status', true)],
            'event_ids' => ['nullable', 'array', 'max:'.config('vibes.max_attachments_per_type')],
            'event_ids.*' => ['integer', 'distinct', Rule::exists('link_up_events', 'id')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (blank($this->input('caption')) && count($this->file('media', [])) === 0) {
                $validator->errors()->add('caption', 'A caption or at least one media file is required.');
            }

            $total = collect($this->file('media', []))->sum(fn ($file) => $file->getSize());
            if ($total > config('vibes.max_total_kb') * 1024) {
                $validator->errors()->add('media', 'The total media upload is too large.');
            }

            $sources = $this->input('media_sources', []);
            if ($sources !== [] && count($sources) !== count($this->file('media', []))) {
                $validator->errors()->add('media_sources', 'Each media file must have one source.');
            }
        }];
    }
}
