<?php

namespace App\Http\Controllers\V1\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventTicketDrink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventTicketDrinkController extends Controller
{
    /**
     * Get all ticket drinks for the authenticated organizer
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $drinks = EventTicketDrink::with('createdBy')
            ->where('created_by', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'drinks' => $drinks,
        ]);
    }

    /**
     * Create a new ticket drink
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $validated['created_by'] = $user->id;

        $drink = EventTicketDrink::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket drink created successfully',
            'drink' => $drink,
        ], 201);
    }

    /**
     * Update a ticket drink
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $drink = EventTicketDrink::find($id);

        if (! $drink) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket drink not found',
            ], 404);
        }

        // Verify ownership
        if ($drink->created_by != $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $drink->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket drink updated successfully',
            'drink' => $drink,
        ]);
    }

    /**
     * Delete a ticket drink
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $drink = EventTicketDrink::find($id);

        if (! $drink) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket drink not found',
            ], 404);
        }

        // Verify ownership
        if ($drink->created_by != $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $drink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket drink deleted successfully',
        ]);
    }
}
