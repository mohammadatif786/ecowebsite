<template>
    <div class="fade pb-20">
        <div class="rounded-3xl overflow-hidden mb-5" style="background:linear-gradient(135deg,#2563eb,#3b82f6)">
            <div class="p-5 text-white">
                <div class="flex items-center gap-3">
                    <span class="h-11 w-11 rounded-2xl bg-white/15 grid place-items-center text-lg font-black shrink-0">NL</span>
                    <div>
                        <h1 class="text-2xl font-black">Clubs &amp; Restaurants</h1>
                        <p class="text-white/80 text-sm font-bold mt-0.5">
                            Near you in {{ locationLabel }} &middot; powered by Google{{ isRefreshingLocation ? ' (updating location...)' : '' }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <button
                        @click="setTab('club')"
                        :class="['rounded-2xl py-2.5 font-black text-sm flex items-center justify-center gap-2 transition', tab === 'club' ? 'bg-white text-black' : 'bg-white/15 text-white hover:bg-white/25 text-black']"
                    >
                        Clubs Near By
                    </button>
                    <button
                        @click="setTab('restaurant')"
                        :class="['rounded-2xl py-2.5 font-black text-sm flex items-center justify-center gap-2 transition', tab === 'restaurant' ? 'bg-white text-black' : 'bg-white/15 text-white hover:bg-white/25 text-black']"
                    >
                        Restaurants Near By
                    </button>
                </div>
            </div>
        </div>

            <div v-if="currentVenues.length" class="grid grid-cols-2 gap-4 mb-4">
            <div
                v-for="(v, i) in currentVenues"
                :key="v.place_id || i"
                class="card overflow-hidden cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all"
                @click="openVenue(v)"
            >
                <div class="relative h-32 bg-slate-100">
                    <img :src="venueImage(v, i)" class="w-full h-full object-cover" />
                    <span class="absolute top-2 left-2 bg-black/60 text-white text-[11px] font-black px-2 py-1 rounded-full shadow-sm backdrop-blur-sm">
                        {{ tab === 'club' ? 'Club' : 'Restaurant' }}
                    </span>
                </div>
                <div class="p-3">
                    <p class="font-black text-sm leading-tight">{{ v.name }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ v.address || 'Address not available' }}</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="rounded-full bg-amber-50 text-amber-600 text-xs font-black px-2 py-1">
                            {{ ratingLabel(v.rating) }}
                        </span>
                        <span class="text-lkblue2 text-xs font-black">
                            {{ loadingPlaceId === v.place_id ? 'Loading...' : 'View ->' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card p-6 text-center mb-4">
            <p class="font-black text-slate-700">No {{ tab === 'club' ? 'clubs' : 'restaurants' }} found nearby.</p>
            <p class="text-sm font-semibold text-slate-400 mt-1">Try opening the full Google Maps search below.</p>
        </div>

        <a
            :href="openAllMapsLink"
            target="_blank"
            rel="noopener"
            class="btn btn-primary w-full py-3.5 flex items-center justify-center gap-2 shadow hover:shadow-md hover:-translate-y-0.5 transition"
        >
            Open all on Google Maps
        </a>

        <VenueModal ref="venueModalRef" />
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import MainLayout from '../../../layouts/new_front_layout/MainLayout.vue';
import VenueModal from '../../../components/new_frontend/modals/VenueModal.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    clubs: { type: Array, default: () => [] },
    restaurants: { type: Array, default: () => [] },
    swipeAds: { type: Array, default: () => [] },
    location: { type: Object, default: () => ({}) },
});

const tab = ref('club');
const venueModalRef = ref(null);
const loadingPlaceId = ref(null);
const imageSlideIndices = ref({});
const clubs = ref(props.clubs);
const restaurants = ref(props.restaurants);
const location = ref(props.location);
const isRefreshingLocation = ref(false);
let imageAutoSwipeTimer = null;

const currentVenues = computed(() => (tab.value === 'club' ? clubs.value : restaurants.value) || []);

const venueImage = (venue, index) => {
    const photos = Array.isArray(venue.photos) ? venue.photos.filter(Boolean) : [];
    if (!photos.length) {
        return venue?.image || `https://picsum.photos/seed/linkup-nightlife-${tab.value}-${index}/700/500`;
    }

    const key = venue.place_id || `${tab.value}-${index}`;
    const currentIndex = imageSlideIndices.value[key] ?? 0;
    return photos[currentIndex % photos.length] || venue?.image;
};

const startImageAutoSwipe = () => {
    stopImageAutoSwipe();
    if (currentVenues.value.length <= 0) {
        return;
    }

    imageAutoSwipeTimer = window.setInterval(() => {
        currentVenues.value.forEach((venue, idx) => {
            const photos = Array.isArray(venue.photos) ? venue.photos.filter(Boolean) : [];
            if (photos.length <= 1) {
                return;
            }

            const key = venue.place_id || `${tab.value}-${idx}`;
            imageSlideIndices.value[key] = ((imageSlideIndices.value[key] ?? 0) + 1) % photos.length;
        });
    }, 3000);
};

const stopImageAutoSwipe = () => {
    if (imageAutoSwipeTimer !== null) {
        clearInterval(imageAutoSwipeTimer);
        imageAutoSwipeTimer = null;
    }
};

onMounted(() => {
    startImageAutoSwipe();
    refreshForCurrentLocation();
});

onUnmounted(() => {
    stopImageAutoSwipe();
});

watch([tab, currentVenues], () => {
    imageSlideIndices.value = {};
    startImageAutoSwipe();
});

const locationLabel = computed(() => {
    return [location.value?.city, location.value?.country].filter(Boolean).join(', ') || 'your area';
});

const openAllMapsLink = computed(() => {
    const term = tab.value === 'club' ? 'nightclubs' : 'restaurants';
    return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(term + ' near ' + locationLabel.value);
});

const setTab = (value) => {
    tab.value = value;
};

const refreshForCurrentLocation = () => {
    if (!navigator.geolocation) {
        return;
    }

    isRefreshingLocation.value = true;

    navigator.geolocation.getCurrentPosition(
        async ({ coords }) => {
            try {
                const response = await axios.get(route('new_frontend.nightlife'), {
                    params: {
                        lat: coords.latitude,
                        lng: coords.longitude,
                    },
                    headers: { Accept: 'application/json' },
                });

                clubs.value = response.data.clubs || [];
                restaurants.value = response.data.restaurants || [];
                location.value = response.data.location || location.value;
            } catch (error) {
                // Preserve the server-rendered results if the refresh fails.
            } finally {
                isRefreshingLocation.value = false;
            }
        },
        () => {
            // Preserve the server-rendered fallback when permission is unavailable.
            isRefreshingLocation.value = false;
        },
        {
            enableHighAccuracy: false,
            timeout: 10000,
            maximumAge: 300000,
        },
    );
};

const ratingLabel = (rating) => {
    const value = Number(rating);
    return Number.isFinite(value) && value > 0 ? value.toFixed(1) + ' star' : 'N/A';
};

const openVenue = async (venue) => {
    if (!venueModalRef.value || !venue) return;

    loadingPlaceId.value = venue.place_id;

    try {
        const response = await axios.get(route('new_frontend.nightlife.detail', {
            kind: tab.value,
            placeId: venue.place_id,
        }), {
            params: { source: venue.source || 'google' },
        });

        venueModalRef.value.open({ ...venue, ...response.data }, tab.value);
    } catch (error) {
        venueModalRef.value.open(venue, tab.value);
    } finally {
        loadingPlaceId.value = null;
    }
};
</script>
