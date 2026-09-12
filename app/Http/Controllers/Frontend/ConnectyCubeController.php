<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ConnectyCubeController extends Controller
{
    public function index()
    {

        $appId = env('CONNECTYCUBE_APP_ID');
        $authKey = env('CONNECTYCUBE_AUTH_KEY');
        $authSecret = env('CONNECTYCUBE_AUTH_SECRET');

        // 1. Create application session
        $nonce = random_int(1000, 9999);
        $timestamp = time();
        $signature = hash_hmac('sha1', "application_id={$appId}&auth_key={$authKey}&nonce={$nonce}&timestamp={$timestamp}", $authSecret);

        $sessionResp = Http::post('https://api.connectycube.com/session.json', [
            'application_id' => $appId,
            'auth_key' => $authKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature
        ]);

        if (!$sessionResp->successful()) {
            return $sessionResp->body();
        }

        $token = $sessionResp->json('session.token');

        // 2. Create user
        $userResp = Http::withHeaders(['CB-Token' => $token])->post('https://api.connectycube.com/users.json', [
            'user' => [
                'login' => 'user2',
                'password' => 'password2',
                'full_name' => 'user2',
            ]
        ]);

        if ($userResp->successful()) {
            Log::info('User created: ' . json_encode($userResp->json()['user']));
        } else {
            Log::error('User create failed: ' . $userResp->body());
        }

        return 'Success';
    }

    public function videoCall()
    {
        return Inertia::render('User/VideoCall');
    }
}
