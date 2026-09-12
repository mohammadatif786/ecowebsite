<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => 'required|string',
            "summary" => 'required|string',
            "body" => 'required|string',
            "country" => 'required|string',
            "countryCode" => 'required|string',
            "region" => 'required|string',
            "category" => 'required|string',
            "sourceName" => 'nullable|string',
            "sourceType" => 'nullable|string',
            "isBreaking" => 'nullable|boolean',
            "breakingExpiresAt" => 'nullable',
            "trending" => 'nullable|integer',
            "news_media" => 'nullable|array',
            "media_info" => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            "title.required" => "Title is required",
            "summary.required" => "Summary is required",
            "body.required" => "Body is required",
            "country.required" => "Country is required",
            "countryCode.required" => "Country code is required",
            "region.required" => "Region is required",
            "category.required" => "Category is required",
            "sourceName.required" => "Source name is required",
            "sourceType.required" => "Source type is required",
            "isBreaking.required" => "Is breaking is required",
            "breakingExpiresAt.required" => "Breaking expires at is required",
            "trending.required" => "Trending is required",
            "news_media.required" => "Media is required",
            "media_info.required" => "Media info is required",
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mediaInfo = json_decode($this->media_info, true);
            if (!is_array($mediaInfo) || count($mediaInfo) === 0) {
                $validator->errors()->add('news_media', 'At least one media item (image, video, or audio) is required.');
            }
        });
    }
}
