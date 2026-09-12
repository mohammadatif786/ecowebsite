<?php

namespace App\DTOs\Admin;

class CountryMetricDTO
{
    public function __construct(
        public string $country,
        public string $region,
        public int $users = 0,
        public int $merchants = 0,
        public int $organizers = 0,
        public float $tickets = 0,
        public float $subscriptions = 0,
        public float $marketplace = 0,
        public float $eats = 0,
        public float $merchantPay = 0,
        public float $wallet = 0,
        public float $live = 0,
        public float $ads = 0,
        public float $wellness = 0,
        public float $cookouts = 0,
        public float $linkup360 = 0,
        public float $coinsPurchased = 0,
        public float $coinsRedeemed = 0
    ) {}

    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'region' => $this->region,
            'users' => $this->users,
            'merchants' => $this->merchants,
            'organizers' => $this->organizers,
            'tickets' => $this->tickets,
            'subscriptions' => $this->subscriptions,
            'marketplace' => $this->marketplace,
            'eats' => $this->eats,
            'merchantPay' => $this->merchantPay,
            'wallet' => $this->wallet,
            'live' => $this->live,
            'ads' => $this->ads,
            'wellness' => $this->wellness,
            'cookouts' => $this->cookouts,
            'linkup360' => $this->linkup360,
            'coinsPurchased' => $this->coinsPurchased,
            'coinsRedeemed' => $this->coinsRedeemed,
        ];
    }
}
