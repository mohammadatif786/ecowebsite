<?php

use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Hash;

/** A member who finished the email step and is now in the wizard. */
function memberInWizard(array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'type' => 'user',
        'email_verified_at' => now(),
        'is_wizard_completed' => false,
    ], $overrides));
    $user->forceFill(['requires_email_verification' => true])->save();

    return $user;
}

/** Captures the text the member would have received. */
function fakeSms(): object
{
    $sent = new stdClass;
    $sent->to = null;
    $sent->body = null;

    test()->mock(TwilioService::class, function ($mock) use ($sent) {
        $mock->shouldReceive('sendSMS')->andReturnUsing(function ($to, $body) use ($sent) {
            $sent->to = $to;
            $sent->body = $body;
        });
    });

    return $sent;
}

function smsCode(object $sent): string
{
    preg_match('/(\d{6})/', (string) $sent->body, $m);

    return $m[1];
}

test('sending a code needs a signed-in member', function () {
    $this->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])->assertUnauthorized();
});

test('the number is normalised, saved unverified and texted a 6-digit code', function () {
    $sent = fakeSms();
    $member = memberInWizard();

    $this->actingAs($member)
        ->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '1 (876) 123-4567'])
        ->assertOk()
        ->assertJsonPath('sent', true);

    expect($member->fresh()->phone_number)->toBe('+18761234567')
        ->and($member->fresh()->phone_verified_at)->toBeNull()
        ->and($sent->to)->toBe('+18761234567')
        ->and(smsCode($sent))->toMatch('/^\d{6}$/')
        ->and(UserOtp::where('user_id', $member->id)->count())->toBe(1);
});

test('the right code verifies the phone', function () {
    $sent = fakeSms();
    $member = memberInWizard();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567']);

    $this->actingAs($member)
        ->postJson(route('frontend.wizard.phone.verify'), ['otp' => smsCode($sent)])
        ->assertOk()
        ->assertJsonPath('verified', true);

    expect($member->fresh()->phone_verified_at)->not->toBeNull()
        ->and(UserOtp::where('user_id', $member->id)->count())->toBe(0);
});

test('a wrong code is refused and three wrong codes kill the code', function () {
    $sent = fakeSms();
    $member = memberInWizard();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567']);
    $right = smsCode($sent);
    $wrong = $right === '000000' ? '111111' : '000000';

    foreach (range(1, 3) as $i) {
        $this->actingAs($member)->postJson(route('frontend.wizard.phone.verify'), ['otp' => $wrong])
            ->assertUnprocessable()->assertJsonValidationErrors('otp');
    }

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.verify'), ['otp' => $right])
        ->assertUnprocessable()->assertJsonValidationErrors('otp');

    expect($member->fresh()->phone_verified_at)->toBeNull();
});

test('an expired code is refused', function () {
    $sent = fakeSms();
    $member = memberInWizard();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567']);

    $this->travel(6)->minutes();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.verify'), ['otp' => smsCode($sent)])
        ->assertUnprocessable()->assertJsonValidationErrors('otp');
    expect($member->fresh()->phone_verified_at)->toBeNull();
});

test('verifying before a code was ever sent is refused', function () {
    $member = memberInWizard();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.verify'), ['otp' => '123456'])
        ->assertUnprocessable()->assertJsonValidationErrors('otp');
});

test('a number used by another member is refused', function () {
    fakeSms();
    User::factory()->create(['phone_number' => '+18761234567']);
    $member = memberInWizard();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])
        ->assertUnprocessable()->assertJsonValidationErrors('phone_number');

    expect($member->fresh()->phone_number)->toBeNull();
});

test('a number that is not a phone number is refused', function () {
    fakeSms();
    $member = memberInWizard();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+0123456789'])
        ->assertUnprocessable()->assertJsonValidationErrors('phone_number');
});

test('changing the number drops the verification and needs a new code', function () {
    $sent = fakeSms();
    $member = memberInWizard(['phone_number' => '+18761234567', 'phone_verified_at' => now()]);

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+13055550123'])
        ->assertOk()->assertJsonPath('sent', true);

    expect($member->fresh()->phone_number)->toBe('+13055550123')
        ->and($member->fresh()->phone_verified_at)->toBeNull()
        ->and($sent->to)->toBe('+13055550123');
});

test('sending to a number that is already verified does not text again', function () {
    $this->mock(TwilioService::class)->shouldNotReceive('sendSMS');
    $member = memberInWizard(['phone_number' => '+18761234567', 'phone_verified_at' => now()]);

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])
        ->assertOk()->assertJsonPath('verified', true);
});

test('resending to the same number waits a little', function () {
    fakeSms();
    $member = memberInWizard();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])->assertOk();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])
        ->assertUnprocessable()->assertJsonValidationErrors('phone_number');

    $this->travel(31)->seconds();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])->assertOk();
});

test('a failed text leaves no usable code', function () {
    $this->mock(TwilioService::class, fn ($m) => $m->shouldReceive('sendSMS')->andThrow(new RuntimeException('twilio down')));
    $member = memberInWizard();

    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])
        ->assertUnprocessable()->assertJsonValidationErrors('phone_number');

    expect(UserOtp::where('user_id', $member->id)->count())->toBe(0);
});

test('codes are stored hashed', function () {
    $sent = fakeSms();
    $member = memberInWizard();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567']);

    $record = UserOtp::where('user_id', $member->id)->firstOrFail();

    expect($record->otp_hash)->not->toBe(smsCode($sent))
        ->and(Hash::check(smsCode($sent), $record->otp_hash))->toBeTrue();
});
