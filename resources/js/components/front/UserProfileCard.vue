<template>
    <article class="relative overflow-hidden rounded-[26px] text-white shadow-xl select-none isolate"
        style="height: 409px">
        <!-- ── Background Image Carousel ── -->
        <Carousel class="absolute inset-0 h-full w-full z-0 [&>div]:h-full" :opts="{ loop: true }" :plugins="[autoplay]"
            @init-api="(api: any) => (carouselApi = api)">
            <CarouselContent class="h-full w-full flex m-0">
                <CarouselItem v-for="(src, i) in photos" :key="`${i}-${src}`"
                    class="w-full h-full min-w-full min-h-full flex-[0_0_100%] p-0">
                    <Link class="block h-full w-full"
                        :href="route('frontend.linkup.user.details', { slug: name, user: uid })">
                        <img :src="src || 'https://picsum.photos/800/600'" :alt="`Photo ${i + 1} of ${name ?? 'User'}`"
                            class="h-full w-full object-cover object-center transform scale-105" loading="lazy"
                            draggable="false" decoding="async" />
                    </Link>
                </CarouselItem>
            </CarouselContent>
        </Carousel>

        <!-- ── Gradient overlay (bottom-heavy) ── -->
        <div class="absolute top-3 left-3 z-30">
            <img v-if="countryFlag" :src="countryFlag" :alt="caribbean_interest"
                class="h-6 w-6 rounded-sm shadow-md ring-1 ring-white/30" />
        </div>
        <div class="absolute inset-0 z-10 pointer-events-none"
            style="background: linear-gradient(to top, rgba(10,6,30,0.92) 0%, rgba(10,6,30,0.45) 45%, rgba(0,0,0,0.08) 100%)" />

        <!-- ── TOP ROW: Premium badge + photo counter ── -->
        <div class="absolute top-3 left-3 right-3 z-20 flex items-center justify-between">

            <!-- Premium badge -->
            <div v-if="isPremium"
                class="flex items-center gap-1.5 rounded-full bg-purple-600 px-3 py-1 text-[11px] font-bold shadow-lg">
                <svg class="h-3.5 w-3.5 fill-yellow-300 shrink-0" viewBox="0 0 24 24">
                    <path d="M5 16L3 5l5.5 5L12 2l3.5 8L21 5l-2 11H5zm2 3h10v1a1 1 0 01-1 1H8a1 1 0 01-1-1v-1z" />
                </svg>
                <span>Premium</span>
            </div>
            <div v-else />

            <!-- Photo counter pill -->
            <div
                class="flex items-center gap-2 rounded-full bg-black/55 px-2.5 py-1 text-[11px] font-semibold backdrop-blur-sm">
                <span>{{ currentSlide + 1 }}/{{ photos.length }}</span>
                <div class="flex items-center gap-[3px]">
                    <span v-for="(_, i) in photos.slice(0, 5)" :key="i"
                        class="block rounded-full bg-white transition-all duration-200" :class="i === currentSlide
                            ? 'w-[12px] h-[6px]'
                            : 'w-[6px] h-[6px] opacity-40'" />
                </div>
            </div>
        </div>

        <!-- ── BOTTOM CONTENT ── -->
        <div class="absolute bottom-0 left-0 right-0 z-20 px-4 pb-3">
            <!-- Name + Age + Verified badge -->
            <div class="flex items-center gap-2 mb-[3px]">
                <h3 class="text-[19px] font-extrabold leading-tight tracking-tight drop-shadow">
                    {{ name }}, {{ age }}
                </h3>
                <span
                    class="flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full bg-blue-500 shadow-md">
                    <svg class="h-3 w-3 fill-white" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                    </svg>
                </span>
            </div>

            <!-- Distance -->
            <p class="mb-1.5 flex items-center gap-1 text-[12px] text-white/75 font-medium">
                <svg class="h-3.5 w-3.5 fill-current shrink-0" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                </svg>
                <span>{{ distance ?? 'Unknown' }} km away</span>
            </p>

            <!-- Interest / Why tag -->
            <div
                class="mb-2.5 inline-flex items-center gap-1.5 rounded-full bg-purple-900/80 px-3 py-[4px] text-[11px] font-bold backdrop-blur-sm border border-purple-500/20">
                <span>🎉</span>
                <span>{{ whyare }}</span>
            </div>

            <!-- ── All 5 Action buttons ── -->
            <div class="flex items-center justify-around px-1">

                <!-- Shuffle 🔀 -->
                <button class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-white/90 shadow-md
                           transition-transform duration-150 hover:scale-110 active:scale-90" title="Shuffle"
                    @click.prevent.stop="handleClick('shuffle')">
                    <svg class="h-[18px] w-[18px] fill-gray-500" viewBox="0 0 24 24">
                        <path
                            d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z" />
                    </svg>
                </button>

                <!-- Pass ✕ -->
                <button class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-white/90 shadow-md
                           transition-transform duration-150 hover:scale-110 active:scale-90" title="Pass"
                    @click.prevent.stop="handleClick('pass')">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#9ca3af" stroke-width="2.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Gift 🎁 — centre raised -->
                <button class="flex h-[52px] w-[52px] -mt-3 items-center justify-center rounded-full shadow-lg border-[3px] border-white
                           transition-transform duration-150 hover:scale-110 active:scale-90"
                    style="background: linear-gradient(135deg, #a855f7, #7c3aed);" title="Send Gift"
                    @click.prevent.stop="handleClick('gift')">
                    <svg class="h-[22px] w-[22px] fill-white" viewBox="0 0 24 24">
                        <path
                            d="M20 6h-2.18c.07-.31.18-.6.18-.93C18 3.37 16.63 2 14.93 2c-.97 0-1.76.42-2.35 1.09L12 3.77l-.58-.68C10.83 2.42 10.04 2 9.07 2 7.37 2 6 3.37 6 5.07c0 .33.11.62.18.93H4c-1.11 0-2 .89-2 2v3c0 .55.45 1 1 1h1v7c0 1.1.89 2 2 2h12c1.11 0 2-.9 2-2v-7h1c.55 0 1-.45 1-1V8c0-1.11-.89-2-2-2zm-7 0h-2V5.07C11 4.48 11.48 4 12.07 4h-.07C12.59 4 13 4.41 13 4.93V6zm-4 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm8 14H7v-7h10v7z" />
                    </svg>
                </button>

                <!-- Like ♥ -->
                <button class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-white/90 shadow-md
                           transition-transform duration-150 hover:scale-110 active:scale-90" title="Like"
                    @click.prevent.stop="handleClick('like')">
                    <svg class="h-[18px] w-[18px] fill-rose-500" viewBox="0 0 24 24">
                        <path
                            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                </button>

                <!-- Message 💬 -->
                <!-- <button class="flex h-[42px] w-[42px] items-center justify-center rounded-full bg-white/90 shadow-md
                           transition-transform duration-150 hover:scale-110 active:scale-90" title="Message"
                    @click.prevent.stop="handleClick('message')">
                    <svg class="h-[18px] w-[18px] fill-[#379ce8]" viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" />
                    </svg>
                </button> -->

            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { Carousel, CarouselContent, CarouselItem } from '@/components/front/ui/carousel';
import { Link } from '@inertiajs/vue3';
import Autoplay from 'embla-carousel-autoplay';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    id: number;
    name: string | null;
    age: number | null;
    distance: string;
    matchPercentage?: number;
    whyare: string;
    country: string;
    caribbean_interest: string;
    isPremium?: boolean;
    gender: string | null;
    cardId: string;
    avatar: string;
    more_photos: string | string[];
    uid: string;
    user?: any;
    google_api_key?: string;
    countryFlag: string;
}

const props = defineProps<Props>();

// ── Carousel API → track current slide index ─────────────────────────────────
const carouselApi = ref<any>(null);
const currentSlide = ref(0);

watch(carouselApi, (api) => {
    if (!api) return;
    currentSlide.value = api.selectedScrollSnap();
    api.on('select', () => {
        currentSlide.value = api.selectedScrollSnap();
    });
    api.on('reInit', () => {
        currentSlide.value = api.selectedScrollSnap();
    });
});

// ── Normalise photos list ─────────────────────────────────────────────────────
const normalizedPhotos = computed<string[]>(() => {
    const mp = props.more_photos;
    if (Array.isArray(mp)) return mp;
    if (typeof mp === 'string' && mp.trim()) {
        try {
            const parsed = JSON.parse(mp);
            if (Array.isArray(parsed)) return parsed;
        } catch {
            return [mp];
        }
    }
    return [];
});

const photos = computed<string[]>(() => {
    if (normalizedPhotos.value.length) {
        return normalizedPhotos.value.map((p) => `/storage/${p}`);
    }
    return props.avatar ? [props.avatar] : ['https://picsum.photos/800/600'];
});

// ── Autoplay (one instance per card) ─────────────────────────────────────────
const autoplay = Autoplay({
    delay: 7000 + Math.floor(Math.random() * 500),
    stopOnMouseEnter: true,
    stopOnInteraction: false,
});

// ── Emits ─────────────────────────────────────────────────────────────────────
const emit = defineEmits<{
    like: [];
    pass: [];
    shuffle: [];
    message: [];
    gift: [];
}>();

const actionLabels: Record<'like' | 'pass' | 'shuffle' | 'message' | 'gift', string> = {
    like: 'You liked this user ❤️',
    pass: 'You passed 👎',
    shuffle: 'Reloading another profile 🔄',
    message: 'Opening chat 💬',
    gift: 'Send a gift to someone 🎁',
};

function handleClick(action: 'like' | 'pass' | 'shuffle' | 'message' | 'gift') {
    emit(action);
    toast.success(actionLabels[action]);
}
</script>
