<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Jobs\RegisterWelcomeEmail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RegisteredUserService
{

    public function store(array $validated)
    {
        $user = $this->storeRegisteredUser($validated);

        event(new Registered($user));

        $this->sendWelcomeEmail($user);

        Auth::login($user);
    }

    private function storeRegisteredUser(array $validated)
    {
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'type' => 'user',
            'email' => $validated['email'],
            'linkup_id' => '@' . ltrim(strtolower(trim($validated['linkup_id'])), '@'),
            'password' => Hash::make($validated['password']),
        ]);

        return $user;
    }

    private function sendWelcomeEmail(User $user)
    {
        RegisterWelcomeEmail::dispatch($user)->delay(now()->addMinutes(2));
    }
}
