<template>
    <article
        ref="cardRef"
        class="relative flex h-[350px] flex-col overflow-hidden rounded-[30px] shadow-lg cursor-pointer select-none bg-[#111111]"
        @click="handleClick"
    >
        <!-- ── Top Media Section ─────────────────────────────────────────── -->
        <div class="relative flex-1 overflow-hidden bg-gray-800">
            <!-- ── VIDEO ad ────────────────────────────────────────────────── -->
            <video
                v-if="isVideo && ad.video"
                ref="videoRef"
                :src="ad.video"
                :poster="ad.thumbnail ?? undefined"
                :loop="ad.loop_video === 'yes'"
                :muted="ad.autoplay_sound !== 'on'"
                autoplay
                playsinline
                class="absolute inset-0 h-full w-full object-cover"
                preload="metadata"
            />

            <!-- ── IMAGE ad ───────────────────────────────────────────────── -->
            <img
                v-else-if="adImage"
                :src="adImage"
                :alt="ad.name"
                class="absolute inset-0 h-full w-full object-cover"
                loading="lazy"
                draggable="false"
            />

            <!-- ── Fallback (no image / video) ────────────────────────────── -->
            <div
                v-else
                class="absolute inset-0"
                :style="{ backgroundColor: ad.brand_color || '#4ade80' }"
            />
            
            <!-- ── CTA Overlay ───────────────────────────────────────────────────── -->
            <transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCtaOverlay && ad.www"
                    class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                >
                    <a
                        :href="ad.www"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-6 py-3 rounded-full font-bold text-white shadow-lg transition-transform hover:scale-105"
                        :style="{ backgroundColor: ad.brand_color || '#22d3ee' }"
                        @click.stop="handleVisit"
                    >
                        {{ ad.cta_text || 'Visit Website' }}
                    </a>
                </div>
            </transition>

            <div v-if="isVideo" class="absolute top-3 right-3 rounded-full bg-black/50 px-2 py-1">
                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
        </div>

        <!-- ── Bottom Info Panel (Black) ────────────────────────────────── -->
        <div class="flex flex-col justify-between bg-[#111111] px-5 py-4 min-h-[130px]">
            <div>
                <!-- SPONSORED badge -->
                <span class="inline-block rounded-full bg-[#22d3ee] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                    SPONSORED - {{ ad.category || 'AD' }}
                </span>

                <!-- Title -->
                <h3 class="mt-2 text-lg font-bold leading-tight text-white line-clamp-1">
                    {{ ad.headline || ad.name }}
                </h3>

                <!-- Location & Visit link -->
                <p class="mt-1 text-xs text-gray-400 line-clamp-1">
                    <span v-if="location">{{ location }} - </span>
                    <a
                        v-if="ad.www"
                        :href="ad.www"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-white hover:underline transition-all"
                        @click.stop="handleVisit"
                    >
                        {{ ad.cta_text || 'Visit Website' }} &rarr;
                    </a>
                </p>
            </div>

            <!-- Action Buttons (X and Heart) -->
            <div class="mt-4 flex items-center justify-between">
                <!-- Swipe Left (Dismiss) -->
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-red-500/20 text-red-500 hover:bg-red-500/30 transition-colors"
                    title="Dismiss (Swipe Left)"
                    @click.stop="handleDismiss"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Swipe Right (Visit) -->
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-[#22d3ee]/20 text-[#22d3ee] hover:bg-[#22d3ee]/30 transition-colors"
                    title="Visit Website (Swipe Right)"
                    @click.stop="handleVisit"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </button>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

interface Ad {
    id: number;
    name: string;
    headline?: string | null;
    description?: string | null;
    ad_type?: string | null;       // 'image' | 'video'
    image?: string | null;         // base64 data-URI or storage path
    video?: string | null;         // base64 data-URI or storage path
    thumbnail?: string | null;     // poster for video
    loop_video?: string | null;    // 'yes' | 'no'
    autoplay_sound?: string | null;// 'on' | 'off'
    max_duration?: number | null;  // max video duration in seconds
    cta_timing?: string | null;     // 'immediate' | 'end' | 'after_5s' | 'after_10s'
    cta_text?: string | null;
    brand_color?: string | null;
    www?: string | null;
    city?: string | null;
    state?: string | null;
    country?: string | null;
    status: boolean | number;
    category?: string | null;
}

const props = defineProps<{ ad: Ad }>();
const emit  = defineEmits<{ dismiss: [] }>();

const cardRef = ref<HTMLElement | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const showCtaOverlay = ref(false);
const maxDurationTimer = ref<number | null>(null);
const ctaTimingTimer = ref<number | null>(null);

// ── Helpers ────────────────────────────────────────────────────────────────
const isVideo = computed(() => props.ad.ad_type === 'video');
const appUrl = import.meta.env.VITE_APP_URL;
/**
 * Resolve ad image: if it's already a data-URI use as-is;
 * if it's a storage path prefix with /storage/; null if absent.
 */
const adImage = computed<string | null>(() => {
    const img = props.ad.image;
    if (!img) return null;
    if (img.startsWith('data:') || img.startsWith('http')) return img;
    return `${appUrl}${img}`;
});

const location = computed(() => {
    const parts = [props.ad.city, props.ad.state].filter(Boolean);
    return parts.join(', ');
});

// ── Impression tracking via IntersectionObserver ───────────────────────────
let impressionFired = false;
let observer: IntersectionObserver | null = null;

onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => {
            if (!impressionFired && entries[0]?.isIntersecting) {
                impressionFired = true;
                track('impression');
                observer?.disconnect();
            }
        },
        { threshold: 0.5 },
    );
    if (cardRef.value) observer.observe(cardRef.value);

    // Setup video duration and CTA timing
    if (isVideo.value && videoRef.value) {
        setupVideoControls();
    } else if (!isVideo.value) {
        // For image ads, show CTA immediately or based on timing
        setupCtaTiming();
    }
});

onUnmounted(() => {
    observer?.disconnect();
    if (maxDurationTimer.value) clearTimeout(maxDurationTimer.value);
    if (ctaTimingTimer.value) clearTimeout(ctaTimingTimer.value);
});

// ── Fire-and-forget tracker ────────────────────────────────────────────────
function track(eventType: 'impression' | 'click' | 'swipe_left') {
    axios
        .post(`/api/ads/${props.ad.id}/track`, { event_type: eventType })
        .catch(() => { /* silent — never block UI */ });
}

// ── Interaction handlers ───────────────────────────────────────────────────
function handleClick() {
    if (props.ad.www) {
        track('click');
        window.open(props.ad.www, '_blank', 'noopener,noreferrer');
    }
}

function handleVisit() {
    track('click'); // href already handles navigation
    if (props.ad.www) {
        window.open(props.ad.www, '_blank', 'noopener,noreferrer');
    }
}

function handleDismiss() {
    track('swipe_left');
    emit('dismiss');
}

// ── Video controls ───────────────────────────────────────────────────────────
function setupVideoControls() {
    const video = videoRef.value;
    if (!video) return;

    // Handle max duration
    if (props.ad.max_duration && props.ad.max_duration > 0) {
        maxDurationTimer.value = window.setTimeout(() => {
            if (props.ad.loop_video !== 'yes') {
                video.pause();
            }
            // Show CTA when max duration is reached
            showCtaOverlay.value = true;
        }, props.ad.max_duration * 1000);
    }

    // Handle CTA timing
    if (props.ad.cta_timing) {
        if (props.ad.cta_timing === 'immediate') {
            showCtaOverlay.value = true;
        } else if (props.ad.cta_timing === 'end') {
            video.addEventListener('ended', () => {
                showCtaOverlay.value = true;
            });
        } else if (props.ad.cta_timing === 'after_5s') {
            ctaTimingTimer.value = window.setTimeout(() => {
                showCtaOverlay.value = true;
            }, 5000);
        } else if (props.ad.cta_timing === 'after_10s') {
            ctaTimingTimer.value = window.setTimeout(() => {
                showCtaOverlay.value = true;
            }, 10000);
        }
    }
}

// ── CTA timing for image ads ───────────────────────────────────────────────────
function setupCtaTiming() {
    if (!props.ad.cta_timing) return;

    if (props.ad.cta_timing === 'immediate') {
        showCtaOverlay.value = true;
    } else if (props.ad.cta_timing === 'after_5s') {
        ctaTimingTimer.value = window.setTimeout(() => {
            showCtaOverlay.value = true;
        }, 5000);
    } else if (props.ad.cta_timing === 'after_10s') {
        ctaTimingTimer.value = window.setTimeout(() => {
            showCtaOverlay.value = true;
        }, 10000);
    }
    // 'end' timing doesn't apply to image ads
}
</script>
