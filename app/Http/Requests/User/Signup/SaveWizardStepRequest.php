<?php

namespace App\Http\Requests\User\Signup;

use App\DTOs\User\Signup\WizardOptions;
use App\DTOs\User\Signup\WizardStep;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One wizard step's answers. The rules depend on the step in the URL (wizard/steps/{step}).
 * An optional step may be sent with skip=1 and nothing else.
 */
class SaveWizardStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // behind `auth` and the email gate
    }

    public function step(): WizardStep
    {
        $step = $this->route('step');

        return $step instanceof WizardStep ? $step : WizardStep::from((string) $step);
    }

    protected function prepareForValidation(): void
    {
        foreach (['bio', 'work', 'university'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        if ($this->boolean('skip') && $this->step()->isOptional()) {
            return ['skip' => ['accepted']];
        }

        $minimumAge = (int) config('signup.minimum_age');
        $countries = WizardOptions::countries();

        return match ($this->step()) {
            WizardStep::Home => [
                'country' => ['required', 'string', 'max:100'],
                'country_name' => ['required', 'string', 'max:100'],
                'state' => ['required', 'string', 'max:100'],
                'state_name' => ['nullable', 'string', 'max:100'],
                'city' => ['required', 'string', 'max:100'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            ],
            WizardStep::Languages => [
                'languages' => ['required', 'array', 'min:1', 'max:'.count(WizardOptions::LANGUAGES)],
                'languages.*' => ['distinct', Rule::in(WizardOptions::languageValues())],
            ],
            WizardStep::Phone => [],
            WizardStep::Birthday => [
                'birthday' => [
                    'required', 'date_format:Y-m-d', 'after:1900-01-01',
                    'before_or_equal:'.now()->subYears($minimumAge)->toDateString(),
                ],
                'show_age' => ['required', 'boolean'],
            ],
            WizardStep::Interests => [
                'interests' => ['required', 'array', 'min:1'],
                'interests.*' => ['distinct', Rule::in(WizardOptions::INTERESTS)],
            ],
            WizardStep::Gender => [
                'gender' => ['required', Rule::in(array_keys(WizardOptions::GENDERS))],
                'show_gender' => ['required', 'boolean'],
            ],
            WizardStep::Distance => [
                'km' => ['required', 'integer', 'between:'.WizardOptions::DISTANCE_MIN_KM.','.WizardOptions::DISTANCE_ANYWHERE_KM],
                'unit' => ['required', Rule::in(['km', 'mi'])],
            ],
            WizardStep::AgeRange => [
                'min' => ['required', 'integer', 'between:'.WizardOptions::AGE_MIN.','.(WizardOptions::AGE_MAX - 1)],
                'max' => ['required', 'integer', 'gt:min', 'max:'.WizardOptions::AGE_MAX],
            ],
            WizardStep::ShowMe => [
                'show_me' => ['required', Rule::in(array_keys(WizardOptions::SHOW_ME))],
            ],
            WizardStep::Roots => [
                'roots' => ['required', Rule::in($countries)],
            ],
            WizardStep::LinkWith => [
                'countries' => ['required', 'array', 'min:1', 'max:'.count($countries)],
                'countries.*' => ['distinct', Rule::in($countries)],
            ],
            WizardStep::Bio => [
                'bio' => ['required', 'string', 'max:300'],
            ],
            WizardStep::Work => [
                'work' => ['required', 'string', 'max:60'],
            ],
            WizardStep::University => [
                'no_university' => ['required', 'boolean'],
                'university' => ['exclude_if:no_university,true', 'required', 'string', 'min:2', 'max:120'],
                'location' => ['exclude_if:no_university,true', 'nullable', 'string', 'max:120'],
                'country' => ['exclude_if:no_university,true', 'nullable', 'string', 'max:100'],
                'studying' => ['exclude_if:no_university,true', 'boolean'],
            ],
            WizardStep::Intent => [
                'intents' => ['required', 'array', 'min:1'],
                'intents.*' => ['distinct', Rule::in(array_keys(WizardOptions::intentTitles()))],
            ],
            WizardStep::Photos => [],
        };
    }

    public function messages(): array
    {
        return [
            'birthday.before_or_equal' => 'You need to be '.config('signup.minimum_age').' or older to join LinkUp.',
            'birthday.required' => 'Pick the month, day and year you were born.',
            'birthday.date_format' => 'Pick the month, day and year you were born.',
            'city.required' => 'Pick your city, or tap a popular one above.',
            'languages.required' => 'Pick at least one language.',
            'interests.required' => 'Pick at least one interest, or skip for now.',
            'roots.required' => 'Pick the country you are connected to.',
            'roots.in' => 'Pick a country from the list.',
            'countries.required' => 'Pick at least one country, or All countries.',
            'intents.required' => 'Pick at least one reason.',
            'university.required' => 'Pick your university, or tap "I didn\'t go to university".',
            'max.gt' => 'The oldest age must be higher than the youngest.',
        ];
    }
}
