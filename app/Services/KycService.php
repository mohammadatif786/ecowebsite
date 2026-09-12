<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class KycService
{
    protected $uploader;

    public function __construct(FileUploadService $uploader)
    {
        $this->uploader = $uploader;
    }

    public function submitKyc($data, $files)
    {
        $user = User::find(Auth::id());

        $front = $this->uploader->uploadSingle($files['front_side'] ?? null, 'kyc/front');
        $back = $this->uploader->uploadSingle($files['back_side'] ?? null, 'kyc/back');
        $address = $this->uploader->uploadSingle($files['address_proof'] ?? null, 'kyc/address');
        $selfie = $this->uploader->uploadSingle($files['selfie'] ?? null, 'kyc/selfie');

        $user->update([
            'front_side'     => $front,
            'back_side'      => $back,
            'address_proof'  => $address,
            'selfie'         => $selfie,
            'kyc_submitted'  => 1,
        ]);

        return $user;
    }
}
