<?php

namespace App\Actions;

use DateTime;
use App\Helpers\Helpers;
use App\Services\StoreOptimizedImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class WizardUpdateAction
{
    protected $storeOptimized;

    public function __construct(StoreOptimizedImage $storeOptimized)
    {
        $this->storeOptimized = $storeOptimized;
    }

    public function update(array $validated, object $request)
    {
        $latitude = Helpers::getAuthUserLocation()['latitude'];
        $longitude = Helpers::getAuthUserLocation()['longitude'];

        $user = Auth::user();

        logger('Validated form data:', $validated);

        $validated['is_wizard_completed'] = 1;

        if ($request->hasFile('more_photos')) {
            $first = $request->file('more_photos')[0] ?? null;
            if ($first instanceof UploadedFile) {
                $validated['avatar'] = $this->storeOptimized->storeOptimized($first, 'profiles', 600, 82);
            }
        }

        // calculating age
        $birthday = new DateTime($request->birthday);
        $today = new DateTime();
        $validated['age'] = $today->diff($birthday)->y;

        if ($request->hasFile('more_photos')) {
            $morePhotos = [];
            foreach ($request->file('more_photos') as $photo) {
                if ($photo instanceof UploadedFile && $photo->isValid()) {
                    $morePhotos[] = $this->storeOptimized->storeOptimized($photo, 'more_photos', 1080, 82);
                }
            }
            $validated['more_photos'] = $morePhotos;
        }

        $validated['latitude'] = $latitude;
        $validated['longitude'] = $longitude;

        $user->update($validated);

        return $user;
    }


}
