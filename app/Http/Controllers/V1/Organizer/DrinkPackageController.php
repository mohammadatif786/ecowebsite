<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\DrinkPackage;
use App\Models\OrganizerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DrinkPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $packages = DrinkPackage::where('organizer_id', $organizerProfile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * Store a newly created drink package in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'bottles' => 'nullable|array',
            'bottles.*.id' => 'nullable|string',
            'bottles.*.name' => 'nullable|string',
            'bottles.*.qty' => 'nullable|integer|min:1',
            'chasers' => 'nullable|array',
            'chasers.*.id' => 'nullable|string',
            'chasers.*.name' => 'nullable|string',
            'chasers.*.qty' => 'nullable|integer|min:1',
            'waters' => 'nullable|array',
            'waters.*.id' => 'nullable|string',
            'waters.*.name' => 'nullable|string',
            'waters.*.qty' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $validated = $validator->validated();

        $drinkPackage = DrinkPackage::create([
            'organizer_id' => $organizerProfile->id,
            'name' => $validated['name'],
            'bottles' => $validated['bottles'] ?? null,
            'chasers' => $validated['chasers'] ?? null,
            'waters' => $validated['waters'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Drink Package created successfully',
            'data' => $drinkPackage,
        ], 201);
    }

    /**
     * Display the specified drink package.
     */
    public function show($id)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $package = DrinkPackage::where('organizer_id', $organizerProfile->id)
            ->find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'error' => 'Drink Package not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $package,
        ]);
    }

    /**
     * Update the specified drink package in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bottles' => 'nullable|array',
            'bottles.*.id' => 'nullable|string',
            'bottles.*.name' => 'nullable|string',
            'bottles.*.qty' => 'nullable|integer|min:1',
            'chasers' => 'nullable|array',
            'chasers.*.id' => 'nullable|string',
            'chasers.*.name' => 'nullable|string',
            'chasers.*.qty' => 'nullable|integer|min:1',
            'waters' => 'nullable|array',
            'waters.*.id' => 'nullable|string',
            'waters.*.name' => 'nullable|string',
            'waters.*.qty' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $package = DrinkPackage::where('organizer_id', $organizerProfile->id)
            ->find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'error' => 'Drink Package not found'
            ], 404);
        }

        $validated = $validator->validated();

        $package->update([
            'name' => $validated['name'],
            'bottles' => $validated['bottles'] ?? null,
            'chasers' => $validated['chasers'] ?? null,
            'waters' => $validated['waters'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Drink Package updated successfully',
            'data' => $package,
        ]);
    }

    /**
     * Remove the specified drink package from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            return response()->json([
                'success' => false,
                'error' => 'Organizer profile not found'
            ], 404);
        }

        $package = DrinkPackage::where('organizer_id', $organizerProfile->id)
            ->find($id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'error' => 'Drink Package not found'
            ], 404);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Drink Package deleted successfully'
        ]);
    }
}
