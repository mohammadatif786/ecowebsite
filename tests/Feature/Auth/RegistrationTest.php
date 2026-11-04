<?php

use App\Mail\Signup\EmailVerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Mail::fake();
    // The password rule asks the Have I Been Pwned range API; keep tests offline. Empty body = "not leaked".
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);
});

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Aaliyah',
        'last_name' => 'Brown',
        'email' => 'aaliyah@example.com',
        'linkup_id' => 'aaliyahbrown',
        'password' => 'Zq9!vTr#2026xK',
        'password_confirmation' => 'Zq9!vTr#2026xK',
        'terms' => true,
    ], $overrides);
}

test('registration screen opens the Create account tab', function () {
    $this->get('/register')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('User/Signup/Account', false)
        ->where('mode', 'create')
        ->where('urls.checkLinkupId', route('check.linkup.id'))
        ->where('urls.forgotPassword', route('password.request'))
        ->has('urls.terms')->has('urls.privacy')->has('urls.acceptableUse'));
});

test('every page the account flow renders has a Vue file', function () {
    // Checked by hand because Inertia's own check looks in js/Pages and the app uses js/pages.
    foreach (['Signup/Account', 'Signup/VerifyEmail', 'Signup/ForgotPassword', 'Signup/ResetPassword', 'Signup/Wizard', 'Signup/WizardDone'] as $page) {
        expect(resource_path("js/pages/User/{$page}.vue"))->toBeFile();
    }
});

test('the sign in screen is the same page on the Sign in tab', function () {
    $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('User/Signup/Account', false)
        ->where('mode', 'signin'));
});

test('create account signs the member in and holds them on the verify email page', function () {
    $this->post('/register', registrationPayload())->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();

    $member = User::where('email', 'aaliyah@example.com')->firstOrFail();
    expect($member->linkup_id)->toBe('@aaliyahbrown')
        ->and($member->type)->toBe('user')
        ->and($member->email_verified_at)->toBeNull()
        ->and((bool) $member->requires_email_verification)->toBeTrue();
});

test('create account emails a 6-digit code and stores only its hash', function () {
    $this->post('/register', registrationPayload());

    Mail::assertSent(EmailVerificationCodeMail::class, function (EmailVerificationCodeMail $mail) {
        return $mail->hasTo('aaliyah@example.com') && preg_match('/^\d{6}$/', $mail->code) === 1;
    });

    $record = EmailVerificationCode::firstOrFail();
    expect($record->code_hash)->toHaveLength(64)
        ->and($record->attempts)->toBe(0);
});

test('create account is refused for a weak password, a taken email and a taken LinkUp ID', function () {
    User::factory()->create(['email' => 'taken@example.com', 'linkup_id' => '@takenid']);

    $this->post('/register', registrationPayload(['password' => 'password', 'password_confirmation' => 'password']))
        ->assertSessionHasErrors('password');

    $this->post('/register', registrationPayload(['email' => 'taken@example.com']))
        ->assertSessionHasErrors('email');

    $this->post('/register', registrationPayload(['linkup_id' => 'takenid']))
        ->assertSessionHasErrors('linkup_id');

    $this->assertGuest();
});

test('a signed-in member cannot open the register page again', function () {
    $this->actingAs(User::factory()->create(['type' => 'user']))->get('/register')->assertRedirect();
});

test('create account needs the terms to be agreed', function () {
    $this->post('/register', registrationPayload(['terms' => false]))->assertSessionHasErrors('terms');
    $this->post('/register', registrationPayload(['terms' => null]))->assertSessionHasErrors('terms');

    $this->assertGuest();
    expect(User::where('email', 'aaliyah@example.com')->exists())->toBeFalse();
});

test('a reserved LinkUp ID is refused even when the page is skipped', function () {
    foreach (['admin', '@Support', 'wallet'] as $reserved) {
        $this->post('/register', registrationPayload(['linkup_id' => $reserved]))->assertSessionHasErrors('linkup_id');
    }

    $this->assertGuest();
});

test('a LinkUp ID with dots, spaces or too few characters is refused', function () {
    foreach (['aaliyah.brown', 'aaliyah brown', 'ab', str_repeat('a', 21)] as $bad) {
        $this->post('/register', registrationPayload(['linkup_id' => $bad]))->assertSessionHasErrors('linkup_id');
    }
});

// ---------------------------------------------------------------------------------------------
// Live LinkUp ID check
// ---------------------------------------------------------------------------------------------

test('the LinkUp ID check says a free ID is available', function () {
    $this->postJson(route('check.linkup.id'), ['linkup_id' => 'aaliyahbrown'])
        ->assertOk()
        ->assertExactJson(['available' => true, 'reason' => null]);
});

test('the LinkUp ID check says a taken ID is taken, with or without the @', function () {
    User::factory()->create(['linkup_id' => '@takenid']);

    $this->postJson(route('check.linkup.id'), ['linkup_id' => 'takenid'])
        ->assertOk()->assertJsonPath('available', false);
    $this->postJson(route('check.linkup.id'), ['linkup_id' => '@TakenID'])
        ->assertOk()->assertJsonPath('available', false);
});

test('the LinkUp ID check blocks reserved words, doubled underscores and repeated characters', function () {
    foreach (['admin', 'support', 'wallet', 'a__b', 'aaaab'] as $id) {
        $this->postJson(route('check.linkup.id'), ['linkup_id' => $id])
            ->assertOk()->assertJsonPath('available', false);
    }
});

test('the LinkUp ID check refuses a malformed ID', function () {
    foreach (['ab', 'has.dot', 'has space', str_repeat('a', 21)] as $bad) {
        $this->postJson(route('check.linkup.id'), ['linkup_id' => $bad])->assertUnprocessable()->assertJsonValidationErrors('linkup_id');
    }
});

test('the LinkUp ID check is for people who are not signed in yet', function () {
    $this->actingAs(User::factory()->create(['type' => 'user']))
        ->postJson(route('check.linkup.id'), ['linkup_id' => 'aaliyahbrown'])
        ->assertRedirect();
});

test('checking LinkUp IDs does not use up the Create account limit', function () {
    // Laravel's plain throttles share one counter per IP unless each route has its own prefix.
    for ($i = 0; $i < 12; $i++) {
        $this->postJson(route('check.linkup.id'), ['linkup_id' => 'someone'.$i])->assertOk();
    }

    $this->post('/register', registrationPayload())->assertSessionHasNoErrors()->assertRedirect();
});
