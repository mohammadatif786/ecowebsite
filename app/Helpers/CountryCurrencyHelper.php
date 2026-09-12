<?php

if (! function_exists('country_to_currency')) {

    /**
     * Convert ISO-2 country code to ISO-4217 currency code
     * Stripe compatible
     */
    function country_to_currency(?string $country): string
    {
        if (! $country) {
            return 'usd';
        }

        return match (strtoupper($country)) {

            // A
            'AF' => 'afn',
            'AL' => 'all',
            'DZ' => 'dzd',
            'AO' => 'aoa',
            'AR' => 'ars',
            'AM' => 'amd',
            'AU' => 'aud',
            'AT' => 'eur',
            'AZ' => 'azn',

            // B
            'BS' => 'bsd',
            'BH' => 'bhd',
            'BD' => 'bdt',
            'BB' => 'bbd',
            'BY' => 'byn',
            'BE' => 'eur',
            'BZ' => 'bzd',
            'BJ' => 'xof',
            'BO' => 'bob',
            'BA' => 'bam',
            'BW' => 'bwp',
            'BR' => 'brl',
            'BN' => 'bnd',
            'BG' => 'bgn',

            // C
            'KH' => 'khr',
            'CM' => 'xaf',
            'CA' => 'cad',
            'CV' => 'cve',
            'CF' => 'xaf',
            'TD' => 'xaf',
            'CL' => 'clp',
            'CN' => 'cny',
            'CO' => 'cop',
            'CR' => 'crc',
            'HR' => 'hrk',
            'CY' => 'eur',
            'CZ' => 'czk',

            // D
            'DK' => 'dkk',
            'DO' => 'dop',
            'DJ' => 'djf',

            // E
            'EC' => 'usd',
            'EG' => 'egp',
            'SV' => 'svc',
            'EE' => 'eur',
            'ET' => 'etb',

            // F
            'FI' => 'eur',
            'FR' => 'eur',

            // G
            'GE' => 'gel',
            'DE' => 'eur',
            'GH' => 'ghs',
            'GR' => 'eur',
            'GT' => 'gtq',
            'GN' => 'gnf',

            // H
            'HT' => 'htg',
            'HN' => 'hnl',
            'HK' => 'hkd',
            'HU' => 'huf',

            // I
            'IS' => 'isk',
            'IN' => 'inr',
            'ID' => 'idr',
            'IR' => 'irr',
            'IQ' => 'iqd',
            'IE' => 'eur',
            'IL' => 'ils',
            'IT' => 'eur',

            // J
            'JM' => 'jmd',
            'JP' => 'jpy',
            'JO' => 'jod',

            // K
            'KZ' => 'kzt',
            'KE' => 'kes',
            'KW' => 'kwd',
            'KG' => 'kgs',

            // L
            'LA' => 'lak',
            'LV' => 'eur',
            'LB' => 'lbp',
            'LS' => 'lsl',
            'LR' => 'lrd',
            'LT' => 'eur',
            'LU' => 'eur',

            // M
            'MG' => 'mga',
            'MW' => 'mwk',
            'MY' => 'myr',
            'MV' => 'mvr',
            'ML' => 'xof',
            'MT' => 'eur',
            'MX' => 'mxn',
            'MD' => 'mdl',
            'MN' => 'mnt',
            'MA' => 'mad',
            'MZ' => 'mzn',

            // N
            'NA' => 'nad',
            'NP' => 'npr',
            'NL' => 'eur',
            'NZ' => 'nzd',
            'NI' => 'nio',
            'NG' => 'ngn',
            'NO' => 'nok',

            // O
            'OM' => 'omr',

            // P
            'PK' => 'pkr',
            'PA' => 'pab',
            'PY' => 'pyg',
            'PE' => 'pen',
            'PH' => 'php',
            'PL' => 'pln',
            'PT' => 'eur',

            // Q
            'QA' => 'qar',

            // R
            'RO' => 'ron',
            'RU' => 'rub',
            'RW' => 'rwf',

            // S
            'SA' => 'sar',
            'SN' => 'xof',
            'RS' => 'rsd',
            'SG' => 'sgd',
            'SK' => 'eur',
            'SI' => 'eur',
            'ZA' => 'zar',
            'KR' => 'krw',
            'ES' => 'eur',
            'LK' => 'lkr',
            'SE' => 'sek',
            'CH' => 'chf',

            // T
            'TW' => 'twd',
            'TZ' => 'tzs',
            'TH' => 'thb',
            'TN' => 'tnd',
            'TR' => 'try',

            // U
            'UA' => 'uah',
            'AE' => 'aed',
            'GB' => 'gbp',
            'US' => 'usd',
            'UY' => 'uyu',
            'UZ' => 'uzs',

            // V
            'VE' => 'ves',
            'VN' => 'vnd',

            // Y
            'YE' => 'yer',

            // Z
            'ZM' => 'zmw',
            'ZW' => 'usd',

            default => 'usd',
        };
    }
}
