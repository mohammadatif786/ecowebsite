<?php

namespace App\Jobs;

use App\Models\PrivateLiveStreamSub;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendMonthlySubscriptionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        $subs = PrivateLiveStreamSub::query()
            ->whereIn('status', ['active', 'Active'])
            ->where('payment_type', 'monthly')
            ->get();

        $sent = 0;

        foreach ($subs as $sub) {
            if (!$sub->created_at) continue;

            $expiresAt = Carbon::parse($sub->created_at)->addDays(30);

            // Only remind once when exactly 3 days remain (daily schedule).
            if ($expiresAt->isPast()) continue;
            $daysUntil = round($now->diffInDays($expiresAt, false));
            if ($daysUntil !== 3) continue;

            $payUser = User::find($sub->pay_user_id);
            if (!$payUser) continue;

            // In-app notification
            Notification::create([
                'user_id' => $payUser->id,
                'send_by' => $sub->user_streamer_id,
                'title' => 'Subscription expiring soon',
                'message' => 'Your monthly private live subscription expires in 3 days. Renew to keep access.',
                'type' => 'monthly_sub_expiry',
                'context' => 'subscription',
                'metadata' => [
                    'user_streamer_id' => $sub->user_streamer_id,
                    'expires_at' => $expiresAt->toISOString(),
                ],
            ]);

            // Email
            if (!empty($payUser->email)) {
                Mail::raw(
                    "Hi {$payUser->name},\n\nYour monthly private live subscription will expire on {$expiresAt->toDayDateTimeString()} (in 3 days).\n\nRenew to keep access to future private streams.\n\nLinkUp",
                    function ($m) use ($payUser) {
                        $m->to($payUser->email)->subject('LinkUp: Subscription expiring in 3 days');
                    }
                );
            }

            $sent++;
        }

    }
}
