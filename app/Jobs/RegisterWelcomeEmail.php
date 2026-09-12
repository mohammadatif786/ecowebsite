<?php

namespace App\Jobs;

use App\Mail\WelcomeEmailMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class RegisterWelcomeEmail implements ShouldQueue
{
    use Queueable;

    protected User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $mailData)
    {
        $this->user = $mailData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->queue(new WelcomeEmailMail($this->user));
    }
}
