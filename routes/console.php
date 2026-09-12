<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendMonthlySubscriptionReminders;
use App\Jobs\RecalculatePopularityScoresJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Queue-based monthly subscription reminders
Schedule::job(new SendMonthlySubscriptionReminders())->daily();

Schedule::job(new RecalculatePopularityScoresJob())->daily();

Schedule::command('app:send-birth-day-emails')->daily();

Schedule::command('app:send-interest-match-emails')->weekly();

Schedule::command('app:send-marketplace-emails')->weekly();

Schedule::command('app:send-marketplace-new-product')->weekly();

Schedule::command('app:send-event-emails')->daily();

Schedule::command('live:complete-stale-host-streams --minutes=2')->everyMinute();
