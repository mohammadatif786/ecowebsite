<?php

namespace App\Services;

use App\Models\LinkUpEvent;
use App\Models\Ticket;

class EventInventoryService
{
    /**
     * Build initial inventory from tickets
     */
    public function getInventoryFromTickets(LinkUpEvent $event)
    {
        $tickets = Ticket::where('event_id', $event->id)
            ->whereNotNull('drink_addons')
            ->get();

        $drinkInventory = [];

        foreach ($tickets as $ticket) {
            if (!$ticket->drink_addons || !isset($ticket->drink_addons['items'])) {
                continue;
            }

            $categories = $ticket->drink_addons['items'];

            foreach ($categories as $categoryKey => $drinks) {
                if (!is_array($drinks)) continue;

                foreach ($drinks as $drink) {
                    if (!isset($drink['name'])) continue;

                    $drinkKey = $categoryKey . '_' . $drink['name'];

                    if (!isset($drinkInventory[$drinkKey])) {
                        $drinkInventory[$drinkKey] = [
                            'name' => $drink['name'],
                            'category' => $this->formatCategoryName($categoryKey),
                            'price' => floatval($drink['cost'] ?? 0),
                            'initial_qty' => intval($drink['qty'] ?? 0),
                            'actual_sold' => 0,
                        ];
                    } else {
                        // If same drink appears in multiple tickets, sum initial quantities
                        $drinkInventory[$drinkKey]['initial_qty'] += intval($drink['qty'] ?? 0);
                    }
                }
            }
        }

        return $drinkInventory;
    }

    /**
     * Calculate actual sold items from ticket sales
     */
    public function calculateSales($ticketSales, array $drinkInventory)
    {
        foreach ($ticketSales as $sale) {
            if (!$sale->drink_addons || !is_array($sale->drink_addons)) {
                continue;
            }

            foreach ($sale->drink_addons as $addon) {
                if (!isset($addon['name'])) continue;

                // Match by name to find the drink in inventory
                $matchedKey = null;
                foreach ($drinkInventory as $key => $inventoryItem) {
                    if ($inventoryItem['name'] === $addon['name']) {
                        $matchedKey = $key;
                        break;
                    }
                }

                if ($matchedKey) {
                    $drinkInventory[$matchedKey]['actual_sold'] += intval($addon['quantity'] ?? 0);
                }
            }
        }

        return $drinkInventory;
    }

    /**
     * Format category name for display
     */
    private function formatCategoryName($categoryKey)
    {
        $mapping = [
            'mixDrinks' => 'Mix Drinks',
            'wines' => 'Wines',
            'waters' => 'Waters',
            'beers' => 'Beers',
            'softDrinks' => 'Soft Drinks',
            'bottles' => 'Bottles',
        ];

        return $mapping[$categoryKey] ?? ucfirst($categoryKey);
    }
    
    /**
     * Calculate metrics for each drink 
     */
    public function calculateDrinkMetrics($drinkInventory) 
    {
        $individualDrinks = [];
        $drinkCategories = [];

        foreach ($drinkInventory as $key => $drink) {
            $forecast = $drink['initial_qty']; // Initial quantity is our forecast
            $actual = $drink['actual_sold'];
            $required = ceil($forecast * 1.2); // 20% buffer
            $remaining = $forecast - $actual; // What's left
            $variance = $forecast - $actual; // Difference between forecast and actual

            $individualDrinks[] = [
                'id' => $key,
                'category' => $drink['category'],
                'name' => $drink['name'],
                'price' => $drink['price'],
                'forecast' => $forecast,
                'required' => $required,
                'actual' => $actual,
                'remaining' => max(0, $remaining),
                'variance' => $variance,
            ];

            // Add to categories list
            if (!in_array($drink['category'], $drinkCategories)) {
                $drinkCategories[] = $drink['category'];
            }
        }

        return [
            'individualDrinks' => $individualDrinks,
            'drinkCategories' => $drinkCategories
        ];
    }

    /**
     * Process bottle service packages (if any)
     */
    public function calculateBottleServiceMetrics($drinkPackages, $ticketSales)
    {
        $bottleService = [];

        foreach ($drinkPackages as $package) {
            $packageSalesCount = $ticketSales->where('package_id', $package->id)->count();
            
            if ($packageSalesCount > 0 && $package->bottles) {
                $bottleItems = is_string($package->bottles) ? json_decode($package->bottles, true) : $package->bottles;
                
                if (is_array($bottleItems)) {
                    foreach ($bottleItems as $bottle) {
                        if (!is_array($bottle)) continue;
                        
                        $bottleName = $bottle['name'] ?? 'Unknown Bottle';
                        $bottlePrice = floatval($bottle['price'] ?? 0);
                        
                        // Calculate actual sold for this bottle
                        $actualSold = 0;
                        foreach ($ticketSales as $sale) {
                            if ($sale->package_id == $package->id && $sale->drink_addons) {
                                foreach ($sale->drink_addons as $addon) {
                                    if (isset($addon['name']) && $addon['name'] === $bottleName) {
                                        $actualSold += intval($addon['quantity'] ?? 0);
                                    }
                                }
                            }
                        }
                        
                        $forecast = $packageSalesCount;
                        $required = ceil($forecast * 1.2);
                        $remaining = $forecast - $actualSold;
                        $variance = $forecast - $actualSold;
                        
                        $bottleService[] = [
                            'id' => 'bottle_' . $package->id . '_' . $bottleName,
                            'type' => 'Bottle',
                            'name' => $bottleName,
                            'price' => $bottlePrice,
                            'forecast' => $forecast,
                            'required' => $required,
                            'actual' => $actualSold,
                            'remaining' => max(0, $remaining),
                            'variance' => $variance,
                        ];
                    }
                }
            }
        }

        return $bottleService;
    }
}
