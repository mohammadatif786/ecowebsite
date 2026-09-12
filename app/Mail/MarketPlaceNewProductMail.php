<?php

namespace App\Mail;

use App\Models\EmailSponsorAd;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MarketPlaceNewProductMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\InjectSponsorAd;

    protected Collection $products;
    protected string $appUrl;

    public function __construct(Collection $products)
    {
        $this->products = $products;
        $this->appUrl = rtrim(config('app.url') ?: env('APP_URL'), '/') . '/';
    }

    public function content(): Content
    {
        $ad = $this->getSponsorAd('marketplace', $this->products->first()->seller->country_code);

        return new Content(
            view: 'emails.MarketPlaceNewProduct',
            with: [
                'products' => $this->products,
                'appUrl'   => $this->appUrl,
                'ad'       => $ad,
            ]
        );
    }
}
