<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\EventTicketDrink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventTicketDrinkController extends Controller
{
    public function createUpdate(Request $request)
    {
        $userId = Auth::id();
        $drinksData = $request->all();

        $incomingIds = [];
        foreach ($drinksData as $type => $drinks) {
            foreach ($drinks as $drink) {
                $incomingIds[] = $drink['id'] ?? null;
            }
        }

        EventTicketDrink::where('created_by', $userId)
            ->whereNotIn('id', array_filter($incomingIds))
            ->delete();

        foreach ($drinksData as $type => $drinks) {
            foreach ($drinks as $drink) {
                EventTicketDrink::updateOrCreate(
                    ['id' => $drink['id'] ?? null],
                    [
                        'type' => $type,
                        'name' => $drink['name'] ?? 'Unnamed Drink',
                        'amount' => $drink['amount'] ?? 0,
                        'created_by' => $userId,
                    ]
                );
            }
        }

        $fresh = EventTicketDrink::where('created_by', $userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'type', 'name', 'amount']);

        $grouped = [];
        foreach ($fresh as $d) {
            $t = $d->type;
            if (!isset($grouped[$t])) {
                $grouped[$t] = [];
            }
            $grouped[$t][] = [
                'id' => $d->id,
                'name' => $d->name,
                'amount' => $d->amount,
                'type' => $t,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Drinks saved successfully!',
            'drinks' => $grouped,
        ]);
    }
}
