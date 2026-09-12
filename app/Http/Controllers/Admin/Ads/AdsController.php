<?php

namespace App\Http\Controllers\Admin\Ads;

use App\Actions\Admin\Ads\AdvertisementAction;
use App\Actions\Admin\Ads\CampaignTypeAction;
use App\Actions\Admin\Ads\TerritoryTierAction;
use App\Actions\Admin\Ads\DeliveryChannelAction;
use App\Actions\Admin\Ads\ExclusivityUpgradeAction;
use App\Actions\Admin\Ads\GetAdminAdManagementDataAction;
use App\Actions\Admin\Ads\AdIndustryAction;
use App\Actions\Admin\Ads\AdSurgeOptionAction;
use App\DTOs\Ads\CampaignTypeData;
use App\DTOs\Ads\TerritoryTierData;
use App\DTOs\Ads\DeliveryChannelData;
use App\DTOs\Ads\ExclusivityUpgradeData;
use App\DTOs\Ads\AdIndustryData;
use App\DTOs\Ads\AdSurgeOptionData;
use Inertia\Inertia;
use App\DTOs\AdvertisementData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ads\StoreAdvertisementRequest;
use App\Http\Requests\Admin\Ads\StoreCampaignRequest;
use App\Http\Requests\Admin\Ads\StoreTerritoryTierRequest;
use App\Http\Requests\Admin\Ads\StoreDeliveryChannelRequest;
use App\Http\Requests\Admin\Ads\StoreExclusivityUpgradeRequest;
use App\Http\Requests\Admin\Ads\StoreAdIndustryRequest;
use App\Http\Requests\Admin\Ads\StoreAdSurgeOptionRequest;
use App\Http\Requests\Admin\Ads\StoreCampaignLaunchRequest;
use App\Models\Advertisement;
use App\Models\CampaignType;
use App\Models\TerritoryTier;
use App\Models\DeliveryChannel;
use App\Models\ExclusivityUpgrade;
use App\Models\AdIndustry;
use App\Models\AdSurgeOption;
use App\Models\AdCampaignLaunch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AdsController extends Controller
{

    public function index(GetAdminAdManagementDataAction $action)
    {
        $data = $action->execute();
        return Inertia::render('admin/ads/Dashboard', $data->toArray());
    }

    public function analytics()
    {
        return Inertia::render('admin/ads/Analytics');
    }

    public function reports()
    {
        return Inertia::render('admin/ads/Reports');
    }

    public function indexAds(AdvertisementAction $action)
    {
        $data = $action->list();

        return Inertia::render('admin/ads/AllAds', [
            'ads' => $data['ads'],
            'stats' => $data['stats']
        ]);
    }

    public function restaurantsAdsList()
    {
        return Inertia::render('admin/ads/RestaurantsAds');
    }

    public function clubsFetesAdsList()
    {
        return Inertia::render('admin/ads/ClubsFetesAds');
    }

    public function campaignsIndex(CampaignTypeAction $action, TerritoryTierAction $tierAction)
    {
        $campaignTypes = $action->list();
        $territoryTiers = $tierAction->list();

        return Inertia::render('admin/ads/Campaigns', compact('campaignTypes', 'territoryTiers'));
    }

    public function Campaignstore(StoreCampaignRequest $request, CampaignTypeAction $action)
    {
        $data = CampaignTypeData::fromArray($request->validated());

        $campaignType = $action->execute($data);

        return response()->json(['success' => true, 'campaignType' => $campaignType], 201);
    }

    public function campaignShow(CampaignType $id)
    {
        return response()->json($id);
    }

    public function campaignUpdate(StoreCampaignRequest $request, CampaignTypeAction $action, CampaignType $id)
    {
        $data = CampaignTypeData::fromArray($request->validated());

        $campaignType = $action->update($id, $data);

        return response()->json(['success' => true, 'campaignType' => $campaignType]);
    }

    public function campaignDelete(CampaignTypeAction $action, CampaignType $id)
    {
        $deletedId = $id->id;

        $action->delete($id);

        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    public function campaignLaunch(StoreCampaignLaunchRequest $request)
    {
        $data = $request->validated();
        $reference = $this->makeCampaignReference();

        $launch = AdCampaignLaunch::create($this->campaignLaunchAttributes($data, [
            'reference' => $reference,
            'status' => 'active',
            'launched_at' => now(),
        ]));

        return response()->json(['success' => true, 'campaign' => $launch], 201);
    }

    public function campaignLaunchUpdate(StoreCampaignLaunchRequest $request, AdCampaignLaunch $id)
    {
        $data = $request->validated();

        $id->update($this->campaignLaunchAttributes($data));

        return response()->json(['success' => true, 'campaign' => $id->fresh()]);
    }

    private function campaignLaunchAttributes(array $data, array $extra = []): array
    {
        $pricing = $data['pricing'];
        $override = $data['override'] ?? [];

        return array_merge([
            'campaign_type_id' => $data['type']['id'] ?? null,
            'territory_tier_id' => $data['tier']['id'] ?? null,
            'ad_industry_id' => $data['industry']['id'] ?? null,
            'exclusivity_upgrade_id' => $data['exclusivity']['id'] ?? null,
            'campaign_type_snapshot' => $data['type'],
            'territory_tier_snapshot' => $data['tier'],
            'selected_countries' => $data['countries'] ?? [],
            'selected_diaspora_markets' => $data['diaspora_markets'] ?? [],
            'delivery_channels_snapshot' => $data['channels'],
            'industry_snapshot' => $data['industry'],
            'frequency_snapshot' => $data['frequency'],
            'start_date' => $data['schedule']['start_date'],
            'end_date' => $data['schedule']['end_date'],
            'surge_active' => $data['surge']['active'],
            'surge_snapshot' => $data['surge']['option'] ?? null,
            'exclusivity_snapshot' => $data['exclusivity'] ?? null,
            'pricing_snapshot' => $pricing,
            'calculated_total' => $pricing['calculated_total'],
            'final_total' => $pricing['final_total'],
            'internal_notes' => $override['notes'] ?? null,
        ], $extra);
    }

    private function makeCampaignReference(): string
    {
        do {
            $reference = 'LV-' . Str::upper(Str::random(6));
        } while (AdCampaignLaunch::where('reference', $reference)->exists());

        return $reference;
    }

    // ══ Territory Tiers ══
    public function territoryTierStore(StoreTerritoryTierRequest $request, TerritoryTierAction $action)
    {
        $data = TerritoryTierData::fromArray($request->validated());

        $tier = $action->execute($data);

        return response()->json(['success' => true, 'tier' => $tier], 201);
    }

    public function territoryTierUpdate(StoreTerritoryTierRequest $request, TerritoryTierAction $action, TerritoryTier $id)
    {
        $data = TerritoryTierData::fromArray($request->validated());

        $tier = $action->update($id, $data);

        return response()->json(['success' => true, 'tier' => $tier]);
    }

    public function territoryTierDelete(TerritoryTierAction $action, TerritoryTier $id)
    {
        $deletedId = $id->id;

        $action->delete($id);

        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    // ══ Delivery Channels ══
    public function deliveryChannelStore(StoreDeliveryChannelRequest $request, DeliveryChannelAction $action)
    {
        $data = DeliveryChannelData::fromArray($request->validated());

        $channel = $action->execute($data);

        return response()->json(['success' => true, 'channel' => $channel], 201);
    }

    public function deliveryChannelUpdate(StoreDeliveryChannelRequest $request, DeliveryChannelAction $action, DeliveryChannel $id)
    {
        $data = DeliveryChannelData::fromArray($request->validated());

        $channel = $action->update($id, $data);

        return response()->json(['success' => true, 'channel' => $channel]);
    }

    public function deliveryChannelDelete(DeliveryChannelAction $action, DeliveryChannel $id)
    {
        $deletedId = $id->id;

        $action->delete($id);

        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    // ══ Exclusivity Upgrades ══
    public function exclusivityUpgradeStore(StoreExclusivityUpgradeRequest $request, ExclusivityUpgradeAction $action)
    {
        $data = ExclusivityUpgradeData::fromArray($request->validated());

        $upgrade = $action->execute($data);

        return response()->json(['success' => true, 'upgrade' => $upgrade], 201);
    }

    public function exclusivityUpgradeUpdate(StoreExclusivityUpgradeRequest $request, ExclusivityUpgradeAction $action, ExclusivityUpgrade $id)
    {
        $data = ExclusivityUpgradeData::fromArray($request->validated());

        $upgrade = $action->update($id, $data);

        return response()->json(['success' => true, 'upgrade' => $upgrade]);
    }

    public function exclusivityUpgradeDelete(ExclusivityUpgradeAction $action, ExclusivityUpgrade $id)
    {
        $deletedId = $id->id;

        $action->delete($id);

        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    // ══ Ad Industries ══
    public function adIndustryStore(StoreAdIndustryRequest $request, AdIndustryAction $action)
    {
        $data = AdIndustryData::fromArray($request->validated());
        $industry = $action->execute($data);
        return response()->json(['success' => true, 'industry' => $industry], 201);
    }

    public function adIndustryUpdate(StoreAdIndustryRequest $request, AdIndustryAction $action, AdIndustry $id)
    {
        $data = AdIndustryData::fromArray($request->validated());
        $industry = $action->update($id, $data);
        return response()->json(['success' => true, 'industry' => $industry]);
    }

    public function adIndustryDelete(AdIndustryAction $action, AdIndustry $id)
    {
        $deletedId = $id->id;
        $action->delete($id);
        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    // ══ Ad Surge Options ══
    public function adSurgeOptionStore(StoreAdSurgeOptionRequest $request, AdSurgeOptionAction $action)
    {
        $data = AdSurgeOptionData::fromArray($request->validated());
        $option = $action->execute($data);
        return response()->json(['success' => true, 'option' => $option], 201);
    }

    public function adSurgeOptionUpdate(StoreAdSurgeOptionRequest $request, AdSurgeOptionAction $action, AdSurgeOption $id)
    {
        $data = AdSurgeOptionData::fromArray($request->validated());
        $option = $action->update($id, $data);
        return response()->json(['success' => true, 'option' => $option]);
    }

    public function adSurgeOptionDelete(AdSurgeOptionAction $action, AdSurgeOption $id)
    {
        $deletedId = $id->id;
        $action->delete($id);
        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    public function adsIndex(AdvertisementAction $action)
    {
        return Inertia::render('admin/ads/EmailSponsorAds', [
            'categories' => $action->getEmailAdCategories(),
            'countries' => $action->countries(),
        ]);
    }

    public function storeAds(StoreAdvertisementRequest $request, AdvertisementAction $action)
    {
        $data = AdvertisementData::fromArray($request->validated());

        $advertisement = $action->store($data);

        return response()->json(["success" => true, "advertisement" => $advertisement], 201);
    }

    public function editAds(Advertisement $id)
    {
        return response()->json($id);
    }

    public function updateAds(StoreAdvertisementRequest $request, AdvertisementAction $action, Advertisement $id)
    {
        $data = AdvertisementData::fromArray($request->validated());

        $advertisement = $action->update($id, $data);

        return response()->json(["success" => true, "advertisement" => $advertisement]);
    }

    public function destroyAds(AdvertisementAction $action, Advertisement $id)
    {
        $deletedId = $id->id;

        $action->delete($id);

        return response()->json(["success" => true, "id" => $deletedId]);
    }

    public function c360NewsList()
    {
        return Inertia::render('admin/ads/C360News');
    }

    public function c360NewsAds()
    {
        $ads = Advertisement::query()
            ->where('ad_type', 'c360-news')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'ads' => $ads->map(fn (Advertisement $ad) => $this->formatC360NewsAd($ad))->values(),
        ]);
    }

    public function c360NewsStore(Request $request)
    {
        $data = $this->validateC360News($request);

        $ad = Advertisement::create($this->c360Payload($data));

        return response()->json([
            'success' => true,
            'ad' => $this->formatC360NewsAd($ad->fresh()),
        ], 201);
    }

    public function c360NewsUpdate(Request $request, Advertisement $ad)
    {
        abort_unless($ad->ad_type === 'c360-news', 404);

        $data = $this->validateC360News($request);

        $ad->update($this->c360Payload($data));

        return response()->json([
            'success' => true,
            'ad' => $this->formatC360NewsAd($ad->fresh()),
        ]);
    }

    public function c360NewsDestroy(Advertisement $ad)
    {
        abort_unless($ad->ad_type === 'c360-news', 404);

        $deletedId = $ad->id;
        $ad->delete();

        return response()->json(['success' => true, 'id' => $deletedId]);
    }

    private function validateC360News(Request $request): array
    {
        return $request->validate([
            'advertiser_name' => 'required|string|max:255',
            'placement_key' => 'required|string|max:100',
            'placement_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'territory' => 'required|string|max:255',
            'territory_flags' => 'nullable|string|max:255',
            'territory_keys' => 'nullable|array',
            'territory_keys.*' => 'string|max:20',
            'headline' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'package_key' => 'required|string|max:100',
            'package_name' => 'required|string|max:255',
            'package_duration' => 'nullable|string|max:100',
            'cost' => 'required|numeric|min:0',
            'status' => 'nullable|in:Active,Draft,Paused,active,draft,paused',
        ]);
    }

    private function c360Payload(array $data): array
    {
        $status = strtolower($data['status'] ?? 'active');
        $targeting = [
            'placement_key' => $data['placement_key'],
            'placement_name' => $data['placement_name'],
            'package_key' => $data['package_key'],
            'package_name' => $data['package_name'],
            'package_duration' => $data['package_duration'] ?? null,
            'territory_flags' => $data['territory_flags'] ?? '',
            'territory_keys' => $data['territory_keys'] ?? [],
        ];

        return [
            'category' => $data['category'],
            'name' => $data['advertiser_name'],
            'headline' => $data['headline'] ?? null,
            'description' => $data['placement_name'],
            'country' => $data['territory'],
            'state' => $data['territory_flags'] ?? null,
            'city' => $data['territory'],
            'location' => $data['placement_name'],
            'ad_type' => 'c360-news',
            'cost' => (string) round((float) $data['cost'], 2),
            'paid' => 'yes',
            'is_paid' => true,
            'status' => $status !== 'draft' && $status !== 'paused',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'duration_days' => max(0, Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date']))),
            'price_package' => $data['package_key'],
            'publication_status' => ucfirst($status),
            'targeting_notes' => json_encode($targeting),
        ];
    }

    private function formatC360NewsAd(Advertisement $ad): array
    {
        $targeting = json_decode($ad->targeting_notes ?: '{}', true) ?: [];

        return [
            'id' => $ad->id,
            'advertiser_name' => $ad->name,
            'placement_key' => $targeting['placement_key'] ?? $ad->location ?? 'homepage-feature',
            'placement_name' => $targeting['placement_name'] ?? $ad->location ?? $ad->description ?? 'Homepage Feature',
            'category' => $ad->category,
            'territory' => $ad->country,
            'territory_flags' => $targeting['territory_flags'] ?? $ad->state ?? '',
            'territory_keys' => $targeting['territory_keys'] ?? [],
            'headline' => $ad->headline,
            'start_date' => $ad->start_date ? Carbon::parse($ad->start_date)->format('Y-m-d') : null,
            'end_date' => $ad->end_date ? Carbon::parse($ad->end_date)->format('Y-m-d') : null,
            'package_key' => $targeting['package_key'] ?? $ad->price_package,
            'package_name' => $targeting['package_name'] ?? $ad->price_package,
            'package_duration' => $targeting['package_duration'] ?? null,
            'cost' => (float) $ad->cost,
            'status' => $ad->status ? 'Active' : ($ad->publication_status ?: 'Draft'),
            'impressions' => 0,
            'clicks' => 0,
            'ctr' => '0.0%',
            'created_at' => $ad->created_at,
        ];
    }
}
