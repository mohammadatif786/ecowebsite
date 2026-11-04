<?php

use App\Mail\Signup\PasswordResetLinkMail;
use App\Models\User;
use App\Notifications\User\Signup\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]); // the "not a leaked password" check
});

function resetMember(array $overrides = []): User
{
    return User::factory()->create(array_merge(['email' => 'aaliyah@example.com', 'first_name' => 'Aaliyah', 'type' => 'user'], $overrides));
}

test('the forgot password page opens from the sign in tab', function () {
    $this->get(route('login'))->assertInertia(fn (Assert $page) => $page->where('urls.forgotPassword', route('password.request')));

    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('User/Signup/ForgotPassword', false)->where('expireMinutes', 60)->has('urls.send'));

    expect(resource_path('js/pages/User/Signup/ForgotPassword.vue'))->toBeFile();
});

test('a reset link is emailed to the account', function () {
    Notification::fake();
    $user = resetMember();

    $this->post(route('password.email'), ['email' => ' Aaliyah@Example.com '])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('sentTo', 'aaliyah@example.com');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('the email is LinkUp styled and links to the reset page', function () {
    $user = resetMember();
    $mail = (new ResetPasswordNotification('tok123'))->toMail($user);

    expect($mail)->toBeInstanceOf(PasswordResetLinkMail::class)
        ->and($mail->url)->toBe(route('password.reset', ['token' => 'tok123', 'email' => 'aaliyah@example.com']))
        ->and($mail->expireMinutes)->toBe(60);

    $mail->assertHasSubject('Reset your LinkUp password');
    $mail->assertSeeInHtml('Choose a new password');
    $mail->assertSeeInHtml('Hi Aaliyah');
});

test('the page gives the same answer for an email with no account', function () {
    Notification::fake();
    resetMember();

    $known = $this->post(route('password.email'), ['email' => 'aaliyah@example.com'])->assertSessionHasNoErrors();
    $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.com'])->assertSessionHasNoErrors();

    expect(session('status'))->toBe($known->getSession()->get('status'));
    Notification::assertCount(1);
});

test('asking again within a minute sends nothing new but still answers the same', function () {
    Notification::fake();
    resetMember();

    $this->post(route('password.email'), ['email' => 'aaliyah@example.com'])->assertSessionHasNoErrors();
    $this->post(route('password.email'), ['email' => 'aaliyah@example.com'])->assertSessionHasNoErrors()->assertSessionHas('status');

    Notification::assertCount(1);
});

test('forgot password needs a valid email', function () {
    $this->post(route('password.email'), ['email' => 'not-an-email'])->assertSessionHasErrors('email');
});

test('the reset page opens from the link', function () {
    $this->get(route('password.reset', ['token' => 'tok', 'email' => 'aaliyah@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('User/Signup/ResetPassword', false)
            ->where('email', 'aaliyah@example.com')
            ->where('token', 'tok')
            ->has('urls.save'));

    expect(resource_path('js/pages/User/Signup/ResetPassword.vue'))->toBeFile();
});

test('the password can be reset with the emailed link, once', function () {
    $user = resetMember(['email_verified_at' => null]);
    $token = Password::broker()->createToken($user);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'aaliyah@example.com',
        'password' => 'NewPass#2026',
        'password_confirmation' => 'NewPass#2026',
    ])->assertSessionHasNoErrors()->assertRedirect(route('login'))->assertSessionHas('status');

    $user->refresh();
    expect(Hash::check('NewPass#2026', $user->password))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull(); // the link proved the inbox

    // The same link can't be used twice.
    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'aaliyah@example.com',
        'password' => 'Other#Pass2026',
        'password_confirmation' => 'Other#Pass2026',
    ])->assertSessionHasErrors('token');
});

test('a wrong or expired link is refused', function () {
    resetMember();

    $this->post(route('password.store'), [
        'token' => 'wrong',
        'email' => 'aaliyah@example.com',
        'password' => 'NewPass#2026',
        'password_confirmation' => 'NewPass#2026',
    ])->assertSessionHasErrors('token');
});

test('the new password follows the sign-up rules', function () {
    $user = resetMember();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.store'), [
        'token' => $token, 'email' => 'aaliyah@example.com', 'password' => 'weak', 'password_confirmation' => 'weak',
    ])->assertSessionHasErrors('password');

    $this->post(route('password.store'), [
        'token' => $token, 'email' => 'aaliyah@example.com', 'password' => 'NewPass#2026', 'password_confirmation' => 'Different#2026',
    ])->assertSessionHasErrors('password');
});

test('signed-in members are sent away from the reset pages', function () {
    $this->actingAs(resetMember())->get(route('password.request'))->assertRedirect();
});
