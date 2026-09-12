<script setup lang="ts">
import { computed } from 'vue';
import {
    Star, BookOpen, Image as ImageIcon, ShieldCheck, SlidersHorizontal,
    Flag, Shuffle, Flame, Clock, Bookmark, BookmarkCheck, Link as LinkIcon,
    Radio, FileText, Users
} from 'lucide-vue-next';

interface Story {
    id: string;
    title: string;
    summary: string;
    country: string;
    countryCode: string;
    region: string;
    category: string;
    sourceType: string;
    sourceName: string;
    publishedAt: string;
    trending: number;
    isBreaking: boolean;
    image_object: string | null;
    media?: any[];
    body?: string;
}

interface FeaturedAd {
    id: string;
    title: string;
    description: string;
    type: 'image' | 'video' | 'audio';
    mediaUrl: string;
    thumbnailUrl?: string;
    duration?: number;
    advertiser?: string;
    callToAction?: string;
    targetUrl?: string;
    isActive: boolean;
}

const props = defineProps<{
    topStory: Story | null;
    trendingStories: Story[];
    prefs: any;
    savedIds: string[];
    featuredAd?: FeaturedAd | null;
}>();

const emit = defineEmits<{
    openArticle: [id: string];
    openPrefs: [];
    quickOneCountry: [];
    shuffleTrending: [];
    toggleSave: [id: string];
}>();

const FLAG = (code: string) => {
    const map: Record<string, string> = {
        JM: "🇯🇲", BS: "🇧🇸", TT: "🇹🇹", BB: "🇧🇧", HT: "🇭🇹", DO: "🇩🇴", PR: "🇵🇷",
        AG: "🇦🇬", GD: "🇬🇩", LC: "🇱🇨", VC: "🇻🇨", KN: "🇰🇳", GY: "🇬🇾", SR: "🇸🇷",
        BR: "🇧🇷", CO: "🇨🇴", MX: "🇲🇽", AR: "🇦🇷", CL: "🇨🇱", PE: "🇵🇪", VE: "🇻🇪",
        PA: "🇵🇦", CR: "🇨🇷", GLB: "🌍"
    };
    return map[code] || "🏳️";
};

const timeAgo = (dateStr: string) => {
    const date = new Date(dateStr);
    const seconds = Math.floor((new Date().getTime() - date.getTime()) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + "y";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + "mo";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + "d";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + "h";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + "m";
    return Math.floor(seconds) + "s";
};

const summaryRegions = computed(() => {
    const r = [];
    if (props.prefs.regions.caribbean) r.push("Caribbean");
    if (props.prefs.regions.latam) r.push("Latin America");
    if (props.prefs.regions.global) r.push("Global");
    return r.length ? r.join(", ") : "None";
});

const summaryCountries = computed(() => {
    const count = props.prefs.countries?.length || 0;
    return count > 0 ? `${count} selected` : "None";
});

const summaryCats = computed(() => {
    const count = props.prefs.categories?.length || 0;
    return count === 8 ? "All" : (count > 0 ? `${count} selected` : "None");
});

const summarySources = computed(() => {
    const s = [];
    if (props.prefs.sources.rss) s.push("Feeds");
    if (props.prefs.sources.internal) s.push("Internal");
    return s.length ? s.join(" + ") : "None";
});

const isSaved = (id: string) => props.savedIds.includes(id);

const truncate = (text: string | undefined, length: number) => {
    if (!text) return '';
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};

</script>
<template>
    <section class="gridAuto">
        <!-- Main Column -->
        <div class="col-span-12 lg:col-span-8 card p-4">
            <!-- Top Story (always shown) -->
            <div v-if="topStory" class="flex items-start justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="pill" style="border-color:rgba(14,165,233,.35); background:rgba(14,165,233,.07);">
                            <Star :size="14" class="text-sky-500" /> Top Story
                        </div>
                        <div class="pill">
                            {{ FLAG(topStory.countryCode) }}
                            {{ topStory.countryCode === 'GLB' ? topStory.region : topStory.country }} •
                            {{ topStory.category }}
                        </div>
                    </div>
                    <div class="mt-2 text-[.95rem] sm:text-[1.1rem] font-black leading-tight">
                        {{ topStory.title }}
                    </div>
                    <div class="mt-2 font-normal muted">
                        {{ topStory.summary }}
                    </div>
                </div>
                <button @click="emit('openArticle', topStory.id)" class="btn btnPrimary shrink-0">
                    <BookOpen :size="18" /> Read
                </button>
            </div>

            <div class="mt-4 grid grid-cols-12 gap-3">
                <!-- Visual Display -->
                <div class="col-span-12 sm:col-span-7 overflow-hidden rounded-[18px] border border-slate-200">
                    <div v-if="topStory && topStory.image_object"
                        class="w-full h-full min-h-[220px] bg-slate-100 flex items-center justify-center">
                        <img :src="topStory.image_object" :alt="topStory.title" class="w-full h-full object-cover">
                    </div>
                    <div v-else class="imgPh p-4 h-full flex flex-col justify-center">
                        <div class="flex items-center justify-between mb-4">
                            <div class="font-black opacity-40">No Visual Provided</div>
                            <div class="pill">
                                <ImageIcon :size="14" /> Info
                            </div>
                        </div>
                        <div class="space-y-3 opacity-50">
                            <div class="skeleton h-4 w-10/12"></div>
                            <div class="skeleton h-4 w-8/12"></div>
                            <div class="skeleton h-32 w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Featured Ad (replaces Feed Settings) -->
                <div class="col-span-12 sm:col-span-5">
                    <div v-if="featuredAd?.isActive && featuredAd?.mediaUrl" class="card p-4 h-full">
                        <!-- Ad Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="font-black">Featured Ad</div>
                            <div class="pill" style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07);">
                                <Radio :size="14" class="text-red-500" /> Sponsored
                            </div>
                        </div>

                        <!-- Ad Content -->
                        <div class="space-y-4">
                            <div class="text-[.9rem] font-extrabold">
                                <div class="font-black text-[1rem]">{{ featuredAd.title }}</div>
                                <div class="text-slate-600 mt-1">{{ featuredAd.description }}</div>
                                <div class="text-[.8rem] text-slate-500 mt-1" v-if="featuredAd.advertiser">
                                    by {{ featuredAd.advertiser }}
                                </div>
                            </div>

                            <!-- Ad Media -->
                            <div class="ad-media-container-small">
                                <!-- Image Ad -->
                                <div v-if="featuredAd.type === 'image'" class="ad-image-small">
                                    <img :src="featuredAd.mediaUrl" :alt="featuredAd.title"
                                         class="w-full h-full object-cover rounded-lg" />
                                </div>

                                <!-- Video Ad -->
                                <div v-else-if="featuredAd.type === 'video'" class="ad-video-small">
                                    <video controls :poster="featuredAd.thumbnailUrl" class="w-full rounded-lg">
                                        <source :src="featuredAd.mediaUrl" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>

                                <!-- Audio Ad -->
                                <div v-else-if="featuredAd.type === 'audio'" class="ad-audio-small">
                                    <div class="bg-slate-50 rounded-lg p-3 text-center">
                                        <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-2">
                                            <Radio class="w-4 h-4 text-slate-500" />
                                        </div>
                                        <audio controls class="w-full">
                                            <source :src="featuredAd.mediaUrl" type="audio/mpeg">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                </div>
                            </div>

                            <!-- Call to Action -->
                            <div v-if="featuredAd.callToAction && featuredAd.targetUrl" class="flex justify-center">
                                <button @click="window.open(featuredAd.targetUrl, '_blank')"
                                        class="btn btnPrimary w-full">
                                    {{ featuredAd.callToAction }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Feed Summary (fallback when no ad) -->
                    <div v-else class="card p-4 h-full">
                        <div class="flex items-center justify-between">
                            <div class="font-black">Your Feed Settings</div>
                            <div class="pill">
                                <ShieldCheck :size="14" />
                            </div>
                        </div>
                        <div class="mt-2 text-[.9rem] font-extrabold muted">
                            Your selections control what appears below. Breaking strip stays visible regardless of
                            category filters.
                        </div>

                        <div class="mt-4 space-y-2 text-[.92rem] font-extrabold">
                            <div class="flex items-center justify-between">
                                <span class="muted">Regions</span><span class="font-black">{{ summaryRegions }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="muted">Countries</span><span class="font-black">{{ summaryCountries
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="muted">Categories</span><span class="font-black">{{ summaryCats }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="muted">Source Types</span><span class="font-black">{{ summarySources
                                }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button @click="emit('quickOneCountry')" class="btn w-full" title="Quick set: one country">
                                <Flag :size="16" /> Quick
                            </button>
                        </div>

                        <div class="mt-3 text-[.8rem] font-extrabold muted">
                            Pro tip: You can add your own internal stories via <b>Admin</b>.
                        </div>
                    </div>
                </div>

                <hr class="col-span-12 sm:col-span-12 my-2 border-slate-200">
                <div class="col-span-12 sm:col-span-12 text-[.9rem] leading-relaxed font-normal text-slate-700">
                    {{ truncate(topStory?.body, 300) }}
                </div>
            </div>
        </div>

        <!-- Right rail (Trending) -->
        <div class="col-span-12 lg:col-span-4 card p-4">
            <div class="flex items-center justify-between">
                <div class="font-black text-[1.05rem]">Trending Now</div>
                <button @click="emit('shuffleTrending')" class="btn" title="Shuffle">
                    <Shuffle :size="16" />
                </button>
            </div>

            <div class="mt-3 space-y-2">
                <div v-for="story in trendingStories.slice(0, 4)" :key="story.id"
                    class="card p-3 w-full text-left hover:glow transition cursor-pointer" style="box-shadow:none;"
                    @click="emit('openArticle', story.id)">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="pill">{{ FLAG(story.countryCode) }} {{ story.countryCode === 'GLB' ?
                                    story.region : story.country }}</span>
                                <span v-if="story.isBreaking" class="pill"
                                    style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07);">
                                    <div class="dotPulse" style="width:8px;height:8px;"></div> Breaking
                                </span>
                            </div>
                            <div class="mt-2 font-black text-[.75rem] leading-snug truncate">
                                {{ story.title }}
                            </div>
                            <div class="mt-1 text-[.65rem] font-normal muted flex items-center gap-2">
                                <Flame :size="10" class="text-orange-500" /> {{ Math.min(99, story.trending) }}
                                <span class="muted">•</span>
                                <Clock :size="10" /> {{ timeAgo(story.publishedAt) }}
                            </div>
                        </div>
                        <div class="shrink-0">
                            <button @click.stop="emit('toggleSave', story.id)" class="btn"
                                style="padding:.5rem .65rem;">
                                <BookmarkCheck v-if="isSaved(story.id)" :size="18" class="text-sky-500" />
                                <Bookmark v-else :size="18" />
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="!trendingStories.length" class="text-center font-extrabold muted py-4">
                    No trending items.
                </div>
            </div>
            <div
                class="mt-4 card p-4 flex flex-col items-center justify-center text-center space-y-2 bg-slate-50 border-slate-200">
                <div class="text-[.75rem] font-bold tracking-widest text-slate-400 uppercase">Advertisement</div>
                <div
                    class="w-full h-32 bg-slate-200 rounded-xl flex items-center justify-center text-slate-400 font-bold border border-slate-300 border-dashed">
                    Ad Space
                </div>
                <div class="text-[.8rem] font-bold text-slate-500">Support independent journalism.</div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.card {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 12px 26px rgba(2, 6, 23, .08);
    border: 1px solid rgba(148, 163, 184, .35);
}

.pill {
    border-radius: 999px;
    padding: .25rem .6rem;
    font-size: .75rem;
    font-weight: 900;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}

.btn {
    border-radius: 16px;
    font-weight: 900;
    padding: .75rem 1rem;
    display: inline-flex;
    gap: .6rem;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(148, 163, 184, .35);
    background: #fff;
    transition: transform .12s ease, filter .12s ease;
    user-select: none;
}

.btn:hover {
    transform: translateY(-1px);
    filter: brightness(1.02);
}

.btnPrimary {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    border-color: transparent;
    color: #fff;
}

.imgPh {
    background: linear-gradient(135deg, rgba(14, 165, 233, .16), rgba(34, 197, 94, .14));
    border: 1px dashed rgba(148, 163, 184, .7);
    border-radius: 18px;
}

.skeleton {
    background: linear-gradient(90deg, rgba(148, 163, 184, .10), rgba(148, 163, 184, .22), rgba(148, 163, 184, .10));
    background-size: 200% 100%;
    animation: shimmer 1.2s ease-in-out infinite;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, .25);
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

.gridAuto {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 12px;
}

.muted {
    color: #64748b;
}

.dotPulse {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, .6);
    animation: pulse 1.2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, .55);
    }

    70% {
        box-shadow: 0 0 0 12px rgba(239, 68, 68, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.glow {
    box-shadow: 0 0 0 4px rgba(14, 165, 233, .10);
}
</style>

<style scoped>
/* Featured Ad Styles */
.featured-ad {
    position: relative;
}

.ad-media-container {
    position: relative;
    width: 100%;
    min-height: 220px;
    background: #f8fafc;
    border-radius: 18px;
    overflow: hidden;
}

.ad-image {
    width: 100%;
    height: 100%;
    min-height: 220px;
    background: #e2e8f0;
}

.ad-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.ad-video {
    width: 100%;
    background: #1a1a1a;
    border-radius: 12px;
}

.ad-video video {
    width: 100%;
    height: auto;
    max-height: 400px;
    border-radius: 12px;
}

.ad-audio {
    width: 100%;
    background: #f8fafc;
    border-radius: 12px;
}

.ad-media-container-small {
    position: relative;
    width: 100%;
    min-height: 120px;
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
}

.ad-image-small {
    width: 100%;
    height: 120px;
    background: #e2e8f0;
}

.ad-image-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.ad-video-small {
    width: 100%;
    background: #1a1a1a;
    border-radius: 8px;
}

.ad-video-small video {
    width: 100%;
    height: auto;
    max-height: 150px;
    border-radius: 8px;
}

.ad-audio-small {
    width: 100%;
    background: #f8fafc;
    border-radius: 8px;
}
</style>