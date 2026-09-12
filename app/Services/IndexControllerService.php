<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\CaribbeanIsland;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IndexControllerService
{
    public function caribbeanIsland()
    {
        return CaribbeanIsland::all();
    }

    public function advertisementList()
    {
        return Advertisement::where('status', 1)->where('category', 'general')->get();
    }

    public function getCountryFlag(string $countryName): ?string
    {
        $cacheKey = 'country_flag_v5_' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $countryName));

        $flagUrl = Cache::get($cacheKey);

        if ($flagUrl !== null) {
            return $flagUrl === 'not_found' ? null : $flagUrl;
        }

        try {
            // Fetch the country data using the new API endpoint and Bearer token
            $response = Http::timeout(5)
                ->withToken('rc_live_1768ecef61b54435b5a07320b7219f9a')
                ->get('https://api.restcountries.com/countries/v5', [
                    'q' => strtolower($countryName)
                ]);

            if (!$response->successful()) {
                Cache::put($cacheKey, 'not_found', now()->addHours(1));
                return null;
            }

            $data = $response->json();

            // Extract the SVG flag URL based on the new JSON response structure
            $url = $data['data']['objects'][0]['flag']['url_svg'] ?? null;

            if (!$url) {
                Cache::put($cacheKey, 'not_found', now()->addHours(1));
                return null;
            }

            Cache::put($cacheKey, $url, now()->addDays(30));

            return $url;
        } catch (\Exception $e) {
            // On timeout or exception, do not cache so it can try again later
            return null;
        }
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2, $unit = 'K')
    {
        $theta = $lon1 - $lon2;
        $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2))
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist  = acos($dist);
        $dist  = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        if ($unit === "K") { // kilometers
            return round($miles * 1.609344, 0);
        } elseif ($unit === "N") { // nautical miles
            return round($miles * 0.8684, 0);
        } else { // miles
            return round($miles, 0);
        }
    }
}
