<?php

namespace Database\Seeders;

use App\Models\CaribbeanIsland;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaribbeanIslandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Caribbean Islands
            "Anguilla",
            "Antigua and Barbuda",
            "Aruba",
            "Bahamas",
            "Barbados",
            "British Virgin Islands",
            "Belize",
            "Bonaire",
            "Cayman Islands",
            "Cuba",
            "Curacao",
            "Dominica",
            "Dominican Republic",
            "Grenada",
            "Guadeloupe",
            "Haiti",
            "Jamaica",
            "Martinique",
            "Montserrat",
            "Puerto Rico",
            "Saint Barthelemy",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Martin",
            "Saint Vincent and the Grenadines",
            "Suriname",
            "Trinidad and Tobago",
            "Turks and Caicos Islands",
            "US Virgin Islands",

            // Latin America (Central + South America)
            "Argentina",
            "Bolivia",
            "Brazil",
            "Chile",
            "Colombia",
            "Costa Rica",
            "Ecuador",
            "El Salvador",
            "Guatemala",
            "Guyana",
            "Honduras",
            "Mexico",
            "Nicaragua",
            "Panama",
            "Paraguay",
            "Peru",
            "Uruguay",
            "Venezuela",
        ];
        foreach ($data as $item) {
            CaribbeanIsland::create([
                'name' => $item
            ]);
        }
    }
}
