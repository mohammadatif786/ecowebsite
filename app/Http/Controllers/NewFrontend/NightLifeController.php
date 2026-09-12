<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\ClubFete;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class NightLifeController extends Controller
{
    public function index(Request $request)
    {
        $location = $this->resolveLocation($request);

        $data = [
            'clubs' => $this->venuesFor('club', $location),
            'restaurants' => $this->venuesFor('restaurant', $location),
            'swipeAds' => $this->nightLifeAds(),
            'location' => $location,
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return Inertia::render('new_front/night_life/Index', $data);
    }

    public function detail(Request $request, string $kind, string $placeId): JsonResponse
    {
        abort_unless(in_array($kind, ['club', 'restaurant'], true), 404);

        $venue = $request->query('source') === 'local'
            ? $this->localVenueDetail($kind, $placeId)
            : $this->googlePlaceDetail($placeId);

        abort_if(! $venue, 404);

        return response()->json($venue);
    }

    protected function venuesFor(string $kind, array $location): array
    {
        $googleVenues = $this->nearbyGooglePlaces($kind, $location);
        $localVenues = $this->localVenues($kind);

        return collect($googleVenues)
            ->merge($localVenues)
            ->take(24)
            ->values()
            ->all();
    }

    protected function nearbyGooglePlaces(string $kind, array $location): array
    {
        $apiKey = (string) config('services.google.maps_key', env('GOOGLE_API_KEY'));

        if ($apiKey === '') {
            return [];
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'location' => $location['latitude'] . ',' . $location['longitude'],
                'radius' => 20000,
                'type' => $kind === 'club' ? 'night_club' : 'restaurant',
                'keyword' => $kind === 'club' ? 'club' : 'restaurant',
                'key' => $apiKey,
            ]);
        } catch (Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('results', []))
            ->map(fn (array $place): array => [
                'place_id' => $place['place_id'] ?? null,
                'source' => 'google',
                'name' => $place['name'] ?? 'Unknown venue',
                'address' => $place['vicinity'] ?? '',
                'rating' => $place['rating'] ?? null,
                'image' => $this->googlePhotoUrl($place['photos'][0]['photo_reference'] ?? null, 700),
            ])
            ->filter(fn (array $place): bool => filled($place['place_id']))
            ->values()
            ->all();
    }

    protected function googlePlaceDetail(string $placeId): ?array
    {
        $apiKey = (string) config('services.google.maps_key', env('GOOGLE_API_KEY'));

        if ($apiKey === '') {
            return null;
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'place_id,name,formatted_address,rating,photos,international_phone_number,opening_hours,user_ratings_total,reviews,website,url',
                'key' => $apiKey,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful() || ! $response->json('result')) {
            return null;
        }

        $place = $response->json('result');
        $photos = collect($place['photos'] ?? [])
            ->map(fn (array $photo): ?string => $this->googlePhotoUrl($photo['photo_reference'] ?? null, 1280))
            ->filter()
            ->values()
            ->all();

        return [
            'place_id' => $place['place_id'] ?? $placeId,
            'source' => 'google',
            'name' => $place['name'] ?? 'Unknown venue',
            'address' => $place['formatted_address'] ?? '',
            'rating' => $place['rating'] ?? null,
            'image' => $photos[0] ?? null,
            'photos' => $photos,
            'phone' => $place['international_phone_number'] ?? null,
            'availability' => $place['opening_hours']['open_now'] ?? null,
            'user_ratings_total' => $place['user_ratings_total'] ?? 0,
            'reviews' => $place['reviews'] ?? [],
            'website' => $place['website'] ?? null,
            'maps_url' => $place['url'] ?? null,
        ];
    }

    protected function localVenues(string $kind): array
    {
        $model = $kind === 'club' ? ClubFete::query() : Restaurant::query();

        return $model
            ->where('status', 1)
            ->orderByDesc('is_paid')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(fn ($venue): array => $this->formatLocalVenue($venue, $kind))
            ->all();
    }

    protected function localVenueDetail(string $kind, string $placeId): ?array
    {
        $id = (int) str_replace('local-' . $kind . '-', '', $placeId);
        $venue = $kind === 'club' ? ClubFete::find($id) : Restaurant::find($id);

        if (! $venue) {
            return null;
        }

        return array_merge($this->formatLocalVenue($venue, $kind), [
            'photos' => array_values(array_filter([
                $this->mediaUrl($venue->image_object ?? null),
            ])),
            'phone' => $venue->phone ?? null,
            'availability' => (bool) ($venue->status ?? false),
            'user_ratings_total' => 0,
            'reviews' => [],
            'website' => $venue->www ?? null,
            'maps_url' => null,
        ]);
    }

    protected function formatLocalVenue($venue, string $kind): array
    {
        return [
            'place_id' => 'local-' . $kind . '-' . $venue->id,
            'source' => 'local',
            'name' => $venue->name ?? ucfirst($kind),
            'address' => $venue->location ?? collect([$venue->city ?? null, $venue->state ?? null, $venue->country ?? null])->filter()->implode(', '),
            'rating' => null,
            'image' => $this->mediaUrl($venue->image_object ?? null),
        ];
    }

    protected function nightLifeAds(): array
    {
        return Advertisement::query()
            ->where('status', 1)
            ->whereIn('category', ['club', 'restaurant', 'nightlife'])
            ->latest()
            ->limit(12)
            ->get()
            ->values()
            ->all();
    }

    protected function resolveLocation(Request $request): array
    {
        $user = $request->user();
        $hasBrowserCoordinates = $request->has(['lat', 'lng']);
        $latitude = $hasBrowserCoordinates ? $request->float('lat') : (float) ($user->latitude ?? 0);
        $longitude = $hasBrowserCoordinates ? $request->float('lng') : (float) ($user->longitude ?? 0);

        if ($hasBrowserCoordinates || ($latitude && $longitude)) {
            $profileLocation = [
                'city' => $user->new_city ?? $user->city ?? 'near you',
                'country' => $user->new_country ?? $user->country ?? '',
            ];
            $placeLocation = $hasBrowserCoordinates
                ? $this->reverseGeocode($latitude, $longitude)
                : [];

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'city' => $placeLocation['city'] ?? $profileLocation['city'],
                'country' => $placeLocation['country'] ?? $profileLocation['country'],
            ];
        }

        try {
            $location = Http::timeout(4)->get('http://ip-api.com/json/' . $request->ip())->json();

            if (($location['status'] ?? null) === 'success') {
                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lon'],
                    'city' => $location['city'] ?? ($user->city ?? 'near you'),
                    'country' => $location['country'] ?? ($user->country ?? ''),
                ];
            }
        } catch (Throwable) {
            //
        }

        return [
            'latitude' => 25.7617,
            'longitude' => -80.1918,
            'city' => $user->city ?? $user->new_city ?? 'Miami',
            'country' => $user->country ?? $user->new_country ?? 'United States',
        ];
    }

    protected function reverseGeocode(float $latitude, float $longitude): array
    {
        $apiKey = (string) config('services.google.maps_key', env('GOOGLE_API_KEY'));

        if ($apiKey === '') {
            return [];
        }

        try {
            $result = Http::timeout(4)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => $latitude . ',' . $longitude,
                'key' => $apiKey,
            ])->json('results.0');
        } catch (Throwable) {
            return [];
        }

        if (! is_array($result)) {
            return [];
        }

        $components = collect($result['address_components'] ?? []);
        $component = fn (string $type): ?string => $components
            ->first(fn (array $item): bool => in_array($type, $item['types'] ?? [], true))['long_name'] ?? null;

        return array_filter([
            'city' => $component('locality') ?? $component('administrative_area_level_2'),
            'country' => $component('country'),
        ]);
    }

    protected function googlePhotoUrl(?string $reference, int $width): ?string
    {
        $apiKey = (string) config('services.google.maps_key', env('GOOGLE_API_KEY'));

        if (! $reference || $apiKey === '') {
            return null;
        }

        return 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=' . $width
            . '&photoreference=' . $reference
            . '&sensor=false&key=' . $apiKey;
    }

    protected function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}
