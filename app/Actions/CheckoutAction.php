<?php

namespace App\Actions;

use App\DTOs\CheckoutData;
use App\Services\CheckoutPricingService;
use App\Models\User;

class CheckoutAction
{
    public function __construct(private CheckoutPricingService $pricing) {}

    public function handle(array $requestData, User $user): array
    {
        $dto = new CheckoutData(
            userId: $user->id,
            items: $requestData['items'],
            address: $requestData['address'],
            city: $requestData['city'],
            state: $requestData['state'],
            country: $requestData['country'],
            zip: $requestData['zip'] ?? null,
            paymentMethod: $requestData['payment_method'],
            shippingMethod: $requestData['shipping_method'] ?? 'standard'
        );

        $pricing = $this->pricing->calculate($dto->items, ['country' => $dto->country, 'state' => $dto->state, 'city' => $dto->city, 'zip' => $dto->zip], $dto->shippingMethod);

        return [
            'dto' => $dto,
            'pricing' => $pricing,
            'user' => $user,
        ];
    }
}
