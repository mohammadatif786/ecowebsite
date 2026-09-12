<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxRules = [
            "_meta" => [
                "version" => "1.0",
                "notes" => "Template for LinkUp tax engine. Fill in exact rates from an external tax source. All logic is based on event location, not buyer location.",
                "default_rules" => [
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "tax_based_on" => "event_location",
                    "allow_local_overrides" => true
                ]
            ],
            "US" => [
                "AL" => [
                    "name" => "Alabama",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "General admissions taxable. Use state + local combined rate."
                ],
                "AK" => [
                    "name" => "Alaska",
                    "no_state_sales_tax" => true,
                    "tax_ticket" => false,
                    "tax_fees" => false,
                    "allow_local_sales_tax" => true,
                    "notes" => "No statewide tax, but many cities/boroughs charge local sales tax. Configure by city if needed."
                ],
                "AZ" => [
                    "name" => "Arizona",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Most entertainment/amusement charges are taxable."
                ],
                "AR" => [
                    "name" => "Arkansas",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions generally taxable; use combined state + local rate."
                ],
                "CA" => [
                    "name" => "California",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Statewide base rate plus district taxes. Treat ticket + fees as taxable amount."
                ],
                "CO" => [
                    "name" => "Colorado",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions generally taxable, but some exemptions exist. Some home-rule cities have separate rules."
                ],
                "CT" => [
                    "name" => "Connecticut",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State-level tax only; no general local sales tax. Some entertainment may be taxed at special rates."
                ],
                "DE" => [
                    "name" => "Delaware",
                    "no_state_sales_tax" => true,
                    "tax_ticket" => false,
                    "tax_fees" => false,
                    "allow_local_sales_tax" => false,
                    "notes" => "No general sales tax. Do not charge sales tax unless a specific local or excise scheme is implemented."
                ],
                "FL" => [
                    "name" => "Florida",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "state_tax" => 0.06,
                    "county_tax" => 0.01,
                    "notes" => "Use state rate + county surtax (e.g., 6% + 1% in Broward). Ticket and service fees are both taxable."
                ],
                "GA" => [
                    "name" => "Georgia",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions generally taxable; use combined state + local rate."
                ],
                "HI" => [
                    "name" => "Hawaii",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "General Excise Tax (GET) applies instead of traditional sales tax. Apply GET to ticket + fees."
                ],
                "ID" => [
                    "name" => "Idaho",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Entertainment/amusement usually taxable."
                ],
                "IL" => [
                    "name" => "Illinois",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Use state + local rate. Some cities (e.g., Chicago) also impose separate amusement taxes – handle via local override."
                ],
                "IN" => [
                    "name" => "Indiana",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Statewide base rate, with some local add-ons in certain jurisdictions."
                ],
                "IA" => [
                    "name" => "Iowa",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions and amusement services generally taxable."
                ],
                "KS" => [
                    "name" => "Kansas",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Ticket + fees taxable; use combined state + local rate."
                ],
                "KY" => [
                    "name" => "Kentucky",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State-level tax, no general local sales tax. Some admissions specifically listed as taxable."
                ],
                "LA" => [
                    "name" => "Louisiana",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Very high combined rates in some parishes. Admissions taxable."
                ],
                "ME" => [
                    "name" => "Maine",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State tax only; some special excise and lodging taxes separate."
                ],
                "MD" => [
                    "name" => "Maryland",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State-level sales tax; some local admissions and amusement taxes may apply separately."
                ],
                "MA" => [
                    "name" => "Massachusetts",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State rate applies; some local ‘meals’ or entertainment taxes may be separate."
                ],
                "MI" => [
                    "name" => "Michigan",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "Statewide rate; no general local sales tax. Admissions commonly taxable."
                ],
                "MN" => [
                    "name" => "Minnesota",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Some local entertainment taxes apply in certain cities."
                ],
                "MS" => [
                    "name" => "Mississippi",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Restaurant and tourism-related taxes may be higher in some jurisdictions."
                ],
                "MO" => [
                    "name" => "Missouri",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions generally taxable; configure combined rate."
                ],
                "MT" => [
                    "name" => "Montana",
                    "no_state_sales_tax" => true,
                    "tax_ticket" => false,
                    "tax_fees" => false,
                    "allow_local_sales_tax" => true,
                    "notes" => "No statewide sales tax; some tourist towns charge local resort or sales taxes."
                ],
                "NE" => [
                    "name" => "Nebraska",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions taxable. Use combined state + local rate."
                ],
                "NV" => [
                    "name" => "Nevada",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Sales tax plus Live Entertainment Tax for certain venues; handle LET as a separate line if needed."
                ],
                "NH" => [
                    "name" => "New Hampshire",
                    "no_state_sales_tax" => true,
                    "tax_ticket" => false,
                    "tax_fees" => false,
                    "allow_local_sales_tax" => false,
                    "notes" => "No general sales tax; separate 9% ‘meals and rooms’ tax for some transactions, not general event tickets."
                ],
                "NJ" => [
                    "name" => "New Jersey",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Some zones have reduced rates. Entertainment admissions generally taxable."
                ],
                "NM" => [
                    "name" => "New Mexico",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Gross Receipts Tax model; apply GRT on ticket + fees."
                ],
                "NY" => [
                    "name" => "New York",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State + local sales tax; NYC and some localities may have additional entertainment/venue rules."
                ],
                "NC" => [
                    "name" => "North Carolina",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions generally treated as taxable services."
                ],
                "ND" => [
                    "name" => "North Dakota",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions taxable; configure local add-ons as needed."
                ],
                "OH" => [
                    "name" => "Ohio",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Counties and transit authorities can add local rates."
                ],
                "OK" => [
                    "name" => "Oklahoma",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "High combined rates in many cities; admissions taxable."
                ],
                "OR" => [
                    "name" => "Oregon",
                    "no_state_sales_tax" => true,
                    "tax_ticket" => false,
                    "tax_fees" => false,
                    "allow_local_sales_tax" => true,
                    "notes" => "No statewide sales tax; a few municipalities have targeted taxes (e.g., on prepared food)."
                ],
                "PA" => [
                    "name" => "Pennsylvania",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Some localities (Philadelphia, Allegheny County) have local add-ons."
                ],
                "RI" => [
                    "name" => "Rhode Island",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "State rate; additional 1% meals/restaurant tax separate from ticketing."
                ],
                "SC" => [
                    "name" => "South Carolina",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Hospitality and tourism taxes may apply in resort areas."
                ],
                "SD" => [
                    "name" => "South Dakota",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State + local sales and gross receipts taxes; admissions generally taxable."
                ],
                "TN" => [
                    "name" => "Tennessee",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "One of the highest combined rates; admissions taxable."
                ],
                "TX" => [
                    "name" => "Texas",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State tax plus city/county/special district; amusement services generally taxable."
                ],
                "UT" => [
                    "name" => "Utah",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State and local rates; some resort or tourism add-ons."
                ],
                "VT" => [
                    "name" => "Vermont",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Admissions taxable; some local option taxes."
                ],
                "VA" => [
                    "name" => "Virginia",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State + local rate; some regions have additional transit or regional taxes."
                ],
                "WA" => [
                    "name" => "Washington",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "High combined rates in many cities; admissions taxable."
                ],
                "WV" => [
                    "name" => "West Virginia",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "Local option sales taxes allowed in some municipalities."
                ],
                "WI" => [
                    "name" => "Wisconsin",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State + local rate; some entertainment taxes apply."
                ],
                "WY" => [
                    "name" => "Wyoming",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => true,
                    "notes" => "State + local rate; admissions generally taxable."
                ],
                "DC" => [
                    "name" => "District of Columbia",
                    "no_state_sales_tax" => false,
                    "tax_ticket" => true,
                    "tax_fees" => true,
                    "allow_local_sales_tax" => false,
                    "notes" => "Standard rate applies to admissions."
                ]
            ]
        ];

        DB::table('settings')->updateOrInsert(
            ['key' => 'tax_rules'],
            ['value' => json_encode(['label' => 'Tax Rules Configuration']), 'tax_rules' => json_encode($taxRules)]
        );
    }
}
