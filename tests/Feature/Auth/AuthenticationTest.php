<?php

use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;

beforeEach(function () {
    $this->withoutVite();
});

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('a finished member signs in with their email and lands in the app', function () {
    $user = User::factory()->create([
        'type' => 'user',
        'phone_verified_at' => now(),
        'is_wizard_completed' => true,
    ]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('user.feed', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('a finished member signs in with their LinkUp ID', function () {
    $user = User::factory()->create([
        'type' => 'user',
        'linkup_id' => '@aaliyahbrown',
        'phone_verified_at' => now(),
        'is_wizard_completed' => true,
    ]);

    $this->post('/login', ['email' => 'AaliyahBrown', 'password' => 'password'])
        ->assertRedirect(route('user.feed', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('signing in with an unconfirmed email goes to the verify email page and sends no text', function () {
    $this->mock(TwilioService::class)->shouldNotReceive('sendSMS');

    $user = User::factory()->create([
        'type' => 'user',
        'email_verified_at' => null,
        'is_wizard_completed' => false,
    ]);
    $user->forceFill(['requires_email_verification' => true])->save();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('verification.notice'));

    $this->assertAuthenticatedAs($user);
    $this->get(route('user.feed'))->assertRedirect(route('verification.notice'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create(['type' => 'user']);

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create(['type' => 'user']);

    $this->actingAs($user)->post('/logout')->assertRedirect('/register');

    $this->assertGuest();
});

test('an existing member whose phone is not verified is signed out and sent to the phone code screen', function () {
    $sent = new stdClass;
    $this->mock(TwilioService::class, function ($mock) use ($sent) {
        $mock->shouldReceive('sendSMS')->once()->andReturnUsing(function ($to, $body) use ($sent) {
            $sent->to = $to;
            $sent->body = $body;
        });
    });

    $user = User::factory()->create([
        'type' => 'user',
        'phone_number' => '+18761234567',
        'phone_verified_at' => null,
        'is_wizard_completed' => true,
    ]);

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertRedirectContains('/verify-otp/');
    $this->assertGuest();
    expect(UserOtp::where('user_id', $user->id)->where('is_active', true)->count())->toBe(1)
        ->and($sent->to)->toBe('+18761234567')
        ->and($sent->body)->toMatch('/\d{6}/');
});

test('the phone code screen is still reachable without a phone number on file', function () {
    $this->mock(TwilioService::class)->shouldNotReceive('sendSMS');

    $user = User::factory()->create(['type' => 'user', 'phone_number' => null, 'phone_verified_at' => null, 'is_wizard_completed' => true]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirectContains('/verify-otp/');
    $this->assertGuest();
});

test('a wrong password sends no code and shows the error on the email field', function () {
    $this->mock(TwilioService::class)->shouldNotReceive('sendSMS');
    $user = User::factory()->create(['type' => 'user', 'phone_verified_at' => null]);

    $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'nope'])
        ->assertRedirect('/login')->assertSessionHasErrors('email');

    expect(UserOtp::count())->toBe(0);
});

test('sign in asks for both fields', function () {
    $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
});
