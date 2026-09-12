<?php

namespace App\Jobs;

use App\Mail\MatchFoundMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMatchEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected User $authUser;
    protected User $targetUser;

    public function __construct(User $authUser, User $targetUser)
    {
        $this->authUser = $authUser;
        $this->targetUser = $targetUser;
    }

    public function handle(): void
    {
        try {
            Mail::to([$this->authUser->email, $this->targetUser->email])
                ->send(new MatchFoundMail($this->authUser, $this->targetUser));
        } catch (\Exception $e) {
            Log::error("Failed to send match email: " . $e->getMessage());
            throw $e;
        }
    }
}
