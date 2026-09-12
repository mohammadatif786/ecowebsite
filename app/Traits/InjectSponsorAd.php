<?php

namespace App\Traits;

use App\Services\SponsorService;
use Illuminate\Support\Facades\App;

trait InjectSponsorAd
{
    /**
     * Get the sponsor ad for the current mailable.
     * 
     * @param string $categoryKey
     * @param string|null $countryCode
     * @return \App\Models\EmailSponsorAd|null
     */
    protected function getSponsorAd(string $categoryKey, ?string $countryCode = null)
    {
        $service = App::make(SponsorService::class);
        $ad = $service->getAdForEmail($categoryKey, $countryCode);

        if ($ad) {
            // Record impression
            $service->recordImpression($ad, null, $categoryKey, $countryCode);
        }

        return $ad;
    }
}
