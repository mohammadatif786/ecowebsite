<template>
    <AuthenticatedLayout>

        <Head title="Find Matches" />
        <Card class="bg-transparent">
            <CardHeader>
                <Filter2 url="findmatch" :filters="props.filters" :caribbeanCountry="caribbeanCountry" />
            </CardHeader>
            <CardContent>
                <!-- When profiles exist -->
                <span v-if="gridItems.length > 0">
                    <section class="mx-2.5 sm:mx-5 md:mx-0">
                        <div class="mb-2.5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-5 gap-4">
                            <template v-for="(item, idx) in gridItems" :key="item._key">
                                <!-- Sponsored ad card -->
                                <!-- <SponsoredAdCard
                                    v-if="item._type === 'ad'"
                                    :ad="item"
                                    @dismiss="dismissAd(item.id)"
                                /> -->
                                <!-- Regular profile card -->
                                <UserProfileCardHome v-if="item._type === 'profile'" :more_photos="item.more_photos"
                                    :name="item.name" :age="item.age" :country="item.country"
                                    :caribbean_interest="item.caribbean_interest" :countryFlag="item.country_flag"
                                    :distance="item.distance" :gender="item.gender" :whyare="item.whyare" :id="item.id"
                                    :user="props.currentUser" :google_api_key="props.google_api_key"
                                    :avatar="item.avatar" :card-id="`cardid_${idx}`" :uid="item.uid"
                                    @like="handleLike(item)" @pass="handlePass(item)" @gift="handleGift(item)"
                                    @shuffle="handleShuffle(item)" />
                            </template>
                        </div>
                        <!-- pagination button -->
                        <div class="mt-4 flex justify-center"
                            v-if="props.users.next_page_url || props.users.prev_page_url">
                            <button class="bg-blue-700 mx-2 rounded px-4 py-2 text-white hover:bg-blue-300"
                                :disabled="!props.users.prev_page_url" @click="router.get(props.users.prev_page_url)"
                                v-if="props.users.prev_page_url">
                                Previous
                            </button>
                            <button class="bg-blue-700 mx-2 rounded px-4 py-2 text-white hover:bg-blue-300"
                                :disabled="!props.users.next_page_url" @click="router.get(props.users.next_page_url)"
                                v-if="props.users.next_page_url">
                                Next
                            </button>
                        </div>
                    </section>
                    <!-- When no profiles exist -->
                </span>
                <section v-else class="text-primary py-4 text-center">No record found</section>
            </CardContent>
        </Card>

        <!-- Gift Dialog -->
        <GiftDialog ref="giftDialogRef" :user="selectedUser" :balance="props.currentUser.coins" />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import Filter2 from '@/components/front/filter2.vue';
import GiftDialog from '@/components/front/GiftDialog.vue';
import { Card, CardContent, CardHeader } from '@/components/front/ui/card';
import UserProfileCardHome from '@/components/front/UserProfileCardHome.vue';
import SponsoredAdCard from '@/components/front/SponsoredAdCard.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface UserProfile {
    id: number;
    name: string;
    age: number;
    country: string;
    caribbean_interest: string;
    gender: string;
    avatar: string;
    whyare: string;
    city: string | null;
    distance: string;
    more_photos: string[];
    uid: string;
    country_flag: string;
}

interface Ad {
    id: number;
    name: string;
    headline?: string | null;
    description?: string | null;
    ad_type?: string | null;        // 'image' | 'video'
    image?: string | null;          // base64 data-URI or storage path
    video?: string | null;
    thumbnail?: string | null;       // video poster
    loop_video?: string | null;     // 'yes' | 'no'
    autoplay_sound?: string | null; // 'on' | 'off'
    cta_text?: string | null;
    brand_color?: string | null;
    www?: string | null;
    city?: string | null;
    state?: string | null;
    country?: string | null;
    status: boolean | number;
}

const props = defineProps<{
    users: any;
    filters: any;
    currentUser: any;
    google_api_key: string;
    caribbeanCountry: any;
    homePage: boolean;
    swipeAds?: Ad[];
}>();

const profiles = ref<UserProfile[]>([...(Array.isArray(props.users) ? props.users : props.users?.data || [])]);

const form = useForm<any>();
const giftDialogRef = ref();
const selectedUser = ref();

// Track dismissed ad IDs so they disappear from the grid
const dismissedAdIds = ref<Set<number>>(new Set());

const gridItems = computed(() => {
    const activeAds = (props.swipeAds ?? []).filter((a) => !dismissedAdIds.value.has(a.id));
    if (!activeAds.length) {
        return profiles.value.map((p, i) => ({ ...p, _type: 'profile' as const, _key: `profile-${p.uid}-${i}` }));
    }

    const result: Array<Record<string, any>> = [];
    let adIndex = 0;
    let slotPosition = 0;

    for (const profile of profiles.value) {
        slotPosition++;
        result.push({ ...profile, _type: 'profile' as const, _key: `profile-${profile.uid}-${slotPosition}` });

        if (slotPosition % 5 === 0) {
            const ad = activeAds[adIndex % activeAds.length];
            adIndex++;
            result.push({ ...ad, _type: 'ad' as const, _key: `ad-${ad.id}-${slotPosition}` });
        }
    }

    return result;
});

function dismissAd(adId: number) {
    dismissedAdIds.value = new Set([...dismissedAdIds.value, adId]);
}

const handleLike = (profile: UserProfile) => {
    form.post(route('frontend.profile.like', profile.id), {
        preserveScroll: true,
        onSuccess: () => {
            profiles.value = profiles.value.filter((p) => p.id !== profile.id);
        },
    });

    // router.get(props.users.next_page_url);
};

const handlePass = (profile: UserProfile) => {
    if (props.homePage) {
        form.post(route('frontend.profile.dislike', profile.id), {
            preserveScroll: true,
        });
    }

    // page.value = page.value - 1;
    // router.get(props.users.prev_page_url);
};

const handleGift = (profile: UserProfile) => {
    selectedUser.value = profile;
    giftDialogRef.value.open();
};
const handleShuffle = (profile: UserProfile) => {
    router.visit(route('frontend.home.matches'));
};
</script>
