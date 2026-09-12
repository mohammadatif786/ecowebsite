<?php

namespace App\Helpers;

use Yasser\Agora\RtcTokenBuilder;

class AgoraToken
{
    public static function buildToken($appId, $appCertificate, $channelName, $uid, $expireTimeInSeconds)
    {
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + (int) $expireTimeInSeconds;
        $numericUid = (int) $uid;

        return RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channelName,
            $numericUid,
            RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );
    }
}
