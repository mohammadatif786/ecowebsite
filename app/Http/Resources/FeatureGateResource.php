<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureGateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'icon'        => $this->icon,
            'description' => $this->description,
            'lock_type'   => $this->lock_type->value,
            'lock_label'  => $this->lock_type->label(),
            'is_active'   => $this->is_active,
            'plans'       => $this->whenLoaded(
                'plans',
                fn() =>
                $this->plans->map(fn($plan) => [
                    'id'   => $plan->id,
                    'name' => $plan->name,
                    'emoji' => $plan->emoji,
                ])
            ),
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
