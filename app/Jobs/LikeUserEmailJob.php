<?php

namespace App\Jobs;

use App\Mail\LikeUserEmailMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class LikeUserEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $targetUser;
    public $authUser;

    public function __construct($targetUser, $authUser)
    {
        $this->targetUser = $targetUser;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->targetUser->email)->queue(new LikeUserEmailMail($this->targetUser, $this->authUser));
    }
}
