<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxApiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TaxApiController extends Controller
{
    public function index()
    {
        $settings = TaxApiSetting::all()->map(function ($setting) {
            return [
                'id' => $setting->id,
                'provider' => $setting->provider,
                'end_point_url' => $setting->end_point_url,
                'is_active' => $setting->is_active,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
                // Don't include api_key in the response for security
            ];
        });
        
        return $settings;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|unique:tax_api_settings,provider,' . $request->id,
            'end_point_url' => 'nullable|string',
            'api_key' => 'required|string',
        ]);

        $setting = TaxApiSetting::updateOrCreate(
            ['provider' => $validated['provider']],
            [
                'end_point_url' => $validated['end_point_url'],
                'api_key' => $validated['api_key']
            ]
        );

        return response()->json($setting, 201);
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'api_url' => 'required|string',
            'api_key' => 'required|string',
        ]);

        try {
            $headers = [
                'Authorization' => 'Bearer ' . $validated['api_key'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            // Test with a simple tax rate request for a known ZIP
            $response = Http::withHeaders($headers)->get($validated['api_url'] . '/rates/90210');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'API connection successful',
                    'status' => $response->status(),
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API connection failed',
                    'status' => $response->status(),
                    'error' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|exists:tax_api_settings,provider',
        ]);

        DB::transaction(function () use ($validated) {
            TaxApiSetting::query()->update(['is_active' => false]);
            TaxApiSetting::where('provider', $validated['provider'])->update(['is_active' => true]);
        });

        return response()->json(['message' => 'Active provider updated successfully.']);
    }
}