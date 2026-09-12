<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WellnessSlotBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot_date' => $this->slot_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'count' => $this->count,
        ];
    }
}
