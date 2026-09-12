<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "All Countries",
            "Anguilla",
            "Antigua and Barbuda",
            "Aruba",
            "Bahamas",
            "Bonaire",
            "Barbados",
            "British Virgin Islands",
            "Belize",
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
            "US Virgin Islands"
        ];
        foreach($data as $item){
            Nationality::create([
                'name'=>$item
            ]);
        }
    }
}
