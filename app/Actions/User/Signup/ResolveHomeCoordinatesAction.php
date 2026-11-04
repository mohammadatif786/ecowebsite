<?php

namespace App\Actions\User\Signup;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Map position of the member's home city, used for distance matching. Looked up with the same
 * free geocoder the dashboard weather card already uses (Open-Meteo). Returns null when the
 * lookup fails; a profile save never fails because of it.
 */
class ResolveHomeCoordinatesAction
{
    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function execute(?string $city, ?string $countryCode): ?array
    {
        $city = trim((string) $city);
        if ($city === '') {
            return null;
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        $key = 'signup-geocode:'.md5(mb_strtolower($city).'|'.$countryCode);

        return Cache::remember($key, now()->addWeek(), function () use ($city, $countryCode) {
            try {
                $query = ['name' => $city, 'count' => 1, 'language' => 'en'];
                if (preg_match('/^[A-Z]{2}$/', $countryCode)) {
                    $query['countryCode'] = $countryCode;
                }

                $response = Http::timeout(5)->connectTimeout(3)
                    ->get('https://geocoding-api.open-meteo.com/v1/search', $query);

                $hit = $response->successful() ? $response->json('results.0') : null;

                return $hit ? ['latitude' => (float) $hit['latitude'], 'longitude' => (float) $hit['longitude']] : null;
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        });
    }
}
