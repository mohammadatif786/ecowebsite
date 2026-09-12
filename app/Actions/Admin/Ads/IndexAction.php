<?php

namespace App\Actions\Admin\Ads;

class IndexAction
{
    public function execute(): array
    {
        $ads = [
            'email_sponsor_ads' => 'dashbaord ads'
        ];

        return $ads;
    }

}
