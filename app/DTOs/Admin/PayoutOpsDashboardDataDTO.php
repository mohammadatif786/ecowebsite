<?php

namespace App\DTOs\Admin;

use Illuminate\Contracts\Support\Arrayable;

class PayoutOpsDashboardDataDTO implements Arrayable
{
    public function __construct(
        public array $units,
        public array $countries,
        public array $payoutRequests,
        public array $auditTrails,
        public array $totals
    ) {}

    public function toArray(): array
    {
        return [
            'units' => array_map(fn($u) => $u->toArray(), $this->units),
            'countries' => array_map(fn($c) => $c->toArray(), $this->countries),
            'payoutRequests' => $this->payoutRequests,
            'auditTrails' => $this->auditTrails,
            'totals' => $this->totals,
        ];
    }
}
