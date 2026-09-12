<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\PointOfSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class PointOfSaleController extends Controller
{
    /**
     * Display a listing of the points of sale.
     */
    public function index()
    {
        $pointsOfSale = PointOfSale::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pos) {
                return [
                    'id' => $pos->id,
                    'name' => $pos->name,
                    'username' => $pos->username,
                    'creationDate' => $pos->created_at->format('Y-m-d'),
                    'lastLogin' => $pos->last_login ? $pos->last_login->format('Y-m-d') : $pos->created_at->format('Y-m-d'),
                    'eventsCount' => 0,
                    'status' => $pos->status
                ];
            })->values()->all();

        return response()->json([
            'success' => true,
            'pointsOfSale' => $pointsOfSale
        ]);
    }

    /**
     * Store a newly created point of sale.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:points_of_sale,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pos = PointOfSale::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'status' => 'enabled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Point of Sale created successfully',
            'pointOfSale' => $pos
        ], 201);
    }

    /**
     * Update the specified point of sale.
     */
    public function update(Request $request, $id)
    {
        $pos = PointOfSale::where('user_id', Auth::id())->find($id);
        
        if (!$pos) {
            return response()->json([
                'success' => false,
                'message' => 'Point of Sale not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:points_of_sale,username,' . $id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pos->update([
            'name' => $request->name,
            'username' => $request->username,
        ]);

        if (!empty($request->password)) {
            $pos->update(['password' => Hash::make($request->password)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Point of Sale updated successfully',
            'pointOfSale' => $pos->fresh()
        ]);
    }

    /**
     * Toggle the status of the specified point of sale.
     */
    public function toggleStatus($id)
    {
        $pos = PointOfSale::where('user_id', Auth::id())->find($id);

        if (!$pos) {
            return response()->json([
                'success' => false,
                'message' => 'Point of Sale not found'
            ], 404);
        }

        $pos->update([
            'status' => $pos->status === 'enabled' ? 'disabled' : 'enabled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Point of Sale status updated successfully',
            'pointOfSale' => $pos->fresh()
        ]);
    }

    /**
     * Remove the specified point of sale.
     */
    public function destroy($id)
    {
        $pos = PointOfSale::where('user_id', Auth::id())->where('id', $id)->first();
        
        if (!$pos) {
            return response()->json([
                'success' => false,
                'message' => 'Point of Sale not found'
            ], 404);
        }

        $pos->delete();

        return response()->json([
            'success' => true,
            'message' => 'Point of Sale deleted successfully'
        ]);
    }
}
