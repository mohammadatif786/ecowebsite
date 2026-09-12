<?php

namespace App\Services\Organizer;

use App\Models\DrinkPackage;

class DrinkPackageService
{
    /**
     * store a newly created drink package in storage.
     */
    public function store($data, $organizerId): DrinkPackage
    {
        return DrinkPackage::create([...$this->prepareData($data), 'organizer_id' => $organizerId]);
    }

    /**
     * update the specified drink package in storage.
     */
    public function update($id, $data, $organizerId): DrinkPackage
    {
        $drinkPackage = DrinkPackage::where('organizer_id', $organizerId)
            ->findOrFail($id);

        $drinkPackage->update($this->prepareData($data));

        return $drinkPackage;
    }

    /**
     * Get the drink package details.
     */
    private function prepareData($data)
    {
        return [
            'name' => $data['name'],
            'bottles' => $data['bottles'] ?? null,
            'chasers' => $data['chasers'] ?? null,
            'waters' => $data['waters'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Find a drink package by ID and organizer ID.
     */
    public function find($id, $organizerId)
    {
        return DrinkPackage::where('organizer_id', $organizerId)
            ->findOrFail($id);
    }

    /**
     * Delete a drink package by ID and organizer ID.
     */
    public function delete($id, $organizerId)
    {
        $package = $this->find($id, $organizerId);
        $package->delete();

        return true;
    }
}
