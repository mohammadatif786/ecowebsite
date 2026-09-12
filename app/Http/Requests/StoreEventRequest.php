<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organizer_id' => 'nullable',
            // Media (array of files)
            'mediaFiles' => 'nullable',

            // Event details
            'eventDetails'                  => ['required', 'array'],
            'eventDetails.name'             => ['required', 'string', 'max:255'],
            'eventDetails.description'      => ['required', 'string'],
            'eventDetails.category_id'      => ['required', 'integer', 'exists:event_categories,id'],
            'eventDetails.audiences'        => 'required',
            'eventDetails.audiences.*'      => ['string'],
            'eventDetails.attendees'        => ['required', 'in:show,hide'],
            'eventDetails.reviews'          => ['required', 'in:enable,disable'],
            'eventDetails.seating_plan'     => ['required', 'in:yes,no'],
            'eventDetails.venue'            => ['nullable', 'string', 'max:255'],

            // Date & time
            'dateTime'                      => ['nullable', 'array'],
            'dateTime.eventType'            => ['nullable', 'in:single,recurring'],
            'dateTime.singleDate'           => ['nullable', 'date'],
            'dateTime.singleStartTime'      => ['nullable', 'date_format:H:i'],
            'dateTime.singleEndTime'        => ['nullable', 'date_format:H:i'],
            'dateTime.recurrPattern'        => ['nullable', 'string'],
            'dateTime.recurrStartDate'      => ['nullable', 'date'],
            'dateTime.recurrEndDate'        => ['nullable', 'date'],

            // Additional options
            'additionalOptions'             => ['required', 'array'],
            'additionalOptions.artists'     => ['nullable'],
            'additionalOptions.artists.*'   => ['nullable'],
            'additionalOptions.artist_image'   => ['nullable'],
            'additionalOptions.type'        => ['required', 'in:public,private'],
            'additionalOptions.email'       => ['required', 'email'],
            'additionalOptions.phone'       => ['required', 'string'],
            'additionalOptions.website'     => ['nullable', 'string'],
            'additionalOptions.country'     => ['required', 'string'],
            'additionalOptions.state'       => ['required', 'string'],
            'additionalOptions.city'        => ['required', 'string'],
            'additionalOptions.zip'         => ['nullable', 'string'],
            'additionalOptions.tax_rate'    => ['nullable'],
            'additionalOptions.tax_included' => ['required', 'in:yes,no'],
            'additionalOptions.disclaimer'  => ['required', 'string'],
            'additionalOptions.gallery'   => ['nullable', 'array'],
            'additionalOptions.gallery.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        // Validate file type for uploaded files
                        $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                        $extension = strtolower($value->getClientOriginalExtension());
                        
                        if (!in_array($extension, $allowedMimes)) {
                            $fail("The {$attribute} must be an image (jpg, jpeg, png, gif, webp) or video (mp4, webm, ogg, avi, mov, wmv, flv, mkv) file.");
                        }
                        
                        // Check file size (max 50MB for videos, 5MB for images)
                        $maxSize = in_array($extension, ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv']) ? 50 * 1024 : 5 * 1024; // KB
                        if ($value->getSize() > $maxSize * 1024) { // Convert to bytes
                            $fail("The {$attribute} file size must not exceed " . ($maxSize / 1024) . "MB.");
                        }
                    } elseif (!is_string($value)) {
                        $fail("The {$attribute} must be a file or an existing path.");
                    }
                },
            ],



            // Location
            'location_type'                     => ['nullable', 'in:venue,online,tba'],
            'venue'                            => ['nullable', 'string', 'max:500', 'required_if:location_type,venue'],
            'latitude'                            => ['nullable', 'string'],
            'longtitude'                            => ['nullable', 'string'],


            // Scanners (array of objects)
            'scanners'              => ['nullable', 'array'],
            'scanners.*.id'         => ['nullable', 'integer'],
            'scanners.*.first_name' => ['nullable', 'string', 'max:100'],
            'scanners.*.last_name'  => ['nullable', 'string', 'max:100'],
            'scanners.*.email'      => ['nullable', 'email'],


            // Social media
            'socialMedia'                   => 'array',
            'socialMedia.twitter'           => 'nullable',
            'socialMedia.instagram'         => 'nullable',
            'socialMedia.facebook'          => 'nullable',
            'socialMedia.tiktok'            => 'nullable',
            'socialMedia.linkedin'          => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'eventDetails.name.required'        => 'Event name is required.',
            'eventDetails.category_id.required' => 'Category is required.',
            'dateTime.eventType.required'       => 'Event type is required.',
            'additionalOptions.email.required'  => 'Email is required in additional options.',
            'venue.required_if'                 => 'Venue location is required for an in-person event.',
        ];
    }

    public function attributes()
    {
        return [
            'eventDetails.description' => 'description',
            'eventDetails.audiences' => 'audiences',
            'eventDetails.venue' => 'venue',
            'eventDetails.event_name' => 'event name',
            'eventDetails.category' => 'category',
        ];
    }
}
