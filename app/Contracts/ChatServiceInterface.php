<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface ChatServiceInterface
{
    public function getChatUsers();
    public function togglePin(int $userId);
    public function toggleUnPin(int $userId);
    public function getConversation(string $slug, string $user, Request $request);
}
