<?php

namespace App\Services\Admin\Ads;

use App\Actions\Admin\Ads\CreateAdvertisementAction;
use App\DTOs\AdvertisementData;
use App\Models\Advertisement;
use App\Models\Country;
use App\Models\EmailAdCategory;
use App\Models\EmailSponsorAd;
use App\Models\SponsorClick;
use App\Models\SponsorImpression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdvertisementService
{
    public function __construct(
        private CreateAdvertisementAction $createAdvertisement,
    ) {}

    public function getAllAdvertisements(): array
    {
        $ads = Advertisement::withCount([
            'swipeAdEvents as impressions_count' => function ($query) {
                $query->where('event_type', 'impression');
            },
            'swipeAdEvents as clicks_count' => function ($query) {
                $query->where('event_type', 'click');
            },
            'swipeAdEvents as swipe_lefts_count' => function ($query) {
                $query->where('event_type', 'swipe_left');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'headline', 'description', 'category', 'city', 'location', 'email', 'ad_type', 'start_date', 'end_date', 'duration_days', 'publication_status', 'status', 'image', 'thumbnail', 'cost', 'is_paid', 'price_package', 'created_at']);

        $totalImpressions = \App\Models\SwipeAdEvent::where('event_type', 'impression')->count();
        $totalClicks = \App\Models\SwipeAdEvent::where('event_type', 'click')->count();
        $activeAdsCount = Advertisement::where('status', 1)->count();
        $totalRevenue = Advertisement::sum('cost');

        // Email Ad Stats
        $emailAds = EmailSponsorAd::with(['category', 'countries'])
            ->withCount(['impressions', 'clicks'])
            ->orderBy('created_at', 'desc')
            ->get();
        $emailImpressions = SponsorImpression::count();
        $emailClicks = SponsorClick::count();
        $emailRevenue = $this->getEmailSponsorRevenue();
        $emailCategoryLabels = EmailAdCategory::pluck('label', 'key');
        $emailCategoryStats = SponsorImpression::query()
            ->select('email_category', DB::raw('COUNT(*) as impressions'))
            ->groupBy('email_category')
            ->get()
            ->map(function ($row) use ($emailCategoryLabels) {
                $key = $row->email_category ?: 'uncategorized';

                return [
                    'key' => $key,
                    'name' => $emailCategoryLabels[$key] ?? $key,
                    'impressions' => (int) $row->impressions,
                    'clicks' => 0,
                ];
            });
        $emailClicksByCategory = SponsorClick::query()
            ->join('email_sponsor_ads', 'sponsor_clicks.email_sponsor_ad_id', '=', 'email_sponsor_ads.id')
            ->leftJoin('email_ad_categories', 'email_sponsor_ads.email_ad_category_id', '=', 'email_ad_categories.id')
            ->select(DB::raw("COALESCE(email_ad_categories.key, 'uncategorized') as category_key"), DB::raw('COUNT(*) as clicks'))
            ->groupBy('category_key')
            ->pluck('clicks', 'category_key');
        $emailCategoryStats = $emailCategoryStats->map(function ($row) use ($emailClicksByCategory) {
            $row['clicks'] = (int) ($emailClicksByCategory[$row['key']] ?? 0);
            return $row;
        });
        $emailCountryStats = SponsorImpression::query()
            ->select('country_code', DB::raw('COUNT(*) as impressions'))
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('impressions')
            ->get()
            ->map(fn ($row) => [
                'name' => strtoupper($row->country_code),
                'impressions' => (int) $row->impressions,
            ]);

        return ([
            'ads' => $ads,
            'emailAds' => $emailAds,
            'stats' => [
                'impressions' => $totalImpressions,
                'clicks'      => $totalClicks,
                'likes'       => $totalClicks,
                'active_ads'  => $activeAdsCount,
                'total_revenue' => $totalRevenue,
                'email' => [
                    'impressions' => $emailImpressions,
                    'clicks' => $emailClicks,
                    'opens' => 0,
                    'revenue' => $emailRevenue,
                    'categories' => $emailCategoryStats,
                    'countries' => $emailCountryStats,
                    'active_ads' => EmailSponsorAd::where('status', 'active')->count(),
                ]
            ]
        ]);
    }

    public function store(AdvertisementData $data): Advertisement
    {
        $data = $this->applyCreativeAdTypeRules($data);

        // Convert base64 media to random file identifiers
        $data->image = $this->convertBase64ToRandomFile($data->image, 'image');
        $data->video = $this->convertBase64ToRandomFile($data->video, 'video');
        $data->thumbnail = $this->convertBase64ToRandomFile($data->thumbnail, 'image');

        $data->cost = $this->resolveCost($data);
        $data->status = $this->determineStatusFlag($data);
        $data->publicationStatus = $data->publicationStatus ?? 'Active (publish immediately)';

        return $this->createAdvertisement->execute($data);
    }

    public function update(Advertisement $ad, AdvertisementData $data): Advertisement
    {
        $data = $this->applyCreativeAdTypeRules($data);

        // Convert base64 media to random file identifiers
        $data->image = $this->convertBase64ToRandomFile($data->image, 'image');
        $data->video = $this->convertBase64ToRandomFile($data->video, 'video');
        $data->thumbnail = $this->convertBase64ToRandomFile($data->thumbnail, 'image');

        $data->cost = $this->resolveCost($data);
        $data->status = $this->determineStatusFlag($data);
        $data->publicationStatus = $data->publicationStatus ?? 'Active (publish immediately)';

        return $this->updateAdvertisement($ad, $data);
    }

    public function delete(Advertisement $ad): void
    {
        foreach ([$ad->image, $ad->video, $ad->thumbnail] as $mediaUrl) {
            $this->deleteStoredMedia($mediaUrl);
        }

        $ad->delete();
    }

    private function deleteStoredMedia(?string $mediaUrl): void
    {
        if (!$mediaUrl || !str_starts_with($mediaUrl, '/storage/')) {
            return;
        }

        $path = substr($mediaUrl, strlen('/storage/'));

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function updateAdvertisement(Advertisement $ad, AdvertisementData $data): Advertisement
    {
        $ad->update([
            'category'             => $data->category,
            'name'                 => $data->advertiserName,
            'www'                  => $data->websiteUrl,
            'email'                => $data->contactEmail,
            'phone'                => $data->contactPhone,
            'headline'             => $data->headline,
            'description'          => $data->shortDescription,
            'country'              => $data->country,
            'state'                => $data->state,
            'city'                 => $data->city,
            'location'             => $data->venue,
            'ad_type'              => $data->adType,
            'image'                => $data->image,
            'video'                => $data->video,
            'thumbnail'            => $data->thumbnail,
            'max_duration'         => $data->maxDuration,
            'autoplay_sound'       => $data->autoplaySound,
            'cta_overlay_timing'   => $data->ctaOverlayTiming,
            'loop_video'           => $data->loopVideo,
            'cta_text'             => $data->ctaText,
            'brand_color'          => $data->brandColor,
            'start_date'           => $data->startDate,
            'end_date'             => $data->endDate,
            'duration_days'        => $data->durationDays,
            'price_package'        => $data->pricePackage,
            'custom_cost_override' => $data->customCostOverride,
            'payment_ref'          => $data->paymentRef,
            'paid'                 => $data->isPaid ? 'yes' : 'no',
            'cost'                 => $data->cost,
            'is_paid'              => $data->isPaid,
            'publication_status'   => $data->publicationStatus,
            'targeting_notes'      => $data->targetingNotes,
            'status'               => $data->status,
        ]);

        return $ad->fresh();
    }

    private function applyCreativeAdTypeRules(AdvertisementData $data): AdvertisementData
    {
        if ($data->adType === 'image') {
            $data->video = null;
            $data->thumbnail = null;
        }

        if ($data->adType === 'video') {
            $data->image = null;
        }

        if ($data->adType === 'both') {
            // keep both image and video fields
        }

        return $data;
    }

    private function resolveCost(AdvertisementData $data): string
    {
        if ($data->customCostOverride !== null && is_numeric($data->customCostOverride) && $data->customCostOverride !== '') {
            return (string) round((float) $data->customCostOverride, 2);
        }

        return match ($data->pricePackage) {
            'starter' => '249',
            'growth' => '749',
            'network' => '1999',
            default => '0',
        };
    }

    private function determineStatusFlag(AdvertisementData $data): bool
    {
        $statusText = strtolower((string) $data->publicationStatus);
        return !str_contains($statusText, 'draft');
    }

    private function getEmailSponsorRevenue(): float
    {
        if (Schema::hasColumn('email_sponsor_ads', 'revenue')) {
            return (float) EmailSponsorAd::sum('revenue');
        }

        if (Schema::hasColumn('email_sponsor_ads', 'cost')) {
            return (float) EmailSponsorAd::sum('cost');
        }

        return 0.0;
    }

    /**
     * Convert base64 media to random file identifier
     *
     * @param string|null $mediaData Base64 data or URL
     * @param string $type Type of media (image or video)
     * @return string|null Random file path or original URL
     */
    private function convertBase64ToRandomFile(?string $mediaData, string $type): ?string
    {
        if (!$mediaData) {
            return null;
        }

        // If it's already a URL (not base64), return as-is
        if (!str_starts_with($mediaData, 'data:')) {
            return $mediaData;
        }

        // Extract mime type and base64 content
        if (preg_match('/^data:(image|video)\/(\w+);base64,(.+)$/', $mediaData, $matches)) {
            $mimeType = $matches[1]; // image or video
            $extension = $matches[2]; // jpg, mp4, etc.
            $base64Content = $matches[3];

            // Generate random filename (10-20 characters)
            $randomFilename = $this->generateRandomFilename(10, 20) . '.' . $extension;

            // Determine storage path based on type
            $storagePath = $type === 'video' ? 'ads/videos' : 'ads/images';

            try {
                // Decode and save to storage
                $fileData = base64_decode($base64Content);
                $fullPath = $storagePath . '/' . $randomFilename;

                Storage::disk('public')->put($fullPath, $fileData);

                // Return the storage URL
                return Storage::url($fullPath);
            } catch (\Exception $e) {
                Log::error('Failed to save media file', [
                    'error' => $e->getMessage(),
                    'type' => $type
                ]);
                // Return original base64 if save fails
                return $mediaData;
            }
        }

        // Return original if not a valid base64 data URI
        return $mediaData;
    }

    /**
     * Generate random filename
     *
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @return string Random filename
     */
    private function generateRandomFilename(int $minLength, int $maxLength): string
    {
        $length = rand($minLength, $maxLength);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Get email ad categories with ad counts
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmailAdCategoriesWithCounts()
    {
        return EmailAdCategory::withCount('ads')->orderBy('label')->get();
    }

    public function getC360NewsAds()
    {
        return Advertisement::query()
            ->where('ad_type', 'c360-news')
            ->latest()
            ->get([
                'id',
                'category',
                'name',
                'headline',
                'description',
                'country',
                'state',
                'city',
                'location',
                'email',
                'phone',
                'ad_type',
                'image',
                'video',
                'thumbnail',
                'cost',
                'is_paid',
                'status',
                'start_date',
                'end_date',
                'duration_days',
                'price_package',
                'publication_status',
                'targeting_notes',
                'created_at',
            ]);
    }

    /**
     * Get countries ordered by subregion and name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCountries()
    {
        return Country::orderBy('subregion')->orderBy('name')->get();
    }
}
