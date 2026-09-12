<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'expiry_date' => $this->expiry_date,
            'image_url' => $this->image_url,
        ];
    }
}
