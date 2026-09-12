<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizerUserProfileRequest extends FormRequest
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

            'organizer_profile_type' => 'nullable',

            // Profile Detail
            'categories' => 'sometimes|nullable|array',
            'categories.*' => 'string|max:255',
            'organizer_name' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date',
            'place_of_birth' => 'sometimes|required|string|max:255',
            'nationality' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'telephone' => 'sometimes|required|string|max:255',
            'about_the_organizer' => 'sometimes|required|string',
            'ssn' => 'sometimes|required|string|max:255',

            // Additional Details
            'organizer_profile_type' => 'nullable',
            'logo' => 'nullable',
            'cover_photo' => 'nullable',
            'profile_photo' => 'nullable',

            // Profile Contacts
            'organizer_profile_type' => 'nullable',
            'facebook' => 'sometimes|required|string|max:255',
            'twitter' => 'sometimes|required|string|max:255',
            'instagram' => 'sometimes|required|string|max:255',
            'linkedin' => 'sometimes|required|string|max:255',
            'youtube' => 'sometimes|required|url|max:255',
            'country' => 'sometimes|required|string|max:255',
            'state' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'website' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|required|max:20',

            // Profile Settings
            'organizer_profile_type' => 'nullable',
            'show_venues_map' => 'sometimes|required|in:1,0',
            'show_followers' => 'sometimes|required|in:1,0',
            'show_reviews' => 'sometimes|required|in:1,0',

            // Bank Accounts
            'organizer_profile_type' => 'nullable',
            'banks' => 'sometimes|required|array|min:1',
            'banks.*.bank_name' => 'sometimes|required_with:banks|string|max:255',
            'banks.*.account_number' => 'sometimes|required_with:banks|string|max:255',
            'banks.*.routing_number' => 'sometimes|required_with:banks|string|max:255',
            'paypal_id' => 'nullable|string|max:255',

            // KYC
            'organizer_profile_type' => 'nullable',
            'passport_front' => 'nullable',
            'passport_back' => 'nullable',
            'proof_of_address' => 'nullable',
            'status' => 'nullable',

        ];
    }

    /**
     * Get the validation rules messages that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'organizer_name.required' => 'Organizer name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'place_of_birth.required' => 'Place of birth is required.',
            'nationality.required' => 'Nationality is required.',
            'address.required' => 'Address is required.',
            'telephone.required' => 'Telephone number is required.',
            'about_the_organizer.required' => 'Please provide details about the organizer.',
            'ssn.required' => 'Social Security Number / National ID is required.',

            // organizerMedia
            'facebook.url' => 'Facebook URL must be valid.',
            'twitter.url' => 'Twitter URL must be valid.',
            'instagram.url' => 'Instagram URL must be valid.',
            'linkedin.url' => 'LinkedIn URL must be valid.',
            'youtube.url' => 'YouTube URL must be valid.',
            'country.required' => 'Please select your country.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'Phone number is required.',

            // profileVisibility
            'show_venues_map.required' => 'Please select whether to show or hide the venues map.',
            'show_followers.required'  => 'Please select whether to show or hide followers.',
            'show_reviews.required'    => 'Please select whether to show or hide reviews.',

            // bankingInformation
            'banks.required' => 'Please add at least one bank account.',
            'banks.*.bank_name.required' => 'The bank name field is required.',
            'banks.*.account_number.required' => 'The account number field is required.',
            'banks.*.routing_number.required' => 'The routing number field is required.',
            'paypal_id.required' => 'Please provide a valid PayPal.',

            // KYC
            'passport_front.required'   => 'Passport front image is required.',
            'passport_back.required'    => 'Passport back image is required.',
            'proof_of_address.required' => 'Proof of address document is required.',
        ];
    }
}
