<?php

use App\Actions\WizardUpdateAction;
use App\DTOs\User\Signup\WizardStep;
use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['latitude' => 25.06, 'longitude' => -77.34]]]),
        'nominatim.openstreetmap.org/*' => Http::response(['address' => [
            'country_code' => 'bs', 'country' => 'The Bahamas', 'state' => 'New Providence', 'city' => 'Nassau',
        ]]),
    ]);
});

/** A member who confirmed their email and verified their phone, wizard not started. */
function wizardMember(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'type' => 'user',
        'first_name' => 'Aaliyah',
        'last_name' => 'Brown',
        'email_verified_at' => now(),
        'phone_number' => '+12425550123',
        'phone_verified_at' => now(),
        'is_wizard_completed' => false,
    ], $overrides));
    $user->forceFill(['requires_email_verification' => true])->save();

    return $user;
}

/** A valid answer for every step (photos excluded). */
function stepAnswers(): array
{
    return [
        'home' => ['country' => 'BS', 'country_name' => 'Bahamas', 'state' => 'NP', 'state_name' => 'New Providence', 'city' => 'Nassau'],
        'languages' => ['languages' => ['English', 'Spanish']],
        'phone' => [],
        'birthday' => ['birthday' => now()->subYears(25)->toDateString(), 'show_age' => true],
        'interests' => ['interests' => ['Travel', 'Liming']],
        'gender' => ['gender' => 'Female', 'show_gender' => true],
        'distance' => ['km' => 40, 'unit' => 'km'],
        'age_range' => ['min' => 25, 'max' => 35],
        'show_me' => ['show_me' => 'Men'],
        'roots' => ['roots' => 'Jamaica'],
        'link_with' => ['countries' => ['Jamaica', 'Haiti']],
        'bio' => ['bio' => 'Island girl, big heart.'],
        'work' => ['work' => 'Nurse'],
        'university' => ['no_university' => false, 'university' => 'University of The Bahamas', 'location' => 'Nassau, Bahamas', 'country' => 'Bahamas', 'studying' => false],
        'intent' => ['intents' => ['friends', 'eats']],
    ];
}

function saveStep(User $user, string $step, ?array $payload = null)
{
    return test()->actingAs($user)->post(route('frontend.wizard.step', ['step' => $step]), $payload ?? stepAnswers()[$step]);
}

/** Answers every step up to (not including) $until. */
function answerStepsUntil(User $user, string $until = 'photos'): void
{
    foreach (stepAnswers() as $step => $payload) {
        if ($step === $until) {
            return;
        }
        saveStep($user, $step)->assertSessionHasNoErrors();
    }
}

function expectProfileSaved(?array &$saved): void
{
    test()->mock(WizardUpdateAction::class, function ($mock) use (&$saved) {
        $mock->shouldReceive('update')->once()->andReturnUsing(function (array $validated) use (&$saved) {
            $saved = $validated;
            $user = auth()->user();
            $user->forceFill(['is_wizard_completed' => true])->save();

            return $user;
        });
    });
}

function expectProfileNotSaved(): void
{
    test()->mock(WizardUpdateAction::class)->shouldNotReceive('update');
}

function finish(User $user, int $photos = 1)
{
    $files = [];
    for ($i = 0; $i < $photos; $i++) {
        $files[] = UploadedFile::fake()->image("p{$i}.jpg");
    }

    return test()->actingAs($user)->post(route('frontend.wizard.update'), ['more_photos' => $files]);
}

/* ------------------------------------------------------------------ page */

test('the wizard opens on the first step with the member, options and saved answers', function () {
    $this->actingAs(wizardMember())->get(route('frontend.users.wizard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('User/Signup/Wizard', false)
            ->where('step', 'home')
            ->where('furthest', 'home')
            ->has('steps', 16)
            ->where('member.firstName', 'Aaliyah')
            ->where('member.phoneVerified', true)
            ->has('options.languages', 8)
            ->has('options.intents', 2)
            ->has('urls.saveStep'));

    expect(resource_path('js/pages/User/Signup/Wizard.vue'))->toBeFile();
});

test('the wizard is behind the email code', function () {
    $this->actingAs(wizardMember(['email_verified_at' => null]))
        ->get(route('frontend.users.wizard'))
        ->assertRedirect(route('verification.notice'));
});

test('the wizard is not shown again once finished', function () {
    $this->actingAs(wizardMember(['is_wizard_completed' => true]))
        ->get(route('frontend.users.wizard'))
        ->assertRedirect(route('user.feed'));
});

/* ------------------------------------------------------------- saving steps */

test('each step is saved on the server and the next one opens', function () {
    $user = wizardMember();

    saveStep($user, 'home')->assertRedirect(route('frontend.users.wizard', ['step' => 'languages']));

    $progress = OnboardingProgress::where('user_id', $user->id)->first();
    expect($progress->current_step)->toBe(1)
        ->and($progress->answers['home']['city'])->toBe('Nassau');
});

test('progress survives leaving: coming back opens the furthest step with the answers', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'birthday');

    $this->actingAs($user)->get(route('frontend.users.wizard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('step', 'birthday')
            ->where('answers.languages.languages', ['English', 'Spanish']));
});

test('an earlier step can be opened and changed without losing later answers', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'gender');

    $this->actingAs($user)->get(route('frontend.users.wizard', ['step' => 'home']))
        ->assertInertia(fn (Assert $page) => $page->where('step', 'home')->where('furthest', 'gender'));

    saveStep($user, 'languages', ['languages' => ['French']])->assertSessionHasNoErrors();

    $progress = OnboardingProgress::where('user_id', $user->id)->first();
    expect($progress->answers['languages']['languages'])->toBe(['French'])
        ->and($progress->current_step)->toBe(WizardStep::Gender->index())
        ->and($progress->answers)->toHaveKey('interests');
});

test('a step cannot be jumped to before the earlier ones are answered', function () {
    $user = wizardMember();

    saveStep($user, 'birthday')->assertSessionHasErrors('step');

    $this->actingAs($user)->get(route('frontend.users.wizard', ['step' => 'photos']))
        ->assertInertia(fn (Assert $page) => $page->where('step', 'home'));
});

test('an unknown step is a 404', function () {
    $this->actingAs(wizardMember())->post('/wizard/steps/salary', [])->assertNotFound();
});

test('the phone step only passes once the number is verified', function () {
    $user = wizardMember(['phone_verified_at' => null]);
    answerStepsUntil($user, 'phone');

    saveStep($user, 'phone', [])->assertSessionHasErrors('phone_number');

    $user->forceFill(['phone_verified_at' => now()])->save();
    saveStep($user, 'phone', [])->assertSessionHasNoErrors();

    expect(OnboardingProgress::where('user_id', $user->id)->first()->answers['phone']['phone_number'])->toBe('+12425550123');
});

test('the phone step can be reached while a phone code is waiting', function () {
    $user = wizardMember(['phone_verified_at' => null]);
    \App\Models\UserOtp::create([
        'user_id' => $user->id, 'otp_hash' => 'x', 'expires_at' => now()->addMinutes(5), 'is_active' => true, 'attempts' => 0,
    ]);

    $this->actingAs($user)->get(route('frontend.users.wizard'))->assertOk();
});

test('the birthday step refuses anyone under 18', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'birthday');

    saveStep($user, 'birthday', ['birthday' => now()->subYears(17)->toDateString(), 'show_age' => true])
        ->assertSessionHasErrors('birthday');

    saveStep($user, 'birthday', ['birthday' => now()->subYears(18)->toDateString(), 'show_age' => false])
        ->assertSessionHasNoErrors();
});

test('answers are checked against the offered choices', function (string $step, array $payload, string $error) {
    $user = wizardMember();
    answerStepsUntil($user, $step);

    saveStep($user, $step, $payload)->assertSessionHasErrors($error);
})->with([
    'unknown language' => ['languages', ['languages' => ['Klingon']], 'languages.0'],
    'no language' => ['languages', ['languages' => []], 'languages'],
    'unknown interest' => ['interests', ['interests' => ['Crime']], 'interests.0'],
    'unknown gender' => ['gender', ['gender' => 'Robot', 'show_gender' => true], 'gender'],
    'distance too far' => ['distance', ['km' => 500, 'unit' => 'km'], 'km'],
    'ages upside down' => ['age_range', ['min' => 40, 'max' => 30], 'max'],
    'unknown show me' => ['show_me', ['show_me' => 'Robots'], 'show_me'],
    'roots outside the list' => ['roots', ['roots' => 'Narnia'], 'roots'],
    'link with nobody' => ['link_with', ['countries' => []], 'countries'],
    'bio too long' => ['bio', ['bio' => str_repeat('a', 301)], 'bio'],
    'work too long' => ['work', ['work' => str_repeat('a', 61)], 'work'],
    'no university picked' => ['university', ['no_university' => false, 'university' => ''], 'university'],
    'unknown intent' => ['intent', ['intents' => ['crime']], 'intents.0'],
]);

test('optional steps can be skipped, required ones cannot', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'interests');

    saveStep($user, 'interests', ['skip' => true])->assertSessionHasNoErrors();
    saveStep($user, 'gender', ['skip' => true])->assertSessionHasErrors();

    expect(OnboardingProgress::where('user_id', $user->id)->first()->answers['interests'])->toBe(['skipped' => true]);
});

test('"I didn\'t go to university" needs no school', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'university');

    saveStep($user, 'university', ['no_university' => true])->assertSessionHasNoErrors();
});

test('photos are not saved as a step, only with Finish', function () {
    $user = wizardMember();
    answerStepsUntil($user);

    saveStep($user, 'photos', [])->assertSessionHasErrors('step');
});

/* -------------------------------------------------------------- locating */

test('use my current location returns the country, region and city', function () {
    $this->actingAs(wizardMember())
        ->postJson(route('frontend.wizard.locate'), ['latitude' => 25.06, 'longitude' => -77.34])
        ->assertOk()
        ->assertJson(['country_code' => 'BS', 'state' => 'New Providence', 'city' => 'Nassau']);
});

test('use my current location says so when nothing is found', function () {
    Http::swap(new \Illuminate\Http\Client\Factory);
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([], 500)]);

    $this->actingAs(wizardMember())
        ->postJson(route('frontend.wizard.locate'), ['latitude' => 0.5, 'longitude' => 0.5])
        ->assertStatus(422);
});

/* ---------------------------------------------------------------- finishing */

test('finishing saves the profile from the server-side answers', function () {
    $user = wizardMember();
    answerStepsUntil($user);

    $saved = null;
    expectProfileSaved($saved);

    finish($user, 2)->assertRedirect(route('frontend.wizard.done'))->assertSessionHasNoErrors();

    expect($saved)->toMatchArray([
        'country' => 'BS',
        'state' => 'NP',
        'city' => 'Nassau',
        'language' => 'English, Spanish',
        'interests' => ['Travel', 'Liming'],
        'gender' => 'female',
        'distance_filter' => [0, 40],
        'age_filter' => [25, 35],
        'link_me_with' => 'male',
        'caribbean_interest' => 'Jamaica',
        'link_me_with_country_name' => 'Jamaica, Haiti',
        'about_me' => 'Island girl, big heart.',
        'job' => 'Nurse',
        'university' => 'University of The Bahamas',
        'university_country' => 'Bahamas',
        'whyare' => 'Make friends, Eats',
        'phone_number' => '+12425550123',
    ]);

    // The home city's position is stored for distance matching.
    expect((float) $user->fresh()->latitude)->toBe(25.06);
});

test('a position from "Use my current location" wins over the city lookup', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'languages');
    saveStep($user, 'home', stepAnswers()['home'] + ['latitude' => 26.5, 'longitude' => -78.7])->assertSessionHasNoErrors();
    foreach (array_slice(stepAnswers(), 1) as $step => $payload) {
        saveStep($user, $step)->assertSessionHasNoErrors();
    }

    $saved = null;
    expectProfileSaved($saved);
    finish($user)->assertSessionHasNoErrors();

    expect((float) $user->fresh()->latitude)->toBe(26.5);
});

test('skipped steps and the widest choices are saved the way the app reads them', function () {
    $user = wizardMember();
    $answers = stepAnswers();
    $answers['distance'] = ['km' => 160, 'unit' => 'mi'];
    $answers['age_range'] = ['min' => 18, 'max' => 80];
    $answers['show_me'] = ['show_me' => 'Everyone'];
    $answers['link_with'] = ['countries' => \App\DTOs\User\Signup\WizardOptions::countries()];
    foreach ($answers as $step => $payload) {
        $payload = in_array($step, ['interests', 'bio', 'work'], true) ? ['skip' => true] : $payload;
        $payload = $step === 'university' ? ['no_university' => true] : $payload;
        saveStep($user, $step, $payload)->assertSessionHasNoErrors();
    }

    $saved = null;
    expectProfileSaved($saved);
    finish($user)->assertSessionHasNoErrors();

    expect($saved)->toMatchArray([
        'interests' => [],
        'about_me' => null,
        'job' => null,
        'university' => 'None',
        'distance_filter' => [0, 20000],
        'age_filter' => [18, 120],
        'link_me_with' => 'both',
        'link_me_with_country_name' => 'all',
    ]);
});

test('finishing needs at least one photo, at most six, and photos only', function () {
    $user = wizardMember();
    answerStepsUntil($user);
    expectProfileNotSaved();

    finish($user, 0)->assertSessionHasErrors('more_photos');
    finish($user, 7)->assertSessionHasErrors('more_photos');
    $this->actingAs($user)->post(route('frontend.wizard.update'), [
        'more_photos' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
    ])->assertSessionHasErrors('more_photos.0');
});

test('the wizard cannot be finished with steps still unanswered', function () {
    $user = wizardMember();
    answerStepsUntil($user, 'bio');
    expectProfileNotSaved();

    finish($user)->assertSessionHasErrors('step');
});

test('the wizard cannot be finished with an unverified phone', function () {
    $user = wizardMember();
    answerStepsUntil($user);
    $user->forceFill(['phone_verified_at' => null])->save();
    expectProfileNotSaved();

    finish($user)->assertSessionHasErrors('phone_number');
});

test('the wizard cannot be finished before the email code is entered', function () {
    $user = wizardMember();
    answerStepsUntil($user);
    $user->forceFill(['email_verified_at' => null])->save();
    expectProfileNotSaved();

    finish($user)->assertRedirect(route('verification.notice'));
});

test('an age saved before the 18th birthday rule cannot slip through at the end', function () {
    $user = wizardMember();
    answerStepsUntil($user);
    $progress = OnboardingProgress::where('user_id', $user->id)->first();
    $answers = $progress->answers;
    $answers['birthday']['birthday'] = now()->subYears(16)->toDateString();
    $progress->update(['answers' => $answers]);
    expectProfileNotSaved();

    finish($user)->assertSessionHasErrors('birthday');
});

/* -------------------------------------------------------------- done screen */

test('the done screen suggests where to start from the reasons picked', function () {
    $user = wizardMember();
    answerStepsUntil($user);
    $saved = null;
    expectProfileSaved($saved);
    finish($user);

    $this->actingAs($user->fresh())->get(route('frontend.wizard.done'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('User/Signup/WizardDone', false)
            ->where('firstName', 'Aaliyah')
            ->has('destinations', 2)
            ->where('destinations.0.title', 'Discover')
            ->where('destinations.1.title', 'Eats'));

    expect(resource_path('js/pages/User/Signup/WizardDone.vue'))->toBeFile();
});

test('the done screen waits until the wizard is finished', function () {
    $this->actingAs(wizardMember())->get(route('frontend.wizard.done'))
        ->assertRedirect(route('frontend.users.wizard'));
});
