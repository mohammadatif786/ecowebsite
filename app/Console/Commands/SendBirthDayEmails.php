<?php

namespace App\Console\Commands;

use App\Jobs\SendBirthDayJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendBirthDayEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-birth-day-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday emails to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $users = User::whereDay('birthday', now()->day)
            ->whereMonth('birthday', now()->month)
            ->get();

        foreach ($users as $user) {
            SendBirthDayJob::dispatch($user);
        }
    }
}
