<?php

namespace App\Actions\Admin\Ads;

use App\DTOs\AdvertisementData;
use App\Models\Advertisement;
use App\Services\Admin\Ads\AdvertisementService;
use Illuminate\Database\Eloquent\Collection;

class AdvertisementAction
{
    public function __construct(
        private AdvertisementService $service
    ) {}

    public function list(): array
    {
        $data = $this->service->getAllAdvertisements();
        return $data;
    }

    public function countries(): Collection
    {
        return $this->service->getCountries();
    }

    public function getEmailAdCategories(): Collection
    {
        return $this->service->getEmailAdCategoriesWithCounts();
    }

    public function store(AdvertisementData $data): Advertisement
    {
        $advertisement = $this->service->store($data);

        return $advertisement;
    }

    public function update(Advertisement $id, AdvertisementData $data): Advertisement
    {
        $advertisement = $this->service->update($id, $data);

        return $advertisement;
    }

    public function delete(Advertisement $id): void
    {
        $this->service->delete($id);
    }
}
