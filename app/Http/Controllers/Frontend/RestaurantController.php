<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Frontend\ClubFete;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    public function index()
    {
        // $location = Helpers::getAuthUserLocation();
        $ip = request()->ip();

        $responseIP = file_get_contents("http://ip-api.com/json/{$ip}");
        $locationData = json_decode($responseIP, true);

        $location = [];
        $restaurants = [];

        if ($locationData && $locationData['status'] == 'success') {
            $location = [
                'latitude' => $locationData['lat'],
                'longitude' => $locationData['lon'],
            ];
        } else {
            $location = [
                'latitude' => 25.7617,
                'longitude' => -80.1918,
            ];
        }


        $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
            'location' => $location['latitude'] . ',' . $location['longitude'],
            'radius' => 20000,
            'type' => 'restaurant',
            'keyword' => 'restaurant',
            'key' => env('GOOGLE_API_KEY'),
        ]);

        if ($response->successful()) {
            $places = $response->json()['results'];
            foreach ($places as $place) {
                $restaurants[] = [
                    'place_id' => $place['place_id'],
                    'name' => $place['name'],
                    'address' => $place['vicinity'] ?? '',
                    'rating' => $place['rating'] ?? null,
                    'image' => 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=' . ($place['photos'][0]['photo_reference'] ?? '') . '&sensor=false&key=' . env('GOOGLE_API_KEY'),
                ];
            }
        }

        $swipeAds = \App\Models\Advertisement::where('status', 1)
            ->where('category', 'restaurant')
            ->get();

        return Inertia::render('User/Restaurants/Index', [
            'restaurants' => $restaurants,
            'swipeAds'    => $swipeAds,
        ]);
    }

    public function show($place_id)
    {
        $restaurant = null;

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $place_id,
            'key' => env('GOOGLE_API_KEY'),
        ]);

        if ($response->successful()) {
            $place = $response->json()['result'];

            $photos = [];
            foreach ($place['photos'] ?? [] as $photo) {
                $photos[] = 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=1280&photoreference=' . $photo['photo_reference'] . '&sensor=false&key=' . env('GOOGLE_API_KEY');
            }

            $restaurant = [
                'place_id' => $place['place_id'],
                'name' => $place['name'],
                'address' => $place['formatted_address'] ?? '',
                'rating' => $place['rating'] ?? null,
                'image' => isset($place['photos']) ? 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=' . $place['photos'][0]['photo_reference'] . '&sensor=false&key=' . env('GOOGLE_API_KEY') : null,
                'photos' => $photos,
                'phone' => $place['international_phone_number'] ?? null,
                'availablitiy' => $place['opening_hours']['open_now'] ?? false,
                'user_ratings_total' => $place['user_ratings_total'] ?? 0,
                'reviews' => $place['reviews'] ?? [],
            ];
        }

        if (! $restaurant) {
            abort(404);
        }

        return Inertia::render('User/Restaurants/Show', [
            'restaurant' => $restaurant,
        ]);
    }
}
