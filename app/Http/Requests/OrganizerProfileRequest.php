<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizerProfileRequest extends FormRequest
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
        $isCreate = $this->routeIs('admin.organizer.profile.store');

        return [

            // Account Credentials (used only when creating a brand new organizer login)
            'accountPassword' => $isCreate ? 'required|string|min:6' : 'nullable|string|min:6',

            'profileDetail.organizer_profile_type' => 'nullable',

            // Profile Detail
            'profileDetail.categories' => 'sometimes|nullable|array',
            'profileDetail.categories.*' => 'string|max:255',
            'profileDetail.organizer_name' => 'sometimes|required|string|max:255',
            'profileDetail.date_of_birth' => 'sometimes|required|date',
            'profileDetail.place_of_birth' => 'sometimes|required|string|max:255',
            'profileDetail.nationality' => 'sometimes|required|string|max:255',
            'profileDetail.address' => 'sometimes|required|string|max:255',
            'profileDetail.telephone' => 'sometimes|required|string|max:255',
            'profileDetail.about_the_organizer' => 'sometimes|required|string',
            'profileDetail.ssn' => 'sometimes|required|string|max:255',

            // Additional Details
            'additionalDetails.organizer_profile_type' => 'nullable',
            'additionalDetails.logo' => 'nullable|',
            'additionalDetails.cover_photo' => 'nullable',
            'additionalDetails.profile_photo' => 'nullable',

            // Profile Contacts
            'organizerMedia.organizer_profile_type' => 'nullable',
            'organizerMedia.facebook' => 'nullable|url|max:255',
            'organizerMedia.twitter' => 'nullable|url|max:255',
            'organizerMedia.instagram' => 'nullable|url|max:255',
            'organizerMedia.linkedin' => 'nullable|url|max:255',
            'organizerMedia.youtube' => 'nullable|url|max:255',
            'organizerMedia.country' => 'sometimes|required|string|max:255',
            'organizerMedia.state' => 'sometimes|required|string|max:255',
            'organizerMedia.city' => 'sometimes|required|string|max:255',
            'organizerMedia.website' => 'sometimes|required|url|max:255',
            'organizerMedia.email' => 'sometimes|required|email|max:255',
            'organizerMedia.phone' => 'sometimes|required|max:20',

            // Profile Settings
            'profileVisibility.organizer_profile_type' => 'nullable',
            'profileVisibility.show_venues_map' => 'sometimes|required|in:1,0',
            'profileVisibility.show_followers' => 'sometimes|required|in:1,0',
            'profileVisibility.show_reviews' => 'sometimes|required|in:1,0',

            // Bank Accounts
            'bankingInformation.organizer_profile_type' => 'nullable',
            'bankingInformation.banks' => 'nullable|array',
            'bankingInformation.banks.*.bank_name' => 'nullable|string|max:255',
            'bankingInformation.banks.*.account_number' => 'nullable|string|max:255',
            'bankingInformation.banks.*.routing_number' => 'nullable|string|max:255',
            'bankingInformation.paypal_id' => 'nullable|string|max:255',

            // KYC
            'organizerKYC.organizer_profile_type' => 'nullable',
            'organizerKYC.passport_front' => 'nullable',
            'organizerKYC.passport_back' => 'nullable',
            'organizerKYC.proof_of_address' => 'nullable',

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
            'accountPassword.required' => 'Please set a login password for the new organizer.',
            'accountPassword.min' => 'Password must be at least 6 characters.',

            'profileDetail.organizer_name.required' => 'Organizer name is required.',
            'profileDetail.date_of_birth.required' => 'Date of birth is required.',
            'profileDetail.place_of_birth.required' => 'Place of birth is required.',
            'profileDetail.nationality.required' => 'Nationality is required.',
            'profileDetail.address.required' => 'Address is required.',
            'profileDetail.telephone.required' => 'Telephone number is required.',
            'profileDetail.about_the_organizer.required' => 'Please provide details about the organizer.',
            'profileDetail.ssn.required' => 'Social Security Number / National ID is required.',

            // organizerMedia
            'organizerMedia.facebook.url' => 'Facebook URL must be valid.',
            'organizerMedia.twitter.url' => 'Twitter URL must be valid.',
            'organizerMedia.instagram.url' => 'Instagram URL must be valid.',
            'organizerMedia.linkedin.url' => 'LinkedIn URL must be valid.',
            'organizerMedia.youtube.url' => 'YouTube URL must be valid.',
            'organizerMedia.country.required' => 'Please select your country.',
            'organizerMedia.email.required' => 'Email is required.',
            'organizerMedia.email.email' => 'Please provide a valid email address.',
            'organizerMedia.phone.required' => 'Phone number is required.',

            // profileVisibility
            'profileVisibility.show_venues_map.required' => 'Please select whether to show or hide the venues map.',
            'profileVisibility.show_followers.required'  => 'Please select whether to show or hide followers.',
            'profileVisibility.show_reviews.required'    => 'Please select whether to show or hide reviews.',

            // bankingInformation
            'bankingInformation.banks.required' => 'Please add at least one bank account.',
            'bankingInformation.banks.*.bank_name.required' => 'Bank name is required.',
            'bankingInformation.banks.*.account_number.required' => 'Account number is required.',
            'bankingInformation.banks.*.routing_number.required' => 'Routing number is required.',
            'bankingInformation.paypal_id.required' => 'Please provide a valid PayPal.',

            // KYC
            'organizerKYC.passport_front.required'   => 'Passport front image is required.',
            'organizerKYC.passport_back.required'    => 'Passport back image is required.',
            'organizerKYC.proof_of_address.required' => 'Proof of address document is required.',
        ];
    }
}
