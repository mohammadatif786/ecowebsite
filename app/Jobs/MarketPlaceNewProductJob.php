<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Collection;
use App\Mail\MarketPlaceNewProductMail;
use Illuminate\Support\Facades\Mail;

class MarketPlaceNewProductJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected User $user;
    protected Collection $products;

    public function __construct(User $user, Collection $products)
    {
        $this->user = $user;
        $this->products = $products;
    }

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new MarketPlaceNewProductMail($this->products));
    }
}
