<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $log = EmailLog::where('token', $token)->first();
        if ($log) {
            $log->open_count = (int) ($log->open_count ?? 0) + 1;
            if (! $log->opened_at) {
                $log->opened_at = now();
            }
            $log->save();
        }

        // 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
