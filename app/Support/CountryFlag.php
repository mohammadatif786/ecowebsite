<?php

namespace App\Support;

class CountryFlag
{
    /**
     * Common full country names (as stored inconsistently on users.country/new_country)
     * mapped to their ISO 3166-1 alpha-2 code.
     */
    protected const NAME_TO_ALPHA2 = [
        'afghanistan' => 'AF', 'angola' => 'AO', 'anguilla' => 'AI', 'antigua and barbuda' => 'AG',
        'argentina' => 'AR', 'armenia' => 'AM', 'aruba' => 'AW', 'australia' => 'AU', 'austria' => 'AT',
        'azerbaijan' => 'AZ', 'bahamas' => 'BS', 'bahrain' => 'BH', 'barbados' => 'BB', 'belize' => 'BZ',
        'bermuda' => 'BM', 'bolivia' => 'BO', 'brazil' => 'BR', 'canada' => 'CA', 'cayman islands' => 'KY',
        'chile' => 'CL', 'colombia' => 'CO', 'costa rica' => 'CR', 'cuba' => 'CU', 'curacao' => 'CW',
        'dominica' => 'DM', 'dominican republic' => 'DO', 'ecuador' => 'EC', 'el salvador' => 'SV',
        'france' => 'FR', 'germany' => 'DE', 'ghana' => 'GH', 'grenada' => 'GD', 'guatemala' => 'GT',
        'guyana' => 'GY', 'haiti' => 'HT', 'honduras' => 'HN', 'india' => 'IN', 'ireland' => 'IE',
        'italy' => 'IT', 'jamaica' => 'JM', 'japan' => 'JP', 'kenya' => 'KE', 'mexico' => 'MX',
        'montserrat' => 'MS', 'netherlands' => 'NL', 'nicaragua' => 'NI', 'nigeria' => 'NG',
        'pakistan' => 'PK', 'panama' => 'PA', 'paraguay' => 'PY', 'peru' => 'PE',
        'puerto rico' => 'PR', 'saint kitts and nevis' => 'KN', 'saint lucia' => 'LC',
        'saint vincent and the grenadines' => 'VC', 'sint maarten' => 'SX', 'south africa' => 'ZA',
        'spain' => 'ES', 'suriname' => 'SR', 'trinidad and tobago' => 'TT', 'turks and caicos islands' => 'TC',
        'united kingdom' => 'GB', 'united states' => 'US', 'united states of america' => 'US',
        'uruguay' => 'UY', 'venezuela' => 'VE', 'virgin islands' => 'VG',
    ];

    public static function emoji(?string $value): string
    {
        if (! $value) {
            return '';
        }

        $value = trim($value);

        $alpha2 = preg_match('/^[A-Za-z]{2}$/', $value)
            ? strtoupper($value)
            : self::NAME_TO_ALPHA2[strtolower($value)] ?? null;

        if (! $alpha2) {
            return '';
        }

        $flag = '';
        foreach (str_split($alpha2) as $char) {
            $flag .= mb_chr(0x1F1E6 + (ord($char) - 65), 'UTF-8');
        }

        return $flag;
    }
}
