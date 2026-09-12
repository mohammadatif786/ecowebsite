<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->ticket_type,
            'description' => $this->description,
            'price' => $this->price,
            'promo_price' => $this->promo_price,
            'is_free' => (bool) $this->is_free,
            'has_table' => (bool) $this->has_table,
            'table_price' => $this->table_price,
            'table_capacity' => $this->table_capacity,
            'sections' => $this->sections,
            'package_id' => $this->package_id,
            'drink_package' => $this->whenLoaded('drinkPackage', fn () => $this->drinkPackage),
            'quantity' => $this->quantity,
            'qty_available' => $this->qty_available,
            'sales_start' => $this->sales_start,
            'sales_end' => $this->sales_end,
            'status' => $this->status,
            'cookout' => $this->whenLoaded('extraSetting', fn () => $this->cookout),
            'wellness' => $this->whenLoaded('extraSetting', fn () => $this->wellness),
            'wellness_slot_blocks' => $this->whenLoaded('wellnessSlotBlocks', fn () => WellnessSlotBlockResource::collection($this->wellnessSlotBlocks)),
        ];
    }
}
