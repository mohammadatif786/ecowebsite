<?php

namespace App\Actions;

use App\DTOs\ProductData;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\FileUploadService;
use App\Services\RemoteImageService;
use Illuminate\Http\UploadedFile;

class CreateProductAction
{
    public function __construct(
        private ProductRepository $repo,
        private FileUploadService $fileUploadService,
        private RemoteImageService $remoteImageService
    ) {}

    public function execute(ProductData $data, ?UploadedFile $coverFile = null, ?array $imageFiles = null): Product
    {
        if (!$coverFile && $data->imageUrl && filter_var($data->imageUrl, FILTER_VALIDATE_URL)) {
            $data->coverImage = $this->remoteImageService->storeFromUrl($data->imageUrl, 'product');
        } elseif ($coverFile) {
            $data->coverImage = $this->fileUploadService->uploadSingle($coverFile, 'products');
        }

        if ($imageFiles) {
            $data->images = $this->fileUploadService->uploadMultiple($imageFiles, 'products');
        }

        return $this->repo->create($data);
    }

}
