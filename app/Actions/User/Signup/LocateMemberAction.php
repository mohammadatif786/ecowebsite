<?php

namespace App\Actions\User\Signup;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * "Use my current location" on the home base step: turns the browser's position into a country,
 * region and city. Uses OpenStreetMap's Nominatim reverse geocoder (free, one request per
 * sign-up, results cached). Countries name their places differently (the Bahamas returns the island
 * as "city", Pakistan a district and a town), so every place name and the official region codes are
 * returned and the page matches them against its own lists. Returns null when nothing usable comes back.
 */
class LocateMemberAction
{
    /** Address parts that can name a region or a city, from the finest to the broadest. */
    private const PLACE_KEYS = [
        'city', 'town', 'village', 'municipality', 'suburb', 'city_district', 'subdistrict', 'hamlet',
        'island', 'county', 'state_district', 'region', 'province', 'historical_division', 'state',
    ];

    /**
     * @return array{country_code: string, country: string, state: ?string, city: ?string, region_codes: list<string>, names: list<string>}|null
     */
    public function execute(float $latitude, float $longitude): ?array
    {
        // ~1 km grid, so neighbours share the cache and the exact position is never sent twice.
        $lat = round($latitude, 2);
        $lng = round($longitude, 2);

        return Cache::remember("signup-locate:v2:{$lat}:{$lng}", now()->addDay(), function () use ($lat, $lng) {
            try {
                $response = Http::timeout(6)->connectTimeout(3)
                    ->withHeaders(['User-Agent' => 'LinkUp/1.0 (+'.config('app.url').')'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'jsonv2',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 18,
                        'addressdetails' => 1,
                        'accept-language' => 'en',
                    ]);

                $address = $response->successful() ? (array) $response->json('address', []) : [];
                $code = strtoupper((string) ($address['country_code'] ?? ''));

                if (! preg_match('/^[A-Z]{2}$/', $code)) {
                    return null;
                }

                // Official region codes, e.g. "ISO3166-2-lvl8": "BS-NP" -> "NP" (the Bahamas list uses these).
                $regionCodes = [];
                foreach ($address as $key => $value) {
                    if (str_starts_with((string) $key, 'ISO3166-2') && is_string($value) && str_contains($value, '-')) {
                        $regionCodes[] = substr($value, strpos($value, '-') + 1);
                    }
                }

                $names = [];
                foreach (self::PLACE_KEYS as $key) {
                    if (! empty($address[$key]) && is_string($address[$key]) && ! in_array($address[$key], $names, true)) {
                        $names[] = $address[$key];
                    }
                }

                return [
                    'country_code' => $code,
                    'country' => (string) ($address['country'] ?? $code),
                    'state' => $address['state'] ?? $address['region'] ?? $address['province'] ?? $address['island'] ?? null,
                    'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['municipality'] ?? null,
                    'region_codes' => array_values(array_unique($regionCodes)),
                    'names' => $names,
                ];
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        });
    }
}
