<template>
    <Modal ref="modalRef" maxWidth="max-w-5xl" padding="p-0">
        <div v-if="event" class="w-full">
            <div class="relative h-100 overflow-hidden bg-slate-100">
                <img :src="event.image_url" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-20 blur-xl" aria-hidden="true" />
                <img :src="event.image_url" class="relative h-full w-full object-contain rounded-t-2xl" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <button @click="close"
                    class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/50 text-white grid place-items-center hover:bg-black/70 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <span class="absolute bottom-3 left-4 text-white text-xs font-black rounded-full px-2.5 py-1 shadow"
                    :style="{ background: catColor }">{{ event.category?.name || event.category }}</span>
            </div>

            <div class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-2xl font-black">{{ event.title }}</h3>
                        <p class="text-slate-500 font-semibold mt-1">{{ fmtDate(event.date) }}, 2026<span
                                v-if="isOnline"> · Online</span></p>
                        <div class="flex gap-2 mt-2">
                            <span class="chip">{{ event.category?.name || event.category }}</span>
                            <span class="chip font-black text-lkblue2">{{ priceDisplay }}</span>
                            <a v-if="event.event_details?.x || event.event_details?.twitter"
                                :href="event.event_details.x || event.event_details.twitter" target="_blank" rel="noopener"
                                class="chip social-chip social-chip--x" aria-label="Open X profile" title="X">
                                X
                            </a>

                            <a v-if="event.event_details?.instagram" :href="event.event_details.instagram" target="_blank"
                                rel="noopener" class="chip social-chip social-chip--instagram"
                                aria-label="Open Instagram profile" title="Instagram">
                                IG
                            </a>

                            <a v-if="event.event_details?.facebook" :href="event.event_details.facebook" target="_blank"
                                rel="noopener" class="chip social-chip social-chip--facebook"
                                aria-label="Open Facebook profile" title="Facebook">
                                f
                            </a>

                            <a v-if="event.event_details?.tiktok" :href="event.event_details.tiktok" target="_blank"
                                rel="noopener" class="chip social-chip social-chip--tiktok"
                                aria-label="Open TikTok profile" title="TikTok">
                                ♪
                            </a>

                            <a v-if="event.event_details?.linkedin" :href="event.event_details.linkedin" target="_blank"
                                rel="noopener" class="chip social-chip social-chip--linkedin"
                                aria-label="Open LinkedIn profile" title="LinkedIn">
                                in
                            </a>
                        </div>
                    </div>

                    <div class="flex gap-2 shrink-0">
                        <button @click="toggleFav" class="btn btn-ghost px-4 py-3">
                            <i data-lucide="heart"
                                :class="['w-4 h-4 transition-colors', isFav ? 'text-rose-500 fill-rose-500' : 'text-slate-400']"></i>
                        </button>
                        <button @click="shareEvent" class="btn btn-ghost px-4 py-3">
                            <i data-lucide="share-2" class="w-4 h-4 text-slate-400"></i>
                        </button>

                        <template v-if="isOnline">
                            <button v-if="event.liveSessionId" @click="watchLive"
                                class="btn btn-primary px-5 py-3">Watch Live</button>
                            <button v-else @click="watchLiveGen" class="btn btn-primary px-5 py-3 font-black">Watch
                                Live</button>
                        </template>
                        <template v-else>
                            <button @click="buyTickets" :disabled="event.ticket_count === 0"
                                class="btn btn-primary px-5 py-3 font-black disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">
                                Buy Tickets • {{ priceDisplay }}
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
                    <FactCard label="Event Type" :val="event.type || (isOnline ? 'Online' : 'Public')" />
                    <FactCard label="Going" :val="event.event_audience_count ?? goingCount" />
                    <FactCard label="Seating" :val="event.event_details?.seating_plan == 1 ? 'Yes' : 'No'" />
                    <FactCard label="Reviews" :val="event.event_details?.enable_views  == 1 ? 'Enabled' : 'Disabled'" />
                </div>

                <div class="grid lg:grid-cols-3 gap-5">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="card p-4">
                            <p class="font-black mb-1">About Event</p>
                            <div class="text-sm text-slate-600 prose prose-slate max-w-none"
                                v-html="event.description || event.desc || 'No description provided yet.'"></div>
                        </div>

                        <div v-if="event.disclaimer" class="card p-4">
                            <p class="font-black mb-1">Disclaimer</p>
                            <!-- <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ event.disclaimer }}</p> -->
                            <p class="text-sm text-slate-600 whitespace-pre-wrap" v-html="event.disclaimer"></p>
                        </div>

                        <div v-if="galleryMedia.length" class="card p-4">
                            <p class="font-black mb-2">Media Gallery</p>
                            <div class="grid grid-cols-2 gap-2">
                                <template v-for="(m, idx) in galleryMedia" :key="idx">
                                    <video v-if="m.type === 'video'" :src="m.url"
                                        class="w-full h-32 object-cover rounded-xl" controls muted></video>
                                    <img v-else :src="m.url" class="w-full h-32 object-cover rounded-xl"
                                        @click="openPreview(m)" />
                                </template>
                            </div>
                        </div>

                        <!-- Artist Section -->
                        <div v-if="artists.length" class="card p-4">
                            <p class="font-black mb-3">Artists</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div v-for="(artist, idx) in artists" :key="idx"
                                    class="flex flex-col items-center text-center p-2 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div
                                        class="w-16 h-16 rounded-full overflow-hidden mb-2 ring-2 ring-white shadow-sm">
                                        <img :src="artist.image" class="w-full h-full object-cover"
                                            @error="$event.target.src = '/assets/images/default-avatar.png'" />
                                    </div>
                                    <p class="text-xs font-black truncate w-full">{{ artist.name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold">Performer</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="sponsors.length" class="card p-4">
                            <p class="font-black mb-3">Sponsors</p>
                            <div class="flex flex-wrap gap-4">
                                <div v-for="s in sponsors" :key="s.name" class="w-40">
                                    <img :src="s.image" class="w-full h-28 object-cover rounded-xl" />
                                    <p class="text-sm font-bold mt-2">{{ s.name }} • Sponsor</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="event.event_details.enable_views == 1" class="card p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-black">Reviews &amp; Ratings</p>
                                <span class="text-lkblue2 font-black text-lg">{{ avgRating }} <span
                                        class="text-amber-400">★</span>
                                    <span class="text-slate-400 text-xs font-bold">{{ reviews.length }} review{{
                                        reviews.length === 1 ?
                                            '' : 's' }}</span></span>
                            </div>

                            <div class="space-y-2 mb-3">
                                <template v-if="reviews.length">
                                    <div v-for="(r, i) in reviews" :key="i" class="rounded-xl bg-slate-50 p-3">
                                        <p class="font-black text-sm flex items-center gap-1.5">{{ r.name }} <span
                                                class="text-amber-400 text-xs">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5
                                                    - r.rating) }}</span></p>
                                        <p class="text-sm text-slate-600 mt-0.5">{{ r.text }}</p>
                                    </div>
                                </template>
                                <p v-else class="text-slate-400 text-sm font-semibold text-center py-3">No reviews yet.
                                    Be the first to
                                    review this event!</p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-3 space-y-2 bg-slate-50/50">
                                <div class="flex gap-1 text-2xl">
                                    <button v-for="n in [1, 2, 3, 4, 5]" :key="n" @click="reviewRating = n"
                                        type="button" class="leading-none transition focus:outline-none"
                                        :class="n <= reviewRating ? 'text-amber-400' : 'text-slate-300'">
                                        {{ n <= reviewRating ? '★' : '☆' }} </button>
                                </div>
                                <input v-model="reviewName" placeholder="Your name"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue bg-white" />
                                <textarea v-model="reviewText" rows="2" placeholder="Share your experience…"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-lkblue bg-white"></textarea>
                                <button @click="submitReview" class="btn btn-primary w-full py-2.5 text-sm">Submit
                                    Review</button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="card p-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-full bg-violet-100 text-violet-700 grid place-items-center font-black shrink-0 overflow-hidden">
                                    <img v-if="event.organizer_image_url" :src="event.organizer_image_url"
                                        class="w-full h-full object-cover" />
                                    <span v-else>{{ orgNameStr.slice(0, 1) }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-sm truncate">{{ orgNameStr }}</p>
                                    <p class="text-[11px] text-slate-400">Organizer · typically responds within 24h</p>
                                    <p class="text-[11px] text-lkblue2 font-bold">{{ orgFollowerCount }} followers</p>
                                </div>
                            </div>
                            <button @click="toggleFollowOrg"
                                :class="['btn w-full mt-3 py-2 text-sm transition', followingOrg ? 'btn-ghost' : 'btn-primary']">
                                {{ followingOrg ? 'Following ✓' : 'Follow' }}
                            </button>
                        </div>

                        <div class="card p-4 space-y-2">
                            <p class="font-black text-sm mb-1 text-slate-400 uppercase tracking-wider">Event Facts</p>
                            <FactCard label="Event Type" :val="event.type || 'Public'" />
                            <FactCard label="Website" :val="event.website || contactInfo.website" />
                            <FactCard label="Email" :val="event.email || contactInfo.email" />
                            <FactCard label="Phone" :val="event.phone || contactInfo.phone" />
                            <FactCard label="Location" :val="fullLocation" />
                        </div>

                        <div class="card p-4">
                            <p class="font-black text-sm mb-2">Location</p>
                            <p class="text-sm text-slate-600 mb-2">{{ fullLocation }}</p>
                            <template v-if="isOnline">
                                <div
                                    class="rounded-2xl bg-blue-50 p-3 text-sm font-bold text-blue-700 flex items-center gap-2">
                                    <i data-lucide="video" class="w-4 h-4"></i>Online event — no physical venue.
                                </div>
                            </template>
                            <template v-else>
                                <iframe
                                    :src="'https://maps.google.com/maps?q=' + encodeURIComponent(fullLocation) + '&output=embed'"
                                    class="w-full h-44 rounded-2xl border-0" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(fullLocation)"
                                    target="_blank" rel="noopener"
                                    class="mt-2 flex items-center justify-center gap-1.5 text-lkblue2 text-xs font-black">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Open in Google Maps
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-if="moreEvents.length" class="card p-4 mt-5">
                    <p class="font-black mb-3">More events from {{ orgNameStr }}</p>
                    <div class="relative group">
                        <button @click="scrollCarousel(-1)"
                            class="hidden group-hover:grid absolute -left-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white border border-slate-200 shadow place-items-center transition">
                            <i data-lucide="chevron-left" class="w-4 h-4 text-slate-600"></i>
                        </button>
                        <div ref="moreScrollRef" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-1">
                            <EventCard v-for="e in moreEvents" :key="e.id" :event="e" :favs="favs" @open="openNewEvent"
                                @toggleFav="toggleFavGlobal" />
                        </div>
                        <button @click="scrollCarousel(1)"
                            class="hidden group-hover:grid absolute -right-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white border border-slate-200 shadow place-items-center transition">
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
    <Teleport to="body">
        <div v-if="selectedMedia" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/85 p-4"
            @click.self="closePreview">
            <button class="absolute right-5 top-4 text-3xl text-white" @click="closePreview">
                ×
            </button>

            <video v-if="selectedMedia.type === 'video'" :src="selectedMedia.url"
                class="max-h-[85vh] max-w-full rounded-xl" controls autoplay />

            <img v-else :src="selectedMedia.url" class="max-h-[85vh] max-w-full rounded-xl object-contain"
                alt="Preview" />
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '../ui/Modal.vue';
import EventCard from '../cards/EventCard.vue';
import { DB, PIC } from '../MockDataStore';

const props = defineProps({
    favs: { type: Object, default: () => ({}) },
    allEvents: { type: Array, default: () => [] }
});

const emit = defineEmits(['toggleFav', 'buy']);

const EV_CATCOLOR = {
    'Party/Fete': '#f97316', 'Wellness & Spa': '#ec4899', 'Cookouts/Food': '#f59e0b',
    'Arts': '#8b5cf6', 'Music': '#2563eb', 'Sports': '#22c55e', 'Online': '#0ea5e9', 'Business': '#64748b'
};

const modalRef = ref(null);
const moreScrollRef = ref(null);
const event = ref(null);

const reviewRating = ref(0);
const reviewName = ref('');
const reviewText = ref('');
const localReviews = ref([]);


const selectedMedia = ref(null)

const openPreview = (media) => {
    selectedMedia.value = media
}

const closePreview = () => {
    selectedMedia.value = null
}

// Media Gallery Logic
const resolveMediaUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    return '/storage/' + path.replace(/^\/?storage\//, '').replace(/^\//, '');
};

const isVideo = (url) => {
    const videoExts = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.wmv', '.flv', '.mkv'];
    return videoExts.some(ext => url.toLowerCase().endsWith(ext));
};

const galleryMedia = computed(() => {
    const media = [];
    if (event.value?.image_url) {
        media.push({ url: event.value.image_url, type: 'image' });
    }

    const detailGallery = event.value?.event_details?.image_gallery;
    if (Array.isArray(detailGallery)) {
        detailGallery.forEach(item => {
            if (item) {
                const url = resolveMediaUrl(item);
                media.push({ url, type: isVideo(url) ? 'video' : 'image' });
            }
        });
    }

    const seen = new Set();
    return media.filter(item => {
        if (seen.has(item.url)) return false;
        seen.add(item.url);
        return true;
    }).slice(0, 6);
});

const artists = computed(() => {
    const details = event.value?.event_details;
    if (!details || !Array.isArray(details.artists)) return [];

    const names = details.artists;
    const images = details.artist_image || [];

    return names.map((name, i) => ({
        name: name,
        image: resolveMediaUrl(images[i]) || '/assets/images/default-avatar.png'
    }));
});

// Computed event properties
const catColor = computed(() => {
    const name = event.value?.category?.name || event.value?.category;
    const EV_CATCOLOR_LOCAL = {
        'Wellness and Spa': '#10b981', 'Wellness & Spa': '#10b981',
        'Cookouts/Food': '#f97316'
    };
    return EV_CATCOLOR_LOCAL[name] || '#e11d48';
});

const isOnline = computed(() => event.value?.kind === 'online' || event.value?.type?.toLowerCase() === 'online');
const isFav = computed(() => event.value?.auth_user_favorite !== null);

const priceDisplay = computed(() => {
    if (!event.value) return '';
    if (event.value.ticket_count === 0) return 'No Tickets';
    if (event.value.max_price === 0) return 'FREE';

    const min = Number(event.value.min_price);
    const max = Number(event.value.max_price);

    return min === max ? money(min) : `${money(min)} - ${money(max)}`;
});

const fullLocation = computed(() => {

    if (!event.value) return '';
    const parts = [event.value.venue].filter(Boolean);
    return parts.length ? parts.join(', ') : 'TBA';
});

// Contact info generation
const orgNameStr = computed(() => {
    const obj = event.value;
    if (!obj) return 'LinkUp';
    const name = obj.organizer_name || (typeof obj.organizer === 'string' ? obj.organizer : obj.organizer?.organizer_name);
    return name || 'LinkUp';
});

const orgFollowerCount = computed(() => {
    return event.value?.organizer?.followers_count ?? 0;
});
const contactInfo = computed(() => {
    const name = orgNameStr.value;
    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '') || 'linkup';
    return {
        website: `https://book${slug}.as.me/`,
        email: event.value?.email || `${slug}@gmail.com`,
        phone: event.value?.phone || '(' + (200 + (name.length * 7) % 700) + ') ' + (100 + (name.length * 13) % 900) + '-' + (1000 + (name.length * 97) % 9000)
    };
});

// Follow org logic
const followingOrg = computed(() => {
    return event.value?.organizer?.is_following ?? false;
});
const toggleFollowOrg = () => {
    if (!event.value?.organizer_id) {
        if (window.toast) window.toast('Organizer ID missing');
        return;
    }

    router.post(route('new_frontend.events.toggle-follow-organizer'), {
        organizer_id: event.value.organizer_id
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (event.value.organizer) {
                event.value.organizer.is_following = !followingOrg.value;
                event.value.organizer.followers_count += event.value.organizer.is_following ? 1 : -1;
            }
            if (window.toast) window.toast(followingOrg.value ? 'Following organizer' : 'Unfollowed organizer');
        },
        onError: () => {
            if (window.toast) window.toast('Follow toggle error');
        }
    });
};

// Going count
const goingCount = computed(() => {
    if (!event.value) return 0;
    return DB.get('lk_ticket_orders', []).filter(o => o.event_id === event.value.id).length;
});

// Reviews logic
const loadReviews = () => {
    if (!event.value) { localReviews.value = []; return; }

    // Load reviews from backend API using axios to avoid page navigation
    axios.get(route('new_frontend.events.reviews.index', event.value.id))
        .then(response => {
            localReviews.value = response.data.reviews || [];
        })
        .catch(() => {
            // Fallback to local storage if API fails
            localReviews.value = DB.get('lk_event_reviews_' + event.value.id, []);
        });
};
const reviews = computed(() => localReviews.value);
const avgRating = computed(() => {
    if (!reviews.value.length) return '0.0';
    return (reviews.value.reduce((s, r) => s + r.rating, 0) / reviews.value.length).toFixed(1);
});
const submitReview = () => {
    if (!reviewRating.value) { if (window.toast) window.toast('Pick a star rating'); return; }
    const txt = reviewText.value.trim();
    if (!txt) { if (window.toast) window.toast('Add a short review'); return; }
    const name = reviewName.value.trim() || 'Guest';

    // Submit to backend API
    router.post(route('new_frontend.events.reviews.store', event.value.id), {
        rating: reviewRating.value,
        review_text: txt,
        reviewer_name: name
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            reviewRating.value = 0;
            reviewText.value = '';
            reviewName.value = '';
            loadReviews();
            if (window.toast) window.toast('⭐ Review submitted');
        },
        onError: (errors) => {
            if (errors.error) {
                if (window.toast) window.toast(errors.error);
            } else if (window.toast) {
                window.toast('Failed to submit review');
            }
        }
    });
};

// More events logic
const moreEvents = computed(() => {
    if (!event.value) return [];
    const currentOrgName = orgNameStr.value;
    let more = props.allEvents.filter(x => {
        const xOrg = x.organizer_name || (typeof x.organizer === 'string' ? x.organizer : x.organizer?.organizer_name);
        return xOrg === currentOrgName && x.id !== event.value.id;
    });
    if (!more.length) more = props.allEvents.filter(x => x.id !== event.value.id).slice(0, 6);
    return more;
});

// Sponsors logic
const sponsors = computed(() => {
    if (!event.value || !Array.isArray(event.value.sponsors)) return [];

    return event.value.sponsors
        .filter(s => s.status == 1 || s.status === 'active')
        .map(s => ({
            name: s.name,
            image: resolveMediaUrl(s.sponsor_image_object || s.image_object),
            description: s.description
        }));
});

// Formatting
const money = (n) => '$' + Number(n).toFixed(2);
const fmtDate = (d) => {
    const dt = new Date(d || new Date());
    return dt.toLocaleString('en-US', { month: 'short', day: 'numeric' });
};
const pic = (idx) => PIC('ev' + (event.value?.id || '1') + String.fromCharCode(97 + idx), 700, 500);

const open = (e) => {
    event.value = e;
    reviewRating.value = 0;
    reviewName.value = '';
    reviewText.value = '';
    loadReviews();
    if (modalRef.value) {
        modalRef.value.open();
        nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    }
};

const close = () => {
    if (modalRef.value) modalRef.value.close();
};

const openNewEvent = (e) => {
    open(e);
};

const toggleFav = () => {
    if (event.value) emit('toggleFav', event.value.id);
};

const toggleFavGlobal = (id) => {
    emit('toggleFav', id);
};

const shareEvent = () => {
    const url = window.location.origin + '/frontend/event/show/' + event.value.id;
    navigator.clipboard.writeText(url).then(() => {
        if (window.toast) window.toast('🔗 Link copied');
    });
};

const watchLive = () => {
    close();
    if (window.openLiveOverlay) window.openLiveOverlay(event.value.liveSessionId);
};

const watchLiveGen = () => {
    close();
    if (window.toast) window.toast('▶️ Watching ' + event.value.title);
    try { router.get(route('new_frontend.live')); } catch { window.location.href = '/live'; }
};

const buyTickets = () => {
    close();
    emit('buy', event.value);
};

const scrollCarousel = (dir) => {
    if (moreScrollRef.value) {
        moreScrollRef.value.scrollBy({ left: dir * 300, behavior: 'smooth' });
    }
};

defineExpose({ open, close });
</script>

<script>
// Inline component for facts
import { h } from 'vue';
const FactCard = (props) => {
    return h('div', { class: 'rounded-2xl border border-slate-200 p-3' }, [
        h('p', { class: 'text-[10px] font-black text-slate-400 uppercase' }, props.label),
        h('p', { class: 'font-black text-sm mt-0.5' }, props.val)
    ]);
};
FactCard.props = ['label', 'val'];
</script>

<style scoped>
.chip.social-chip {
    display: inline-grid;
    min-width: 42px;
    place-items: center;
    border-color: transparent;
    color: #fff;
    font-weight: 900;
}

.chip.social-chip--x {
    background: #000;
}

.chip.social-chip--instagram {
    background: linear-gradient(135deg, #833ab4 0%, #e1306c 50%, #f77737 100%);
}

.chip.social-chip--facebook {
    background: #1877f2;
}

.chip.social-chip--tiktok {
    background: #010101;
    text-shadow: -1px -1px 0 #25f4ee, 1px 1px 0 #fe2c55;
}

.chip.social-chip--linkedin {
    background: #0a66c2;
}

.chip.social-chip:hover {
    border-color: transparent;
    color: #fff;
    filter: brightness(1.12);
    transform: translateY(-1px);
}
</style>
