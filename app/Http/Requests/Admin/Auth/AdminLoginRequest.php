<?php

namespace App\Http\Requests\Admin\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    private const DUMMY_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2/QqAlK5H6TkhFbVwW';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->normalizedEmail();
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        $passwordMatches = Hash::check((string) $this->input('password'), $user?->password ?? self::DUMMY_PASSWORD_HASH);

        if (! $passwordMatches || ! $this->isAllowedAdmin($user)) {
            $this->recordFailedAttempt($user);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::guard('web')->login($user, $this->boolean('remember'));

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        foreach ($this->throttleKeys() as $key => $limit) {
            RateLimiter::clear($key);
        }

        Log::info('Admin login succeeded', $this->logContext($user, 'success'));
    }

    private function ensureIsNotRateLimited(): void
    {
        foreach ($this->throttleKeys() as $key => $limit) {
            if (! RateLimiter::tooManyAttempts($key, $limit)) {
                continue;
            }

            event(new Lockout($this));

            $seconds = RateLimiter::availableIn($key);

            Log::warning('Admin login locked out', $this->logContext(null, 'lockout') + [
                'available_in_seconds' => $seconds,
                'limit_key_type' => $this->keyType($key),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }

    private function recordFailedAttempt(?User $user): void
    {
        foreach ($this->throttleKeys() as $key => $limit) {
            RateLimiter::hit($key, $this->decaySecondsForKey($key));
        }

        $attempts = max(array_map(
            fn (string $key): int => RateLimiter::attempts($key),
            array_keys($this->throttleKeys())
        ));

        $context = $this->logContext($user, $this->failureReason($user)) + [
            'attempts' => $attempts,
        ];

        Log::warning('Admin login failed', $context);

        if ($attempts >= (int) config('admin_security.login.suspicious_attempts', 10)) {
            Log::alert('Suspicious admin login behavior detected', $context);
        }
    }

    /**
     * @return array<string, int>
     */
    private function throttleKeys(): array
    {
        $email = $this->normalizedEmail();
        $ip = (string) $this->ip();

        return [
            "admin-login:{$email}|{$ip}" => (int) config('admin_security.login.max_attempts', 5),
            "admin-login-identity:{$email}" => (int) config('admin_security.login.identity_max_attempts', 10),
            "admin-login-ip:{$ip}" => (int) config('admin_security.login.ip_max_attempts', 20),
        ];
    }

    private function decaySecondsForKey(string $key): int
    {
        if (str_starts_with($key, 'admin-login-identity:')) {
            return (int) config('admin_security.login.identity_decay_seconds', 3600);
        }

        if (str_starts_with($key, 'admin-login-ip:')) {
            return (int) config('admin_security.login.ip_decay_seconds', 900);
        }

        return (int) config('admin_security.login.decay_seconds', 900);
    }

    private function normalizedEmail(): string
    {
        return Str::lower(Str::transliterate(trim((string) $this->input('email'))));
    }

    private function isAllowedAdmin(?User $user): bool
    {
        if (! $user || $user->type !== 'admin' || $user->status === false) {
            return false;
        }

        return method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    private function failureReason(?User $user): string
    {
        if (! $user) {
            return 'invalid_credentials';
        }

        if ($user->type !== 'admin' || ! $user->hasRole('admin')) {
            return 'not_admin';
        }

        if ($user->status === false) {
            return 'inactive_admin';
        }

        return 'invalid_credentials';
    }

    /**
     * @return array<string, mixed>
     */
    private function logContext(?User $user, string $outcome): array
    {
        return [
            'outcome' => $outcome,
            'user_id' => $user?->id,
            'email_hash' => hash_hmac('sha256', $this->normalizedEmail(), (string) config('app.key')),
            'ip' => $this->ip(),
            'user_agent' => Str::limit((string) $this->userAgent(), 255, ''),
        ];
    }

    private function keyType(string $key): string
    {
        return Str::between($key, 'admin-login', ':') ?: 'combined';
    }
}
