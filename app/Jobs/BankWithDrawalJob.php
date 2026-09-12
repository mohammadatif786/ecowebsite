<?php

namespace App\Jobs;

use App\Mail\BankWithDrawalMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class BankWithDrawalJob implements ShouldQueue
{
    use Queueable;

    protected array $mailData;
    /**
     * Create a new job instance.
     */
    public function __construct(array $mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->mailData['user_email'])->send(new BankWithDrawalMail($this->mailData));
    }
}
