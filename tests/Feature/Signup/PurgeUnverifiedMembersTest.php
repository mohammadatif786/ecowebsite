<?php

use App\Models\EmailVerificationCode;
use App\Models\User;

function unverifiedMember(string $createdAgo, array $overrides = []): User
{
    $user = User::factory()->create(array_merge([
        'type' => 'user',
        'email_verified_at' => null,
        'created_at' => now()->sub($createdAgo),
    ], $overrides));
    $user->forceFill(['requires_email_verification' => true])->save();

    return $user;
}

test('accounts that never confirmed their email are removed after 7 days', function () {
    $old = unverifiedMember('8 days');
    EmailVerificationCode::create([
        'user_id' => $old->id,
        'email' => $old->email,
        'code_hash' => str_repeat('a', 64),
        'attempts' => 0,
        'expires_at' => now()->subDays(7),
        'last_sent_at' => now()->subDays(8),
    ]);

    $this->artisan('signup:purge-unverified')->assertSuccessful();

    expect(User::find($old->id))->toBeNull()
        ->and(EmailVerificationCode::count())->toBe(0);
});

test('recent, verified and existing members are never removed', function () {
    $recent = unverifiedMember('2 days');
    $verified = unverifiedMember('30 days', ['email_verified_at' => now()->subDays(29)]);
    $legacy = User::factory()->create(['type' => 'user', 'email_verified_at' => null, 'created_at' => now()->subYears(2)]);
    $organizer = User::factory()->create(['type' => 'organizer', 'email_verified_at' => null, 'created_at' => now()->subYears(2)]);

    $this->artisan('signup:purge-unverified')->assertSuccessful();

    expect(User::whereKey([$recent->id, $verified->id, $legacy->id, $organizer->id])->count())->toBe(4);
});

test('a dry run only counts', function () {
    $old = unverifiedMember('9 days');

    $this->artisan('signup:purge-unverified --dry-run')
        ->expectsOutputToContain('Would remove 1')
        ->assertSuccessful();

    expect(User::find($old->id))->not->toBeNull();
});
