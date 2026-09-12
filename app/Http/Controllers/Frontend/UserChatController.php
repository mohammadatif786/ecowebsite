<?php

namespace App\Http\Controllers\Frontend;

use App\Contracts\ChatServiceInterface;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FeatureGateService;
use Illuminate\Support\Facades\Auth;

class UserChatController extends Controller
{
    private $chatService;
    private bool $feature;

    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }
    public function index()
    {

        $chatUsers = $this->chatService->getChatUsers();

        return Inertia::render('User/Chat/Index', [
            'users' => $chatUsers,
        ]);
    }

    public function startChat(string $slug, string $user, Request $request)
    {
        $getConversation = $this->chatService->getConversation($slug, $user, $request);
        if (!app(FeatureGateService::class)->allows(auth()->user(), 'video_voice_calls')) {
            $this->feature = false;
        } else {
            $this->feature = true;
        }
        return Inertia::render('User/Chat/Chat', [
            'chatmessages' => $getConversation['messages'],
            'receiver' => $getConversation['receiver'],
            'auth' => ['user' => Auth::user()],
            'search' => $getConversation['search'],
            'tickets' => $getConversation['tickets'],
            'feature' => $this->feature,
        ]);
    }

    public function toggleUnPin(int $userId)
    {
        $this->chatService->toggleUnPin($userId);

        return back()->with(['success' => 'Pin status updated successfully.']);
    }
    public function togglePin(int $userId)
    {

        $this->chatService->togglePin($userId);

        return back()->with(['success' => 'Pin status updated successfully.']);
    }
}
