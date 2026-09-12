<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

type AdsSettingsValue = {
    admob_native_ad_id: string | null;
    admob_interstitial_ad_id: string | null;
    admob_banner_ad_id: string | null;
    admob_rewarded_video_id: string | null;
    home_feed_ads_enabled: boolean;
    marketplace_ads_enabled: boolean;
    news_sponsored_ads_enabled: boolean;
    live_stream_ads_enabled: boolean;
    event_ticket_ads_enabled: boolean;
};

const props = defineProps<{
    initialAdsSettings?: Partial<AdsSettingsValue>;
}>();

const form = useForm<AdsSettingsValue>({
    admob_native_ad_id: props.initialAdsSettings?.admob_native_ad_id ?? null,
    admob_interstitial_ad_id: props.initialAdsSettings?.admob_interstitial_ad_id ?? null,
    admob_banner_ad_id: props.initialAdsSettings?.admob_banner_ad_id ?? null,
    admob_rewarded_video_id: props.initialAdsSettings?.admob_rewarded_video_id ?? null,
    home_feed_ads_enabled: props.initialAdsSettings?.home_feed_ads_enabled ?? true,
    marketplace_ads_enabled: props.initialAdsSettings?.marketplace_ads_enabled ?? true,
    news_sponsored_ads_enabled: props.initialAdsSettings?.news_sponsored_ads_enabled ?? true,
    live_stream_ads_enabled: props.initialAdsSettings?.live_stream_ads_enabled ?? false,
    event_ticket_ads_enabled: props.initialAdsSettings?.event_ticket_ads_enabled ?? true,
});

const updateAdsSetting = () => {
    form.put(route('admin.settings.ads.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-3xl font-black">Ads Settings</h3>
            <p class="text-slate-500">Configure advertising IDs, sponsored placements, and ad availability across LinkUp.</p>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Ad Network Configuration</h3>
                <div class="grid grid-cols-1 gap-4">
                    <label class="font-bold text-slate-600">AdMob Native Ad ID
                        <input v-model="form.admob_native_ad_id" placeholder="AdMob Native Ad ID" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">AdMob Interstitial Ad ID
                        <input v-model="form.admob_interstitial_ad_id" placeholder="AdMob Interstitial Ad ID" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">AdMob Banner Ad ID
                        <input v-model="form.admob_banner_ad_id" placeholder="AdMob Banner Ad ID" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                    <label class="font-bold text-slate-600">AdMob Rewarded Video Ad ID
                        <input v-model="form.admob_rewarded_video_id" placeholder="AdMob Rewarded Video Ad ID" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3" />
                    </label>
                </div>
                <button @click="updateAdsSetting" :disabled="form.processing" class="mt-5 rounded-2xl bg-gradient-to-r from-purple-600 to-fuchsia-500 text-white px-5 py-3 font-black disabled:opacity-60">Update Ads Setting</button>
            </div>
            <div class="card rounded-3xl p-6">
                <h3 class="text-xl font-black mb-4">Ad Placement Controls</h3>
                <div class="space-y-3">
                    <label class="flex justify-between items-center rounded-2xl bg-slate-50 p-4 font-bold">Home Feed Ads<input v-model="form.home_feed_ads_enabled" type="checkbox" @change="updateAdsSetting" /></label>
                    <label class="flex justify-between items-center rounded-2xl bg-slate-50 p-4 font-bold">Marketplace Ads<input v-model="form.marketplace_ads_enabled" type="checkbox" @change="updateAdsSetting" /></label>
                    <label class="flex justify-between items-center rounded-2xl bg-slate-50 p-4 font-bold">News Sponsored Ads<input v-model="form.news_sponsored_ads_enabled" type="checkbox" @change="updateAdsSetting" /></label>
                    <label class="flex justify-between items-center rounded-2xl bg-slate-50 p-4 font-bold">Live Stream Ads<input v-model="form.live_stream_ads_enabled" type="checkbox" @change="updateAdsSetting" /></label>
                    <label class="flex justify-between items-center rounded-2xl bg-slate-50 p-4 font-bold">Event Ticket Ads<input v-model="form.event_ticket_ads_enabled" type="checkbox" @change="updateAdsSetting" /></label>
                </div>
            </div>
        </div>
    </div>
</template>
