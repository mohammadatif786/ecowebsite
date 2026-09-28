<?php

namespace App\Http\Controllers\NewFrontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileSecurityController extends Controller
{
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateTransactionPin(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'pin' => ['required', 'string', 'digits:4'],
            'pin_confirmation' => ['required', 'same:pin'],
        ];

        if (! empty($user->transaction_pin)) {
            $rules['current_pin'] = [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->transaction_pin)) {
                        $fail('The provided current PIN is incorrect.');
                    }
                },
            ];
        }

        $validated = $request->validate($rules);

        $user->forceFill([
            'transaction_pin' => Hash::make($validated['pin']),
        ])->save();

        return back()->with('success', 'Transaction PIN updated successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $user->forceFill([
                'is_active' => 0,
                'last_active' => now(),
            ])->save();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/register');
    }
}
