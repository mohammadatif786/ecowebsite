<?php

use App\Actions\User\Signup\RegisterMemberAction;
use App\DTOs\User\Signup\RegisterMemberData;
use App\Jobs\RegisterWelcomeEmail;
use App\Mail\Signup\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
    Queue::fake();
});

/** A brand-new member exactly as "Create account" leaves them: signed up, email not confirmed. */
function newMember(string $email = 'aaliyah@example.com'): User
{
    return app(RegisterMemberAction::class)->execute(new RegisterMemberData(
        firstName: 'Aaliyah',
        lastName: 'Brown',
        email: $email,
        linkupId: '@'.str_replace(['@', '.'], '', explode('@', $email)[0]),
        password: 'Zq9!vTr#2026xK',
    ));
}

/** The code that was emailed to the member. */
function emailedCode(string $email = 'aaliyah@example.com'): string
{
    $code = null;
    Mail::assertSent(EmailVerificationCodeMail::class, function ($mail) use ($email, &$code) {
        if ($mail->hasTo($email)) {
            $code = $mail->code;
        }

        return $mail->hasTo($email);
    });

    return $code;
}

function wrongCodeFor(string $right): string
{
    return $right === '000000' ? '111111' : '000000';
}

// ---------------------------------------------------------------------------------------------
// The gate: nothing opens before the code is entered
// ---------------------------------------------------------------------------------------------

test('an unverified member is sent back to the verify email page from any member feature', function () {
    $member = newMember();

    $this->actingAs($member)->get(route('user.feed'))->assertRedirect(route('verification.notice'));
    $this->actingAs($member)->getJson(route('user.feed'))
        ->assertForbidden()
        ->assertJsonPath('redirect', route('verification.notice'));
});

test('an unverified member cannot reach the wizard or its phone code routes', function () {
    $member = newMember();

    $this->actingAs($member)->get(route('frontend.users.wizard'))->assertRedirect(route('verification.notice'));
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.send'), ['phone_number' => '+18761234567'])->assertForbidden();
    $this->actingAs($member)->postJson(route('frontend.wizard.phone.verify'), ['otp' => '123456'])->assertForbidden();
    $this->actingAs($member)->post(route('frontend.wizard.update'), [])->assertRedirect(route('verification.notice'));
});

test('the verify email page shows for an unverified member and bounces everyone else', function () {
    $member = newMember();
    $this->actingAs($member)->get(route('verification.notice'))->assertOk();

    $verified = User::factory()->create(['type' => 'user', 'is_wizard_completed' => true]);
    $this->actingAs($verified)->get(route('verification.notice'))->assertRedirect();
});

test('members who signed up before the gate existed are not held', function () {
    // Same shape as an existing member: email never verified, but the gate flag was never set.
    $legacy = User::factory()->create(['type' => 'user', 'email_verified_at' => null, 'is_wizard_completed' => true]);

    $state = app(\App\Actions\User\Signup\ResolveOnboardingStateAction::class)->execute($legacy);

    expect($state->isComplete())->toBeTrue();
});

// ---------------------------------------------------------------------------------------------
// The code
// ---------------------------------------------------------------------------------------------

test('the right code verifies the email and moves the member on to the wizard', function () {
    $member = newMember();
    $code = emailedCode();

    $this->actingAs($member)->post(route('verification.code'), ['code' => $code])
        ->assertRedirect(route('frontend.users.wizard'));

    expect($member->fresh()->email_verified_at)->not->toBeNull()
        ->and(EmailVerificationCode::count())->toBe(0);

    Queue::assertPushed(RegisterWelcomeEmail::class);
    $this->actingAs($member->fresh())->get(route('frontend.users.wizard'))->assertOk();
});

test('a code with spaces or a dash still works', function () {
    $member = newMember();
    $code = emailedCode();
    $pasted = substr($code, 0, 3).' - '.substr($code, 3);

    $this->actingAs($member)->post(route('verification.code'), ['code' => $pasted])
        ->assertRedirect(route('frontend.users.wizard'));
});

test('a wrong code is refused and counted', function () {
    $member = newMember();
    $code = emailedCode();

    $this->actingAs($member)->from(route('verification.notice'))
        ->post(route('verification.code'), ['code' => wrongCodeFor($code)])
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHasErrors('code');

    expect($member->fresh()->email_verified_at)->toBeNull()
        ->and(EmailVerificationCode::first()->attempts)->toBe(1);
});

test('three wrong codes lock the code, even the right one is then refused until a new code is sent', function () {
    $member = newMember();
    $code = emailedCode();

    foreach (range(1, 3) as $i) {
        $this->actingAs($member)->post(route('verification.code'), ['code' => wrongCodeFor($code)]);
    }

    $this->actingAs($member)->post(route('verification.code'), ['code' => $code])->assertSessionHasErrors('code');

    expect($member->fresh()->email_verified_at)->toBeNull();
});

test('an expired code is refused', function () {
    $member = newMember();
    $code = emailedCode();

    $this->travel(11)->minutes();

    $this->actingAs($member)->post(route('verification.code'), ['code' => $code])->assertSessionHasErrors('code');
    expect($member->fresh()->email_verified_at)->toBeNull();
});

test('a member cannot verify with somebody elses code', function () {
    $alice = newMember('alice@example.com');
    $aliceCode = emailedCode('alice@example.com');
    $bob = newMember('bob@example.com');

    $this->actingAs($bob)->post(route('verification.code'), ['code' => $aliceCode])->assertSessionHasErrors('code');

    expect($bob->fresh()->email_verified_at)->toBeNull()
        ->and($alice->fresh()->email_verified_at)->toBeNull();
});

test('a code needs exactly 6 digits', function () {
    $member = newMember();

    $this->actingAs($member)->post(route('verification.code'), ['code' => '12345'])->assertSessionHasErrors('code');
    $this->actingAs($member)->post(route('verification.code'), ['code' => ''])->assertSessionHasErrors('code');
    $this->actingAs($member)->post(route('verification.code'), ['code' => 'abcdef'])->assertSessionHasErrors('code');
});

test('the code can be posted only by the signed-in member', function () {
    $this->post(route('verification.code'), ['code' => '123456'])->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------------------------
// Resend and change email
// ---------------------------------------------------------------------------------------------

test('resend replaces the old code, after a short wait', function () {
    $member = newMember();
    $first = emailedCode();

    $this->actingAs($member)->post(route('verification.send'))->assertSessionHasErrors('code');

    $this->travel(61)->seconds();
    $this->actingAs($member)->post(route('verification.send'))->assertSessionHasNoErrors();

    Mail::assertSent(EmailVerificationCodeMail::class, 2);
    expect(EmailVerificationCode::count())->toBe(1);
});

test('no more than 5 codes can be requested in an hour', function () {
    $member = newMember(); // send 1

    foreach (range(1, 4) as $i) {
        $this->travel(61)->seconds();
        $this->actingAs($member)->post(route('verification.send'))->assertSessionHasNoErrors();
    }

    $this->travel(61)->seconds();
    $this->actingAs($member)->post(route('verification.send'))->assertSessionHasErrors('code');
});

test('change email moves the code to the new address and kills the old one', function () {
    $member = newMember('typo@example.com');
    $oldCode = emailedCode('typo@example.com');

    $this->actingAs($member)->post(route('verification.change'), ['email' => 'Right@Example.com'])
        ->assertSessionHasNoErrors();

    expect($member->fresh()->email)->toBe('right@example.com');
    Mail::assertSent(EmailVerificationCodeMail::class, fn ($mail) => $mail->hasTo('right@example.com'));

    // The code that went to the typo address no longer works.
    $this->actingAs($member->fresh())->post(route('verification.code'), ['code' => $oldCode])->assertSessionHasErrors('code');
    expect($member->fresh()->email_verified_at)->toBeNull();
});

test('change email refuses an address that is already registered', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $member = newMember();

    $this->actingAs($member)->post(route('verification.change'), ['email' => 'taken@example.com'])
        ->assertSessionHasErrors('email');

    expect($member->fresh()->email)->toBe('aaliyah@example.com');
});

test('a verified member cannot change their email through this page', function () {
    $member = newMember();
    $this->actingAs($member)->post(route('verification.code'), ['code' => emailedCode()]);

    $this->actingAs($member->fresh())->post(route('verification.change'), ['email' => 'other@example.com'])
        ->assertSessionHasErrors('email');

    expect($member->fresh()->email)->toBe('aaliyah@example.com');
});

test('a failed email send leaves no usable code behind', function () {
    $member = newMember();
    RateLimiter::clear('signup-email-code:'.$member->id);
    $this->travel(61)->seconds();

    Mail::shouldReceive('to')->andThrow(new RuntimeException('smtp down'));

    $this->actingAs($member)->post(route('verification.send'))->assertSessionHasErrors('code');
    expect(EmailVerificationCode::count())->toBe(0);
});
