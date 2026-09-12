<?php

namespace App\DTOs;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionPlanStatus;

final class SubscriptionPlanData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $emoji,
        public readonly ?string $tagline,
        public readonly string $stripeProductId,
        public readonly float $price,
        public readonly BillingCycle $billingCycle,
        public readonly int $durationDays,
        public readonly SubscriptionPlanStatus $status,
        public readonly ?string $description,
        public readonly array $features,
        public readonly array $perks,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['plan_name'],
            emoji: $data['plan_emoji'] ?? null,
            tagline: $data['tagline'] ?? null,
            stripeProductId: $data['stripe_product_id'],
            price: (float) $data['price'],
            billingCycle: BillingCycle::from($data['billing_cycle']),
            durationDays: (int) ($data['duration_days'] ?? 0),
            status: SubscriptionPlanStatus::from($data['status'] ?? 'draft'),
            description: $data['description'] ?? null,
            features: $data['features'] ?? [],
            perks: $data['perks'] ?? [],
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'name' => $this->name,
            'emoji' => $this->emoji,
            'tagline' => $this->tagline,
            'stripe_product_id' => $this->stripeProductId,
            'price' => $this->price,
            'billing_cycle' => $this->billingCycle,
            'duration_days' => $this->durationDays,
            'status' => $this->status,
            'description' => $this->description,
            'features' => $this->features,
            'perks' => $this->perks,
        ];
    }
}
