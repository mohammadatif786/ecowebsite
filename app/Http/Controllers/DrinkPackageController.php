<?php

namespace App\Http\Controllers;

use App\Http\Requests\DrinkPackageFormRequest;
use App\Services\Organizer\DrinkPackageService;
use Illuminate\Support\Facades\Auth;

class DrinkPackageController extends Controller
{
    protected $drinkPackageService;

    public function __construct(DrinkPackageService $drinkPackageService)
    {
        $this->drinkPackageService = $drinkPackageService;
    }

    /**
     * Store a newly created drink package in storage.
     */
    public function store(DrinkPackageFormRequest $request)
    {
        $validated = $request->validated();

        $organizerId = Auth::user()->organizerProfile->id;

        $package = $this->drinkPackageService->store($validated, $organizerId);

        return response()->json([
            'message' => 'Drink Package created successfully',
            'package' => $package,
        ]);
    }

    /**
     * Display the specified drink package.
     */
    public function show($id)
    {
        $organizerId = Auth::user()->organizerProfile->id;
        
        $package = $this->drinkPackageService->find($id, $organizerId);

        return response()->json([
            'success' => true,
            'data' => $package,
        ]);
    }

    /**
     * Update the specified drink package in storage.
     */
    public function update(DrinkPackageFormRequest $request, $id)
    {
        $validated = $request->validated();

        $organizerId = Auth::user()->organizerProfile->id;
        
        $package = $this->drinkPackageService->update($id, $validated, $organizerId);

        return response()->json([
            'message' => 'Drink Package updated successfully',
            'package' => $package,
        ]);
    }

    /**
     * Remove the specified drink package from storage.
     */
    public function destroy($id)
    {
        $organizerId = Auth::user()->organizerProfile->id;
        
        $this->drinkPackageService->delete($id, $organizerId);

        return response()->json([
            'message' => 'Drink Package deleted successfully',
            'package' => $id,
        ]);

    }
}
