<?php

namespace App\Http\Controllers;

use App\Domain\Linkup\Services\LinkupAccessService;
use App\Http\Requests\Linkup\AgoraTokenRequest;
use App\Models\User;
use App\Helpers\AgoraToken;

class AgoraController extends Controller
{
    public function generate(AgoraTokenRequest $request, LinkupAccessService $access)
    {
        $recipient = User::findOrFail($request->validated('recipient_id'));
        abort_unless($access->canChat($request->user(), $recipient), 403);
        [$first, $second] = collect([$request->user()->id, $recipient->id])->sort()->values();
        $channelName = "video_chat_{$first}_{$second}";

        $uid = rand(1, 99999);
        $expireTime = 3600; // 1 hour

        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        try {
            $token = AgoraToken::buildToken(
                $appID,
                $appCertificate,
                $channelName,
                $uid,
                $expireTime
            );

            return response()->json([
                'token' => $token,
                'uid' => $uid,
                'appId' => $appID,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Token generation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
