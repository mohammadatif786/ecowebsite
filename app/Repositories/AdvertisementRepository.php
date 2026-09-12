<?php

namespace App\Repositories;

use App\DTOs\AdvertisementData;
use App\Models\Advertisement;

class AdvertisementRepository
{
    public function create(AdvertisementData $data): Advertisement
    {
        return Advertisement::create([
            'firebase_id' => $data->firebaseId,
            'invoice_id' => $data->invoiceId,
            'category' => $data->category,
            'name' => $data->advertiserName,
            'www' => $data->websiteUrl,
            'email' => $data->contactEmail,
            'phone' => $data->contactPhone,
            'headline' => $data->headline,
            'description' => $data->shortDescription,
            'country' => $data->country,
            'state' => $data->state,
            'city' => $data->city,
            'location' => $data->venue,
            'ad_type' => $data->adType,
            'image' => $data->image,
            'video' => $data->video,
            'thumbnail' => $data->thumbnail,
            'max_duration' => $data->maxDuration,
            'autoplay_sound' => $data->autoplaySound,
            'cta_overlay_timing' => $data->ctaOverlayTiming,
            'loop_video' => $data->loopVideo,
            'cta_text' => $data->ctaText,
            'brand_color' => $data->brandColor,
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'duration_days' => $data->durationDays,
            'price_package' => $data->pricePackage,
            'custom_cost_override' => $data->customCostOverride,
            'payment_ref' => $data->paymentRef,
            'paid' => $data->isPaid ? 'yes' : 'no',
            'cost' => $data->cost,
            'is_paid' => $data->isPaid,
            'publication_status' => $data->publicationStatus,
            'targeting_notes' => $data->targetingNotes,
            'status' => $data->status,
        ]);
    }
}
