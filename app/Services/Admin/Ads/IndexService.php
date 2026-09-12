<?php

namespace App\Services\Admin\Ads;

use App\Actions\Admin\Ads\IndexAction;

class IndexService
{

    protected IndexAction $ads;


    public function __construct(IndexAction $ads)
    {
        $this->ads = $ads;
    }
    public function dashboard(): array
    {
        $ads = $this->ads->execute();

        return $ads;
    }
}
