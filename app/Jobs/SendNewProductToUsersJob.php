<?php

namespace App\Jobs;

use App\Models\FavoriteSeller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewProductToUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (! $product) return;

        $sellerId = $product->user_id;

        $followerIds = FavoriteSeller::query()
            ->where('seller_id', $sellerId)
            ->pluck('user_id');

        if ($followerIds->isEmpty()) return;

        User::query()
            ->whereIn('id', $followerIds)
            ->whereNotNull('email')
            ->select('id')
            ->chunkById(500, function ($users) use ($product) {

                SendProductEmailChunkJob::dispatch(
                    $product->id,
                    $users->pluck('id')->toArray()
                );
            });
    }
}
