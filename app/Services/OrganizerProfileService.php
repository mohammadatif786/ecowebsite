<?php

namespace App\Services;

use App\Models\OrganizerBankAccount;
use App\Models\OrganizerContact;
use App\Models\OrganizerKyc;
use App\Models\OrganizerMedia;
use App\Models\OrganizerProfile;
use App\Models\OrganizerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class OrganizerProfileService
{
    protected $image_service;

    public function __construct(ImageService $image_service)
    {
        $this->image_service = $image_service;
    }

    public function profileDetails(array $data)
    {

        $profile = OrganizerProfile::updateOrCreate(
            ['user_id' => $data['user_id']],
            $data
        );

        // Update Contact Details (Location & Email) if provided
        if (isset($data['country']) || isset($data['state']) || isset($data['city']) || isset($data['email'])) {
            OrganizerContact::updateOrCreate(
                ['organizer_id' => $profile->id],
                Arr::only($data, ['country', 'state', 'city', 'email'])
            );
        }

        return $profile;
    }

    public function additionalDetails(array $data)
    {
        $organizer_id = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        $profile = OrganizerMedia::firstOrCreate(
            ['organizer_id' => $organizer_id->id]
        );

        if (isset($data['logo'])) {
            $profile->logo = $data['logo'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/logo', $data['logo'])
                : $data['logo'];
        }

        if (isset($data['cover_photo'])) {
            $profile->cover_photo = $data['cover_photo'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/cover', $data['cover_photo'])
                : $data['cover_photo'];
        }

        if (isset($data['profile_photo'])) {
            $profile->profile_photo = $data['profile_photo'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/profile', $data['profile_photo'])
                : $data['profile_photo'];
        }


        $profile->save();

        return $profile;
    }

    public function profileContacts(array $data)
    {
        $organizer_id = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        $profile = OrganizerContact::updateOrCreate(
            ['organizer_id' => $organizer_id->id],
            $data
        );

        return $profile;
    }

    public function profileSetting(array $data)
    {
        $organizer_id = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        $profile = OrganizerSetting::updateOrCreate(
            ['organizer_id' => $organizer_id->id],
            $data
        );

        return $profile;
    }

    public function profileBankAccounts(array $data)
    {

        $organizer = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );
        $accountIds = collect($data['banks'])->pluck('id')->filter()->toArray();

        OrganizerBankAccount::where('organizer_id', $organizer->id)
            ->whereNotIn('id', $accountIds)
            ->delete();

        foreach ($data['banks'] as $bank) {
            $profile = OrganizerBankAccount::updateOrCreate(
                [
                    'id'           => $bank['id'] ?? null,
                    'organizer_id' => $organizer->id,
                ],
                [
                    'bank_name'      => $bank['bank_name'],
                    'routing_number' => $bank['routing_number'],
                    'account_number' => $bank['account_number'],
                    'paypal_id'      => $data['paypal_id'] ?? null,
                ]
            );
        }

        return $profile;
    }

    public function profileKYC(array $data)
    {
        // Support both nested payload (organizerKYC.*) and flat keys
        $payload = $data['organizerKYC'] ?? $data;

        $organizer = OrganizerProfile::firstOrCreate(
            ['user_id' => Auth::id()]
        );

        // Fetch existing KYC so we don't overwrite other images
        $kyc = OrganizerKyc::firstOrCreate([
            'organizer_id' => $organizer->id,
        ]);

        $updates = [];

        if (array_key_exists('passport_front', $payload)) {
            $updates['passport_front'] = $payload['passport_front'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/kyc', $payload['passport_front'])
                : $payload['passport_front'];
        }

        if (array_key_exists('passport_back', $payload)) {
            $updates['passport_back'] = $payload['passport_back'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/kyc', $payload['passport_back'])
                : $payload['passport_back'];
        }

        if (array_key_exists('proof_of_address', $payload)) {
            $updates['proof_of_address'] = $payload['proof_of_address'] instanceof \Illuminate\Http\UploadedFile
                ? $this->image_service->single('organizer/kyc', $payload['proof_of_address'])
                : $payload['proof_of_address'];
        }

        if (!empty($updates)) {
            // When any doc is updated, reset overall status to pending for re-review
            $updates['status'] = 'pending';
            $kyc->fill($updates);
            $kyc->save();
        }

        return $kyc;
    }
}
