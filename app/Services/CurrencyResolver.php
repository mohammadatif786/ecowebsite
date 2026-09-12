<?php

namespace App\Services;

class CurrencyResolver
{
    public function resolve(?string $countryCode): string
    {
        $countryValues = ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'CH', 'AE', 'SA', 'PK', 'IN', 'CN', 'JP', 'SG', 'MY', 'BS', 'ZA'];
        $available = in_array($countryCode, $countryValues);

        return $available == true ? $countryCode . "$" : $countryCode;
    }
}
