<?php

namespace App\Actions;

use DateTime;
use App\Helpers\Helpers;
use App\Services\StoreOptimizedImage;
use App\Services\UniversityService;
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

        // Not a users column — the country of the university picked next to its name.
        $universityCountry = $validated['university_country'] ?? null;
        unset($validated['university_country']);
        $previousUniversity = $user->university;

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

        $this->syncUniversity($user, $validated['university'] ?? null, $previousUniversity, $universityCountry);

        return $user;
    }

    /**
     * "What university did you attend?" becomes the member's main campus in U Vibe (a school
     * nobody has listed yet is added to the campus list with its country). Runs for the website
     * and the mobile app wizard alike. Skipped when the school is unchanged and already listed,
     * so re-saving a profile never undoes a main university chosen in My universities.
     */
    protected function syncUniversity($user, ?string $name, ?string $previous, ?string $country): void
    {
        if (! filled($name)) {
            return;
        }

        try {
            $universities = app(UniversityService::class);

            if ($universities->sameName($name, $previous) && $universities->hasAny($user)) {
                return;
            }

            $universities->syncFromProfile($user, $name, $country);
        } catch (\Throwable $e) {
            report($e); // the campus list must never block saving a profile
        }
    }


}
