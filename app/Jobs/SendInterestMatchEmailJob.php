<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendInterestMatchEmailJob implements ShouldQueue
{
    use Queueable;

    public $user;
    public $userMatches;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $userMatches)
    {
        $this->user = $user;
        $this->userMatches = $userMatches;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new \App\Mail\SendInterestMatchEmail($this->user, $this->userMatches));
    }
}
