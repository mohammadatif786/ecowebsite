<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // Caribbean (27)
            ['name' => 'Bahamas', 'code' => 'bs', 'subregion' => 'Caribbean', 'flag' => '🇧🇸', 'currency' => 'BSD$', 'support_email' => 'bs.support@linkup.app'],
            ['name' => 'Jamaica', 'code' => 'jm', 'subregion' => 'Caribbean', 'flag' => '🇯🇲', 'currency' => 'JMD$', 'support_email' => 'jm.support@linkup.app'],
            ['name' => 'Trinidad & Tobago', 'code' => 'tt', 'subregion' => 'Caribbean', 'flag' => '🇹🇹', 'currency' => 'TTD$', 'support_email' => 'tt.support@linkup.app'],
            ['name' => 'Barbados', 'code' => 'bb', 'subregion' => 'Caribbean', 'flag' => '🇧🇧', 'currency' => 'BBD$', 'support_email' => 'bb.support@linkup.app'],
            ['name' => 'Guyana', 'code' => 'gy', 'subregion' => 'Caribbean', 'flag' => '🇬🇾', 'currency' => 'GYD$', 'support_email' => 'gy.support@linkup.app'],
            ['name' => 'Suriname', 'code' => 'sr', 'subregion' => 'Caribbean', 'flag' => '🇸🇷', 'currency' => 'SRD$', 'support_email' => 'sr.support@linkup.app'],
            ['name' => 'Haiti', 'code' => 'ht', 'subregion' => 'Caribbean', 'flag' => '🇭🇹', 'currency' => 'HTG$', 'support_email' => 'ht.support@linkup.app'],
            ['name' => 'Dominican Republic', 'code' => 'do', 'subregion' => 'Caribbean', 'flag' => '🇩🇴', 'currency' => 'DOP$', 'support_email' => 'do.support@linkup.app'],
            ['name' => 'Cuba', 'code' => 'cu', 'subregion' => 'Caribbean', 'flag' => '🇨🇺', 'currency' => 'CUP$', 'support_email' => 'cu.support@linkup.app'],
            ['name' => 'Puerto Rico', 'code' => 'pr', 'subregion' => 'Caribbean', 'flag' => '🇵🇷', 'currency' => 'USD$', 'support_email' => 'pr.support@linkup.app'],
            ['name' => 'Antigua & Barbuda', 'code' => 'ag', 'subregion' => 'Caribbean', 'flag' => '🇦🇬', 'currency' => 'XCD$', 'support_email' => 'ag.support@linkup.app'],
            ['name' => 'Saint Lucia', 'code' => 'lc', 'subregion' => 'Caribbean', 'flag' => '🇱🇨', 'currency' => 'XCD$', 'support_email' => 'lc.support@linkup.app'],
            ['name' => 'Grenada', 'code' => 'gd', 'subregion' => 'Caribbean', 'flag' => '🇬🇩', 'currency' => 'XCD$', 'support_email' => 'gd.support@linkup.app'],
            ['name' => 'St. Vincent', 'code' => 'vc', 'subregion' => 'Caribbean', 'flag' => '🇻🇨', 'currency' => 'XCD$', 'support_email' => 'vc.support@linkup.app'],
            ['name' => 'St. Kitts & Nevis', 'code' => 'kn', 'subregion' => 'Caribbean', 'flag' => '🇰🇳', 'currency' => 'XCD$', 'support_email' => 'kn.support@linkup.app'],
            ['name' => 'Dominica', 'code' => 'dm', 'subregion' => 'Caribbean', 'flag' => '🇩🇲', 'currency' => 'XCD$', 'support_email' => 'dm.support@linkup.app'],
            ['name' => 'Cayman Islands', 'code' => 'ky', 'subregion' => 'Caribbean', 'flag' => '🇰🇾', 'currency' => 'KYD$', 'support_email' => 'ky.support@linkup.app'],
            ['name' => 'Turks & Caicos', 'code' => 'tc', 'subregion' => 'Caribbean', 'flag' => '🇹🇨', 'currency' => 'USD$', 'support_email' => 'tc.support@linkup.app'],
            ['name' => 'British Virgin Islands', 'code' => 'vg', 'subregion' => 'Caribbean', 'flag' => '🇻🇬', 'currency' => 'USD$', 'support_email' => 'vg.support@linkup.app'],
            ['name' => 'US Virgin Islands', 'code' => 'vi', 'subregion' => 'Caribbean', 'flag' => '🇻🇮', 'currency' => 'USD$', 'support_email' => 'vi.support@linkup.app'],
            ['name' => 'Anguilla', 'code' => 'ai', 'subregion' => 'Caribbean', 'flag' => '🇦🇮', 'currency' => 'XCD$', 'support_email' => 'ai.support@linkup.app'],
            ['name' => 'Montserrat', 'code' => 'ms', 'subregion' => 'Caribbean', 'flag' => '🇲🇸', 'currency' => 'XCD$', 'support_email' => 'ms.support@linkup.app'],
            ['name' => 'Aruba', 'code' => 'aw', 'subregion' => 'Caribbean', 'flag' => '🇦🇼', 'currency' => 'AWG$', 'support_email' => 'aw.support@linkup.app'],
            ['name' => 'Curaçao', 'code' => 'cw', 'subregion' => 'Caribbean', 'flag' => '🇨🇼', 'currency' => 'ANG$', 'support_email' => 'cw.support@linkup.app'],
            ['name' => 'Sint Maarten', 'code' => 'sx', 'subregion' => 'Caribbean', 'flag' => '🇸🇽', 'currency' => 'ANG$', 'support_email' => 'sx.support@linkup.app'],
            ['name' => 'Martinique', 'code' => 'mq', 'subregion' => 'Caribbean', 'flag' => '🇲🇶', 'currency' => 'EUR$', 'support_email' => 'mq.support@linkup.app'],
            ['name' => 'Guadeloupe', 'code' => 'gp', 'subregion' => 'Caribbean', 'flag' => '🇬🇵', 'currency' => 'EUR$', 'support_email' => 'gp.support@linkup.app'],

            // Central America (8)
            ['name' => 'Mexico', 'code' => 'mx', 'subregion' => 'Central America', 'flag' => '🇲🇽', 'currency' => 'MXN$', 'support_email' => 'mx.support@linkup.app'],
            ['name' => 'Belize', 'code' => 'bz', 'subregion' => 'Central America', 'flag' => '🇧🇿', 'currency' => 'BZD$', 'support_email' => 'bz.support@linkup.app'],
            ['name' => 'Guatemala', 'code' => 'gt', 'subregion' => 'Central America', 'flag' => '🇬🇹', 'currency' => 'GTQ$', 'support_email' => 'gt.support@linkup.app'],
            ['name' => 'El Salvador', 'code' => 'sv', 'subregion' => 'Central America', 'flag' => '🇸🇻', 'currency' => 'USD$', 'support_email' => 'sv.support@linkup.app'],
            ['name' => 'Honduras', 'code' => 'hn', 'subregion' => 'Central America', 'flag' => '🇭🇳', 'currency' => 'HNL$', 'support_email' => 'hn.support@linkup.app'],
            ['name' => 'Nicaragua', 'code' => 'ni', 'subregion' => 'Central America', 'flag' => '🇳🇮', 'currency' => 'NIO$', 'support_email' => 'ni.support@linkup.app'],
            ['name' => 'Costa Rica', 'code' => 'cr', 'subregion' => 'Central America', 'flag' => '🇨🇷', 'currency' => 'CRC$', 'support_email' => 'cr.support@linkup.app'],
            ['name' => 'Panama', 'code' => 'pa', 'subregion' => 'Central America', 'flag' => '🇵🇦', 'currency' => 'PAB$', 'support_email' => 'pa.support@linkup.app'],

            // South America (9)
            ['name' => 'Colombia', 'code' => 'co', 'subregion' => 'South America', 'flag' => '🇨🇴', 'currency' => 'COP$', 'support_email' => 'co.support@linkup.app'],
            ['name' => 'Venezuela', 'code' => 've', 'subregion' => 'South America', 'flag' => '🇻🇪', 'currency' => 'VES$', 'support_email' => 've.support@linkup.app'],
            ['name' => 'Ecuador', 'code' => 'ec', 'subregion' => 'South America', 'flag' => '🇪🇨', 'currency' => 'USD$', 'support_email' => 'ec.support@linkup.app'],
            ['name' => 'Peru', 'code' => 'pe', 'subregion' => 'South America', 'flag' => '🇵🇪', 'currency' => 'PEN$', 'support_email' => 'pe.support@linkup.app'],
            ['name' => 'Bolivia', 'code' => 'bo', 'subregion' => 'South America', 'flag' => '🇧🇴', 'currency' => 'BOB$', 'support_email' => 'bo.support@linkup.app'],
            ['name' => 'Chile', 'code' => 'cl', 'subregion' => 'South America', 'flag' => '🇨🇱', 'currency' => 'CLP$', 'support_email' => 'cl.support@linkup.app'],
            ['name' => 'Argentina', 'code' => 'ar', 'subregion' => 'South America', 'flag' => '🇦🇷', 'currency' => 'ARS$', 'support_email' => 'ar.support@linkup.app'],
            ['name' => 'Uruguay', 'code' => 'uy', 'subregion' => 'South America', 'flag' => '🇺🇾', 'currency' => 'UYU$', 'support_email' => 'uy.support@linkup.app'],
            ['name' => 'Paraguay', 'code' => 'py', 'subregion' => 'South America', 'flag' => '🇵🇾', 'currency' => 'PYG$', 'support_email' => 'py.support@linkup.app'],
            ['name' => 'Brazil', 'code' => 'br', 'subregion' => 'South America', 'flag' => '🇧🇷', 'currency' => 'BRL$', 'support_email' => 'br.support@linkup.app'],

            // User requested
            ['name' => 'Pakistan', 'code' => 'pk', 'subregion' => 'South Asia', 'flag' => '🇵🇰', 'currency' => 'PKR$', 'support_email' => 'pk.support@linkup.app'],
        ];

        // Clean up old data if any (optional, but good for consistency with client doc)
        // Country::truncate(); 

        foreach ($countries as $countryData) {
            Country::updateOrCreate(
                ['code' => $countryData['code']],
                $countryData
            );
        }
    }
}
