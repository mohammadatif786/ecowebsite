<?php

namespace App\Services;

/**
 * Looks up a drink/bottle addon on a ticket's config by id or name.
 * Mirrors the identical private helpers duplicated in
 * Frontend\WalletController and Frontend\StripeController.
 */
class TicketAddonResolver
{
    public function findAddonInTicket($ticket, $addonId)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }

        $categories = $ticket->drink_addons['items'];
        $idCounter = 1;

        foreach ($categories as $category => $items) {
            foreach ($items as $item) {
                if ($idCounter == $addonId) {
                    return [
                        'id' => $idCounter,
                        'name' => $item['name'],
                        'cost' => $item['cost'],
                        'qty' => $item['qty'],
                        'price' => floatval($item['cost']) / $item['qty'],
                        'category' => $category,
                    ];
                }
                $idCounter++;
            }
        }

        return null;
    }

    public function findLegacyAddonById($ticket, int $id)
    {
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['id']) && intval($b['id']) === $id) {
                    return [
                        'name' => $b['name'] ?? 'Bottle',
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['id']) && intval($c['id']) === $id) {
                    return [
                        'name' => $c['name'] ?? 'Mixer',
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['id']) && intval($w['id']) === $id) {
                    return [
                        'name' => $w['name'] ?? 'Water',
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    public function findLegacyAddonByName($ticket, string $name)
    {
        $search = trim($name);
        $main = is_array($ticket->main_bottles)
            ? $ticket->main_bottles
            : (is_string($ticket->main_bottles) ? json_decode($ticket->main_bottles, true) : []);
        $chasers = is_array($ticket->chasers_or_mixers)
            ? $ticket->chasers_or_mixers
            : (is_string($ticket->chasers_or_mixers) ? json_decode($ticket->chasers_or_mixers, true) : []);
        $waters = is_array($ticket->water_options)
            ? $ticket->water_options
            : (is_string($ticket->water_options) ? json_decode($ticket->water_options, true) : []);

        if (!empty($main) && is_array($main)) {
            foreach ($main as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($chasers) && is_array($chasers)) {
            foreach ($chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($waters) && is_array($waters)) {
            foreach ($waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    public function findAddonByNameAndCategory($ticket, $name, $categoryKey)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $items = $ticket->drink_addons['items'];
        if (!isset($items[$categoryKey]) || !is_array($items[$categoryKey])) {
            return null;
        }
        foreach ($items[$categoryKey] as $addon) {
            if (isset($addon['name']) && strcasecmp(trim($addon['name']), trim($name)) === 0) {
                return $addon;
            }
        }
        return null;
    }

    public function findAddonByNameAnyCategory($ticket, $name)
    {
        if (!$ticket->drink_addons || !is_array($ticket->drink_addons) || !isset($ticket->drink_addons['items'])) {
            return null;
        }
        $search = trim($name);
        foreach ($ticket->drink_addons['items'] as $category => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $addon) {
                if (isset($addon['name']) && strcasecmp(trim($addon['name']), $search) === 0) {
                    if (!isset($addon['category'])) {
                        $addon['category'] = $category;
                    }
                    return $addon;
                }
            }
        }
        return null;
    }

    public function findAddonInPackageByName($ticket, $name)
    {
        if (empty($ticket->drinkPackage)) {
            return null;
        }
        $search = trim($name);
        $pkg = $ticket->drinkPackage;
        if (!empty($pkg->bottles) && is_array($pkg->bottles)) {
            foreach ($pkg->bottles as $b) {
                if (isset($b['name']) && strcasecmp(trim($b['name']), $search) === 0) {
                    return [
                        'name' => $b['name'],
                        'category' => 'bottles',
                        'cost' => isset($b['cost']) ? $b['cost'] : (isset($b['price']) ? $b['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->chasers) && is_array($pkg->chasers)) {
            foreach ($pkg->chasers as $c) {
                if (isset($c['name']) && strcasecmp(trim($c['name']), $search) === 0) {
                    return [
                        'name' => $c['name'],
                        'category' => 'mixDrinks',
                        'cost' => isset($c['cost']) ? $c['cost'] : (isset($c['price']) ? $c['price'] : 0),
                    ];
                }
            }
        }
        if (!empty($pkg->waters) && is_array($pkg->waters)) {
            foreach ($pkg->waters as $w) {
                if (isset($w['name']) && strcasecmp(trim($w['name']), $search) === 0) {
                    return [
                        'name' => $w['name'],
                        'category' => 'waters',
                        'cost' => isset($w['cost']) ? $w['cost'] : (isset($w['price']) ? $w['price'] : 0),
                    ];
                }
            }
        }
        return null;
    }

    public function resolveDrinkAddon($ticket, array $addonData)
    {
        $addon = null;
        if (isset($addonData['addon_id'])) {
            $addon = $this->findAddonInTicket($ticket, $addonData['addon_id']);
        }
        if (!$addon && isset($addonData['name']) && isset($addonData['category'])) {
            $addon = $this->findAddonByNameAndCategory($ticket, $addonData['name'], $addonData['category']);
        }
        if (!$addon && isset($addonData['name'])) {
            $addon = $this->findAddonByNameAnyCategory($ticket, $addonData['name']);
        }
        if (!$addon && isset($addonData['name'])) {
            $addon = $this->findAddonInPackageByName($ticket, $addonData['name']);
        }
        if (!$addon && isset($addonData['addon_id'])) {
            $addon = $this->findLegacyAddonById($ticket, intval($addonData['addon_id']));
        }
        if (!$addon && isset($addonData['name'])) {
            $addon = $this->findLegacyAddonByName($ticket, $addonData['name']);
        }

        return $addon;
    }
}
