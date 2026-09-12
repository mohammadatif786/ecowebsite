<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\LinkUpEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketAddonController extends Controller
{
    /**
     * Get addons for a specific event from tickets' drink_addons
     */
    public function getEventAddons(Request $request, $eventId): JsonResponse
    {
        $tickets = Ticket::where('event_id', $eventId)
            ->whereNotNull('drink_addons')
            ->get();

        $allAddons = [];

        foreach ($tickets as $ticket) {
            if (is_array($ticket->drink_addons)) {
                foreach ($ticket->drink_addons as $addon) {
                    // Create a unique key for each addon
                    $addonKey = $addon['name'] ?? $addon['id'] ?? uniqid();

                    if (!isset($allAddons[$addonKey])) {
                        $allAddons[$addonKey] = [
                            'id' => $addon['id'] ?? $addonKey,
                            'name' => $addon['name'] ?? 'Unknown Addon',
                            'description' => $addon['description'] ?? '',
                            'price' => floatval($addon['price'] ?? 0),
                            'available_quantity' => intval($addon['quantity_available'] ?? 999),
                            'is_required' => boolval($addon['is_required'] ?? false),
                            'max_quantity_per_ticket' => intval($addon['max_quantity_per_ticket'] ?? 1),
                            'ticket_id' => $ticket->id,
                            'ticket_name' => $ticket->name,
                        ];
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'addons' => array_values($allAddons)
        ]);
    }

    /**
     * Get addons for a specific ticket
     */
    public function getTicketAddons(Request $request, $ticketId): JsonResponse
    {
        $ticket = Ticket::findOrFail($ticketId);

        if (!$ticket->drink_addons || !is_array($ticket->drink_addons)) {
            return response()->json([
                'success' => true,
                'addons' => []
            ]);
        }

        $addons = array_map(function ($addon) use ($ticket) {
            return [
                'id' => $addon['id'] ?? uniqid(),
                'name' => $addon['name'] ?? 'Unknown Addon',
                'description' => $addon['description'] ?? '',
                'price' => floatval($addon['price'] ?? 0),
                'available_quantity' => intval($addon['quantity_available'] ?? 999),
                'is_required' => boolval($addon['is_required'] ?? false),
                'max_quantity_per_ticket' => intval($addon['max_quantity_per_ticket'] ?? 1),
                'ticket_id' => $ticket->id,
                'ticket_name' => $ticket->name,
            ];
        }, $ticket->drink_addons);

        return response()->json([
            'success' => true,
            'addons' => $addons
        ]);
    }

    /**
     * Calculate addon pricing for cart
     */
    public function calculateAddonPricing(Request $request): JsonResponse
    {
        $request->validate([
            'addons' => 'required|array',
            'addons.*.addon_id' => 'required|string',
            'addons.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAddonPrice = 0;
        $addonDetails = [];

        foreach ($request->addons as $addonData) {
            // Find the addon in tickets' drink_addons
            $addon = $this->findAddonInTickets($addonData['addon_id']);

            if (!$addon) {
                return response()->json([
                    'success' => false,
                    'message' => "Addon not found"
                ], 400);
            }

            if ($addonData['quantity'] > $addon['max_quantity_per_ticket']) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum {$addon['max_quantity_per_ticket']} of '{$addon['name']}' allowed per ticket"
                ], 400);
            }

            if ($addonData['quantity'] > $addon['available_quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$addon['available_quantity']} of '{$addon['name']}' available"
                ], 400);
            }

            $addonTotal = $addon['price'] * $addonData['quantity'];
            $totalAddonPrice += $addonTotal;

            $addonDetails[] = [
                'addon_id' => $addon['id'],
                'name' => $addon['name'],
                'unit_price' => $addon['price'],
                'quantity' => $addonData['quantity'],
                'total_price' => $addonTotal,
            ];
        }

        return response()->json([
            'success' => true,
            'total_addon_price' => $totalAddonPrice,
            'addon_details' => $addonDetails
        ]);
    }

    /**
     * Find addon in tickets' drink_addons by ID
     */
    private function findAddonInTickets($addonId)
    {
        $tickets = Ticket::whereNotNull('drink_addons')->get();

        foreach ($tickets as $ticket) {
            if (is_array($ticket->drink_addons)) {
                foreach ($ticket->drink_addons as $addon) {
                    if (($addon['id'] ?? '') == $addonId) {
                        return [
                            'id' => $addon['id'] ?? uniqid(),
                            'name' => $addon['name'] ?? 'Unknown Addon',
                            'description' => $addon['description'] ?? '',
                            'price' => floatval($addon['price'] ?? 0),
                            'available_quantity' => intval($addon['quantity_available'] ?? 999),
                            'is_required' => boolval($addon['is_required'] ?? false),
                            'max_quantity_per_ticket' => intval($addon['max_quantity_per_ticket'] ?? 1),
                        ];
                    }
                }
            }
        }

        return null;
    }
}
