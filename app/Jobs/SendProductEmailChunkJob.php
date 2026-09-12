<?php

namespace App\Jobs;

use App\Mail\ProductCreatedMail;
use App\Models\EmailLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendProductEmailChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $productId;

    /** @var int[] */
    protected array $userIds;

    /**
     * @param int[] $userIds
     */
    public function __construct(int $productId, array $userIds)
    {
        $this->productId = $productId;
        $this->userIds = $userIds;
    }

    public function handle(): void
    {
        $product = Product::with(['merchant', 'category'])->find($this->productId);
        if (! $product) {
            return;
        }

        $users = User::whereIn('id', $this->userIds)
            ->whereNotNull('email')
            ->select('id', 'email')
            ->get();

        foreach ($users as $user) {
            $emailLog = null;

            try {
                $emailLog = EmailLog::create([
                    'token' => (string) Str::uuid(),
                    'email_type' => 'product_created',
                    'to_email' => (string) $user->email,
                    'to_user_id' => (int) $user->id,
                    'from_user_id' => (int) ($product->user_id ?? 0) ?: null,
                    'subject' => 'New product: ' . ($product->name ?? 'Product'),
                    'status' => 'sending',
                    'meta' => [
                        'product_id' => (int) $product->id,
                    ],
                ]);

                Mail::to($user->email)->queue(new ProductCreatedMail($product, $user, (string) $emailLog->token));

                $emailLog->status = 'sent';
                $emailLog->sent_at = now();
                $emailLog->save();
            } catch (\Throwable $e) {
                if ($emailLog) {
                    $emailLog->status = 'failed';
                    $emailLog->error = $e->getMessage();
                    $emailLog->save();
                }

                Log::warning('Failed to send product created email (chunk)', [
                    'product_id' => $product->id,
                    'to_user_id' => $user?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
