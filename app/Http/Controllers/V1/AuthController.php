<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ScanSignUser;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Sign up with email
    public function signUpWithEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return $this->signUpResponse($user);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not created',
            ], 200);
        }
    }

    // Sign up with phone number
    public function signUpWithPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|unique:users,phone_number',
            'password' => 'required',
        ]);

        $user = User::create([
            'phone_number' => $request->phone,
            'email' => null,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return $this->signUpResponse($user);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not created',
            ], 200);
        }
    }

    // Common response
    public function signUpResponse(User $user)
    {
        $input = $user->phone_number ?? $user->email;

        $token = $user->createToken($input)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user_id' => $user->uid,
            'id' => $user->id
        ], 200);
    }


    // for auth user detail
    public function getUserDetail()
    {
        return response()->json([
            'status' => true,
            'data' => Auth::user()
        ], 200);
    }

    // for spefic user detail
    public function getSpecificUserDetail($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    // for login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $loginInput = $request->username;

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        $user = User::where($fieldType, $loginInput)->where('status', true)->first();

        $scanner = ScanSignUser::where('email', $loginInput)->where('status', 1)->first();


        if ($user && Hash::check($request->password, $user->password)) {

            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken($loginInput)->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user_id' => $user->uid,
                    'id' => $user->id,
                    'user_type' => 'organizer',
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ], 200);
            }
        }
        if ($scanner && Hash::check($request->password, $scanner->password)) {
            if ($scanner && Hash::check($request->password, $scanner->password)) {
                $token = $scanner->createToken($loginInput)->plainTextToken;

                if (!$scanner->hasRole('scanner')) {
                    return response()->json([
                        'error' => 'Unauthorized: User does not have scanner role',
                    ], 403);
                }

                $roles = $scanner->roles()->pluck('name');
                $permissions = $scanner->getAllPermissions()->where('guard_name', 'scanner')->pluck('name')->values();

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user_id' => $scanner->id,
                    'id' => $scanner->id,
                    'user_type' => 'scanner',
                    'roles' => $roles,
                    'permissions' => $permissions,
                ], 200);
            }
        }

        return response()->json([
            'error' => 'Invalid credentials',
        ], 401);
    }

    // log out
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    // for update the user data
    public function updateUser(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'front_side' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'back_side' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'country' => 'nullable|string',
            'country_code' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other',
            'birthday' => 'nullable|date',
            'state' => 'nullable|string',
            'new_state' => 'nullable|string',
            'city' => 'nullable|string',
            'new_city' => 'nullable|string',
            'new_country' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'fcmToken' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'phone_number_dial_code' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'about_me' => 'nullable|string',
            'job' => 'nullable|string',
            'kyc_status' => 'nullable|string',
            'language' => 'nullable|string',
            'university' => 'nullable|string',
            'link_me_with' => 'nullable|string',
            'link_me_with_country_code' => 'nullable|string',
            'link_me_with_country_name' => 'nullable|string',
            'link_with_me_phone_code' => 'nullable|string',
            'which_latin_country_you_linked_with' => 'nullable|string',
            'whyare' => 'nullable|string',
            'video' => 'nullable|string',
            'caribbean_interest' => 'nullable|string',
            'age' => 'nullable|integer',
            'birthday' => 'nullable|date',
            'balance' => 'nullable|numeric',
            'distanceinMK' => 'nullable|string',
            'type' => 'nullable|string',

            'age_filter' => 'nullable|array',
            'distance_filter' => 'nullable|array',
            'interests' => 'nullable|array',
            'subscription' => 'nullable|array',
            'more_photos' => 'nullable|array',

            'new_match_notification' => 'nullable|boolean',
            'new_message_notification' => 'nullable|boolean',
            'promotion_notification' => 'nullable|boolean',
            'show_age' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'is_club' => 'nullable|boolean',
            'is_ghost' => 'nullable|boolean',
            'is_live_streaming' => 'nullable|boolean',
            'is_restaurant' => 'nullable|boolean',
            'is_top_shelf' => 'nullable|boolean',
            'kyc_submitted' => 'nullable|boolean',
        ]);

        // Upload and update images if provided
        if (isset($validated['avatar']) && $request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            $validated['avatar'] = $user->avatar;
        }
        // Front Side
        if ($request->hasFile('front_side')) {
            $path = $request->file('front_side')->store('kyc/front', 'public');
            $validated['front_side'] = Storage::url($path);
        }

        // Back Side
        if ($request->hasFile('back_side')) {
            $path = $request->file('back_side')->store('kyc/back', 'public');
            $validated['back_side'] = Storage::url($path);
        }

        // Address Proof
        if ($request->hasFile('address_proof')) {
            $path = $request->file('address_proof')->store('kyc/address', 'public');
            $validated['address_proof'] = Storage::url($path);
        }


        // Mark the wizard completed
        $validated['is_wizard_completed'] = true;

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully.',
            'user' => $user
        ]);
    }


    // for change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        $user = User::find(auth()->user()->id);
        if ($user) {
            $user->password = Hash::make($request->input('password'));
            $user->save();
            return response()->json([
                'status' => true,
                'message' => "Password updated successfully"
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    }


    // for forgot password
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'msg' => "User not found."
            ], 404);
        }

        // Generate a temporary password
        $tempPassword = Str::random(8);

        // Update user's password
        $user->password = Hash::make($tempPassword);
        $user->save();

        // Send email with the temporary password
        $mailData = [
            'name' => $user->name,
            'temp_password' => $tempPassword,
        ];

        $user->notify(new ForgotPassword($mailData));

        return response()->json([
            'status' => 1,
            'msg' => "New password has been sent to your registered email address.",
        ], 200);
    }

    public function deleteAccount()
    {
        $user = Auth::user();

        if ($user) {
            // Delete all access tokens (if using Laravel Sanctum or Passport)
            $user->tokens()->delete();

            // Delete the user record
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully'
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }
}
