<?php

namespace App\DTOs\User\Signup;

/**
 * Turns the saved wizard answers into the profile columns the rest of LinkUp already reads
 * (the same columns and value shapes the old wizard and the mobile app write):
 *
 *   home        -> country (ISO code), state, city
 *   languages   -> language        "English, Spanish" (first pick first)
 *   phone       -> phone_number    (always the verified number, set by CompleteOnboardingAction)
 *   birthday    -> birthday        Y-m-d
 *   interests   -> interests       array
 *   gender      -> gender          male | female
 *   distance    -> distance_filter [0, km]   ("Anywhere" = [0, 20000])
 *   age_range   -> age_filter      [min, max] ("80+" = no upper limit)
 *   show_me     -> link_me_with    male | female | both
 *   roots       -> caribbean_interest
 *   link_with   -> link_me_with_country_name  "all" or "Jamaica, Haiti"
 *   bio         -> about_me
 *   work        -> job
 *   university  -> university (+ university_country for the UVibes campus list), "None" when
 *                  the member didn't go to university
 *   intent      -> whyare          "Make friends, Eats"
 *
 * Skipped optional steps are saved as empty.
 */
final readonly class OnboardingProfileData
{
    /**
     * @param  array<string, mixed>  $columns
     */
    public function __construct(
        public array $columns,
        public ?float $latitude,
        public ?float $longitude,
    ) {}

    /**
     * Steps that must have an answer before the wizard can finish (everything except the photos,
     * which arrive with the final request, and the steps the member may skip).
     *
     * @param  array<string, mixed>  $answers
     * @return list<WizardStep>
     */
    public static function missingSteps(array $answers): array
    {
        return array_values(array_filter(
            WizardStep::cases(),
            fn (WizardStep $step) => ! $step->isLast() && ! array_key_exists($step->value, $answers),
        ));
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    public static function fromAnswers(array $answers): self
    {
        $get = fn (WizardStep $step) => (($answers[$step->value]['skipped'] ?? false) ? [] : ($answers[$step->value] ?? []));

        $home = $get(WizardStep::Home);
        $distance = $get(WizardStep::Distance);
        $ages = $get(WizardStep::AgeRange);
        $university = $get(WizardStep::University);
        $countries = $get(WizardStep::LinkWith)['countries'] ?? [];
        $titles = WizardOptions::intentTitles();

        $km = (int) ($distance['km'] ?? WizardOptions::DISTANCE_ANYWHERE_KM);
        $maxAge = (int) ($ages['max'] ?? WizardOptions::AGE_MAX);

        $allCountries = count(array_diff(WizardOptions::countries(), $countries)) === 0;

        if ($university === []) {
            $school = null;
            $schoolCountry = null;
        } elseif ($university['no_university'] ?? false) {
            $school = 'None';
            $schoolCountry = null;
        } else {
            $school = $university['university'] ?? null;
            $schoolCountry = $university['country'] ?? null;
        }

        $columns = [
            'country' => $home['country'] ?? null,
            'state' => $home['state'] ?? null,
            'city' => $home['city'] ?? null,
            'language' => implode(', ', $get(WizardStep::Languages)['languages'] ?? []),
            'birthday' => $get(WizardStep::Birthday)['birthday'] ?? null,
            'interests' => $get(WizardStep::Interests)['interests'] ?? [],
            'gender' => WizardOptions::GENDERS[$get(WizardStep::Gender)['gender'] ?? ''] ?? null,
            'distance_filter' => [0, $km >= WizardOptions::DISTANCE_ANYWHERE_KM ? WizardOptions::DISTANCE_ANYWHERE_SAVED_KM : $km],
            'age_filter' => [(int) ($ages['min'] ?? WizardOptions::AGE_MIN), $maxAge >= WizardOptions::AGE_MAX ? WizardOptions::AGE_NO_LIMIT : $maxAge],
            'link_me_with' => WizardOptions::SHOW_ME[$get(WizardStep::ShowMe)['show_me'] ?? ''] ?? null,
            'caribbean_interest' => $get(WizardStep::Roots)['roots'] ?? null,
            'link_me_with_country_name' => $allCountries ? 'all' : implode(', ', $countries),
            'about_me' => ($get(WizardStep::Bio)['bio'] ?? null) ?: null,
            'job' => ($get(WizardStep::Work)['work'] ?? null) ?: null,
            'university' => $school,
            'university_country' => $schoolCountry,
            'whyare' => implode(', ', array_values(array_filter(array_map(
                fn ($id) => $titles[$id] ?? null,
                $get(WizardStep::Intent)['intents'] ?? [],
            )))),
        ];

        $lat = $home['latitude'] ?? null;
        $lng = $home['longitude'] ?? null;

        return new self($columns, $lat !== null ? (float) $lat : null, $lng !== null ? (float) $lng : null);
    }
}
