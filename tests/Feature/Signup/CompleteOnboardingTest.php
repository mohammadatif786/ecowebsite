<?php

use App\Actions\WizardUpdateAction;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->withoutVite();
});

/** A member who confirmed their email and finished every wizard step except the final save. */
function readyMember(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'type' => 'user',
        'email_verified_at' => now(),
        'phone_number' => '+18761234567',
        'phone_verified_at' => now(),
        'is_wizard_completed' => false,
    ], $overrides));
    $user->forceFill(['requires_email_verification' => true])->save();

    return $user;
}

function wizardPayload(array $overrides = []): array
{
    return array_merge([
        'country' => 'Jamaica',
        'state' => 'Kingston',
        'city' => 'Kingston',
        'gender' => 'female',
        'birthday' => now()->subYears(25)->toDateString(),
        'interests' => ['Cooking', 'Music'],
        'link_me_with' => 'male',
        'age_filter' => [18, 35],
        'distance_filter' => [0, 40],
        'link_me_with_country_name' => 'all',
        'about_me' => 'Island girl, big heart.',
        'job' => 'Designer',
        'university' => 'University of the West Indies',
        'whyare' => ['love'],
        'phone_number' => '+18761234567',
        'language' => 'English',
        'caribbean_interest' => 'Jamaica',
        'more_photos' => [UploadedFile::fake()->image('one.jpg')],
    ], $overrides);
}

/** Replaces the real profile writer, which needs storage and location services. */
function expectProfileSaved(?array &$saved): void
{
    test()->mock(WizardUpdateAction::class, function ($mock) use (&$saved) {
        $mock->shouldReceive('update')->once()->andReturnUsing(function (array $validated) use (&$saved) {
            $saved = $validated;

            return auth()->user();
        });
    });
}

function expectProfileNotSaved(): void
{
    test()->mock(WizardUpdateAction::class)->shouldNotReceive('update');
}

test('a member with a verified email and phone can finish the wizard', function () {
    $saved = null;
    expectProfileSaved($saved);

    $this->actingAs(readyMember())->post(route('frontend.wizard.update'), wizardPayload())
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($saved['phone_number'])->toBe('+18761234567');
});

test('the saved number is always the verified one, whatever the browser sends', function () {
    $saved = null;
    expectProfileSaved($saved);

    // Same digits written differently is fine, and the stored form wins.
    $this->actingAs(readyMember())
        ->post(route('frontend.wizard.update'), wizardPayload(['phone_number' => '1 876 123 4567']))
        ->assertSessionHasNoErrors();

    expect($saved['phone_number'])->toBe('+18761234567');
});

test('the wizard cannot be finished with an unverified phone', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember(['phone_verified_at' => null]))
        ->post(route('frontend.wizard.update'), wizardPayload())
        ->assertSessionHasErrors('phone_number');
});

test('the wizard cannot be finished with a different number than the one verified', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember())
        ->post(route('frontend.wizard.update'), wizardPayload(['phone_number' => '+13055550123']))
        ->assertSessionHasErrors('phone_number');
});

test('the wizard cannot be finished when no phone was ever added', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember(['phone_number' => null, 'phone_verified_at' => null]))
        ->post(route('frontend.wizard.update'), wizardPayload())
        ->assertSessionHasErrors('phone_number');
});

test('the wizard cannot be finished before the email code is entered', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember(['email_verified_at' => null]))
        ->post(route('frontend.wizard.update'), wizardPayload())
        ->assertRedirect(route('verification.notice'));
});

test('someone under 18 cannot join', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember())
        ->post(route('frontend.wizard.update'), wizardPayload(['birthday' => now()->subYears(17)->toDateString()]))
        ->assertSessionHasErrors('birthday');
});

test('someone who turns 18 today can join', function () {
    $saved = null;
    expectProfileSaved($saved);

    $this->actingAs(readyMember())
        ->post(route('frontend.wizard.update'), wizardPayload(['birthday' => now()->subYears(18)->toDateString()]))
        ->assertSessionHasNoErrors();
});

test('required wizard answers are still required', function () {
    expectProfileNotSaved();

    $this->actingAs(readyMember())
        ->post(route('frontend.wizard.update'), wizardPayload(['city' => '', 'more_photos' => []]))
        ->assertSessionHasErrors(['city', 'more_photos']);
});
