<?php

namespace App\Jobs;

use App\Mail\EventMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $events;
    protected $cookouts;
    protected $wellness;

    public function __construct($user, $events, $cookouts = null, $wellness = null)
    {
        $this->user = $user;
        $this->events = $events;
        $this->cookouts = $cookouts ?? collect();
        $this->wellness = $wellness ?? collect();
    }

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new EventMail($this->user, $this->events, $this->cookouts, $this->wellness));
    }
}