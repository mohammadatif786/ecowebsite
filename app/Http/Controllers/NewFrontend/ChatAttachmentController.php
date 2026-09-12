<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\Linkup\Services\LinkupAccessService;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;

class ChatAttachmentController extends Controller
{
    public function show(Message $message, LinkupAccessService $access)
    {
        $user = request()->user();
        abort_unless($user && in_array($user->id, [$message->from_user_id, $message->to_user_id], true), 403);
        $otherId = $message->from_user_id === $user->id ? $message->to_user_id : $message->from_user_id;
        abort_unless($access->canChat($user, $message->sender->is($user) ? $message->receiver : $message->sender), 403);

        $path = $message->meta['path'] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $message->meta['name'] ?? basename($path));
    }
}
