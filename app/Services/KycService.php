<?php

namespace App\Services;

use App\Domain\Kyc\KycDocumentStore;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

        // Identity documents go to the private "kyc" disk, never the public one.
        $store = app(KycDocumentStore::class);
        $folder = 'user/' . $user->id;
        $put = fn ($file) => $file instanceof UploadedFile ? $store->store($file, $folder) : null;

        $front = $put($files['front_side'] ?? null);
        $back = $put($files['back_side'] ?? null);
        $address = $put($files['address_proof'] ?? null);
        $selfie = $put($files['selfie'] ?? null);

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
