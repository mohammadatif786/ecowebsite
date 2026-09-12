<?php

namespace App\Jobs;

use App\Mail\SendMarketplaceEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMarketplaceEmailJob implements ShouldQueue
{
    use Queueable;

    public $user;
    public $topPicks;
    public $popularSellers;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $topPicks = null, $popularSellers = null)
    {
        $this->user = $user;
        $this->topPicks = $topPicks;
        $this->popularSellers = $popularSellers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new SendMarketplaceEmail($this->user, $this->topPicks, $this->popularSellers));
    }
}
