<?php

namespace App\Services;

use App\Models\EmailSponsorAd;
use App\Models\SponsorImpression;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SponsorService
{
    /**
     * Get the best ad for a given email category and country.
     * 
     * @param string $categoryKey
     * @param string|null $countryCode
     * @return EmailSponsorAd|null
     */
    public function getAdForEmail(string $categoryKey, ?string $countryCode = null)
    {
        $now = Carbon::now();

        $query = EmailSponsorAd::where('status', 'active')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->whereHas('category', function ($q) use ($categoryKey) {
                $q->where('key', $categoryKey);
            });

        // Filter by country if provided
        if ($countryCode) {
            $normalizedCode = strtolower($countryCode);
            $query->where(function ($q) use ($normalizedCode) {
                // Ad serves specific country
                $q->whereHas('countries', function ($sq) use ($normalizedCode) {
                    $sq->where('code', $normalizedCode);
                })
                // OR Ad serves all countries (no countries assigned)
                ->orWhereDoesntHave('countries');
            });
        }

        return $query->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Record an impression for an ad.
     */
    public function recordImpression(EmailSponsorAd $ad, ?int $userId, string $categoryKey, ?string $countryCode)
    {
        try {
            SponsorImpression::create([
                'email_sponsor_ad_id' => $ad->id,
                'user_id' => $userId,
                'email_category' => $categoryKey,
                'country_code' => $countryCode,
                'sent_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to record sponsor impression: " . $e->getMessage());
        }
    }
}
