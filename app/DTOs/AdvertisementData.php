<?php

namespace App\DTOs;

class AdvertisementData
{
    public function __construct(
        public ?string $category,
        public ?string $advertiserName,
        public ?string $websiteUrl,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $headline,
        public ?string $shortDescription,
        public ?string $country,
        public ?string $state,
        public ?string $city,
        public ?string $venue,
        public ?string $adType,
        public ?string $image,
        public ?string $video,
        public ?string $thumbnail,
        public ?string $maxDuration,
        public ?string $autoplaySound,
        public ?string $ctaOverlayTiming,
        public ?string $loopVideo,
        public ?string $ctaText,
        public ?string $brandColor,
        public ?string $startDate,
        public ?string $endDate,
        public ?int $durationDays,
        public ?string $pricePackage,
        public ?string $customCostOverride,
        public ?string $paymentRef,
        public bool $isPaid,
        public ?string $publicationStatus,
        public ?string $targetingNotes,
        public ?string $firebaseId = null,
        public ?int $invoiceId = null,
        public ?string $cost = null,
        public bool $status = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            category: $data['category'] ?? null,
            advertiserName: $data['advertiser_name'] ?? null,
            websiteUrl: $data['website_url'] ?? null,
            contactEmail: $data['contact_email'] ?? null,
            contactPhone: $data['contact_phone'] ?? null,
            headline: $data['headline'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            country: $data['country'] ?? null,
            state: $data['state'] ?? null,
            city: $data['city'] ?? null,
            venue: $data['venue'] ?? null,
            adType: $data['ad_type'] ?? null,
            image: $data['image'] ?? null,
            video: $data['video'] ?? null,
            thumbnail: $data['thumbnail'] ?? null,
            maxDuration: $data['max_duration'] ?? null,
            autoplaySound: $data['autoplay_sound'] ?? null,
            ctaOverlayTiming: $data['cta_overlay_timing'] ?? null,
            loopVideo: $data['loop_video'] ?? null,
            ctaText: $data['cta_text'] ?? null,
            brandColor: $data['brand_color'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            durationDays: $data['duration_days'] ?? null,
            pricePackage: $data['price_package'] ?? null,
            customCostOverride: $data['custom_cost_override'] ?? null,
            paymentRef: $data['payment_ref'] ?? null,
            isPaid: (bool) ($data['is_paid'] ?? false),
            publicationStatus: $data['publication_status'] ?? null,
            targetingNotes: $data['targeting_notes'] ?? null,
        );
    }
}
