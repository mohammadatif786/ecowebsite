<script setup lang="ts">
import { computed } from 'vue';
import {
    Clock, Flame, Tag, FileText, Radio, Paperclip,
    Bookmark, BookmarkCheck, BookOpen, Map, MapPin, Globe2,
    SlidersHorizontal, X
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
    breakingExpiresAt?: string | null;
    image_object: string | null;
    media?: any[];
}

const props = defineProps<{
    stories: Story[];
    prefs: any;
    savedIds: string[];
    sortMode: 'newest' | 'trending';
}>();

const emit = defineEmits<{
    openArticle: [id: string];
    toggleSave: [id: string];
    openPrefs: [];
    'update:sortMode': [val: 'newest' | 'trending'];
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
    let interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + "h";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + "m";
    return Math.floor(seconds) + "s";
};

const isSaved = (id: string) => props.savedIds.includes(id);

const topForYou = computed(() => props.stories.slice(0, 6));

const caribStories = computed(() =>
    props.stories.filter(s => s.region === 'Caribbean').slice(0, 6)
);

const latamStories = computed(() =>
    props.stories.filter(s => s.region === 'Latin America').slice(0, 6)
);

const globalStories = computed(() =>
    props.stories.filter(s => s.region === 'Global').slice(0, 6)
);
</script>

<template>
    <div class="space-y-12">
        <!-- Section: Top Stories for You -->
        <section class="space-y-4">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <div class="text-[1.1rem] font-black">Top Stories for You</div>
                    <div class="muted font-extrabold text-[.9rem]">Based on your selected countries + categories</div>
                </div>
                <div class="flex gap-2">
                    <button @click="emit('update:sortMode', 'newest')" class="btn"
                        :class="{ btnSoft: sortMode === 'newest' }">
                        <Clock :size="16" /> Newest
                    </button>
                    <button @click="emit('update:sortMode', 'trending')" class="btn"
                        :class="{ btnSoft: sortMode === 'trending' }">
                        <Flame :size="16" /> Trending
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-3">
                <template v-if="topForYou.length">
                    <div v-for="story in topForYou" :key="story.id" class="col-span-12 sm:col-span-6 lg:col-span-3">
                        <div class="card overflow-hidden h-full flex flex-col">
                            <div v-if="story.image_object"
                                class="w-full h-100px bg-slate-100 border-b border-slate-100">
                                <img :src="story.image_object" class="w-full h-full object-cover"
                                    style="height: 20rem; width:100%">
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <span v-if="story.isBreaking" class="pill"
                                            style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07);">
                                            <div class="dotPulse" style="width:8px;height:8px;"></div> Breaking
                                        </span>
                                        <span v-else class="pill">
                                            <Tag :size="12" /> {{ story.category }}
                                        </span>

                                        <span class="pill"><span>{{ FLAG(story.countryCode) }}</span> {{
                                            story.countryCode
                                                === 'GLB' ? story.region : story.country }}</span>

                                        <span class="pill"
                                            :style="story.sourceType === 'internal' ? 'border-color:rgba(34,197,94,.35); background:rgba(34,197,94,.07);' : 'border-color:rgba(14,165,233,.35); background:rgba(14,165,233,.07);'">
                                            <FileText v-if="story.sourceType === 'internal'" :size="12" />
                                            <Radio v-else :size="12" />
                                            {{ story.sourceType === 'internal' ? 'Internal' : 'LinkUp' }}
                                        </span>
                                    </div>
                                    <button @click.stop="emit('toggleSave', story.id)" class="btn"
                                        style="padding:.55rem .7rem;">
                                        <BookmarkCheck v-if="isSaved(story.id)" :size="16" class="text-sky-500" />
                                        <Bookmark v-else :size="16" />
                                    </button>
                                </div>

                                <div class="mt-3 font-black text-[.95rem] leading-snug">
                                    {{ story.title }}
                                </div>
                                <div class="mt-2 font-normal muted text-[.85rem]">
                                    {{ story.summary }}
                                </div>

                                <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 text-[.85rem] font-extrabold muted">
                                        <Clock :size="14" /> {{ timeAgo(story.publishedAt) }}
                                        <span class="muted">•</span>
                                        <Flame :size="14" /> {{ Math.min(99, story.trending) }}
                                    </div>
                                    <button @click="emit('openArticle', story.id)" class="btn btnPrimary"
                                        style="padding:.6rem .85rem;">
                                        <BookOpen :size="16" /> Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="col-span-12">
                    <div class="card p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-black text-[1.05rem]">Nothing to show</div>
                                <div class="mt-1 font-extrabold muted">No stories match your filters. Try broad mode or
                                    select more
                                    countries.</div>
                            </div>
                            <button @click="emit('openPrefs')" class="btn btnPrimary">
                                <SlidersHorizontal :size="16" /> Preferences
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Caribbean Wide -->
        <section class="space-y-4">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <div class="text-[1.1rem] font-black">Caribbean Wide</div>
                    <div class="muted font-extrabold text-[.9rem]">Big stories across the region</div>
                </div>
                <div class="pill"><Map :size="14" /> Caribbean</div>
            </div>

            <div class="grid grid-cols-12 gap-3">
                <template v-if="caribStories.length">
                    <div v-for="story in caribStories" :key="story.id" class="col-span-12 sm:col-span-6 lg:col-span-3">
                        <div class="card overflow-hidden h-full flex flex-col">
                            <div v-if="story.image_object"
                                class="w-full h-100px bg-slate-100 border-b border-slate-100">
                                <img :src="story.image_object" class="w-full h-full object-cover"
                                    style="height: 20rem; width:100%">
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <span v-if="story.isBreaking" class="pill"
                                            style="border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.07);">
                                            <div class="dotPulse" style="width:8px;height:8px;"></div> Breaking
                                        </span>
                                        <span v-else class="pill">
                                            <Tag :size="12" /> {{ story.category }}
                                        </span>
                                        <span class="pill"><span>{{ FLAG(story.countryCode) }}</span> {{
                                            story.countryCode
                                                === 'GLB' ? story.region : story.country }}</span>
                                    </div>
                                    <button @click.stop="emit('toggleSave', story.id)" class="btn"
                                        style="padding:.55rem .7rem;">
                                        <BookmarkCheck v-if="isSaved(story.id)" :size="16" class="text-sky-500" />
                                        <Bookmark v-else :size="16" />
                                    </button>
                                </div>
                                <div class="mt-3 font-black text-[.95rem] leading-snug">{{ story.title }}</div>
                                <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 text-[.85rem] font-extrabold muted">
                                        <Clock :size="14" /> {{ timeAgo(story.publishedAt) }}
                                    </div>
                                    <button @click="emit('openArticle', story.id)" class="btn btnPrimary"
                                        style="padding:.6rem .85rem;">
                                        <BookOpen :size="16" /> Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="col-span-12">
                    <div class="card p-5">
                        <div class="muted font-extrabold">No Caribbean stories found.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Latin America -->
        <section class="space-y-4">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <div class="text-[1.1rem] font-black">Latin America</div>
                    <div class="muted font-extrabold text-[.9rem]">A broader view beyond the islands</div>
                </div>
                <div class="pill">
                    <MapPin :size="14" /> Latin America
                </div>
            </div>

            <div class="grid grid-cols-12 gap-3">
                <template v-if="latamStories.length">
                    <div v-for="story in latamStories" :key="story.id" class="col-span-12 sm:col-span-6 lg:col-span-3">
                        <div class="card overflow-hidden h-full flex flex-col">
                            <div v-if="story.image_object"
                                class="w-full h-100px bg-slate-100 border-b border-slate-100">
                                <img :src="story.image_object" class="w-full h-full object-cover"
                                    style="height: 20rem; width:100%">
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <span class="pill"><span>{{ FLAG(story.countryCode) }}</span> {{
                                            story.countryCode
                                                === 'GLB' ? story.region : story.country }}</span>
                                    </div>
                                    <button @click.stop="emit('toggleSave', story.id)" class="btn"
                                        style="padding:.55rem .7rem;">
                                        <BookmarkCheck v-if="isSaved(story.id)" :size="16" class="text-sky-500" />
                                        <Bookmark v-else :size="16" />
                                    </button>
                                </div>
                                <div class="mt-3 font-black text-[.95rem] leading-snug">{{ story.title }}</div>
                                <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                    <button @click="emit('openArticle', story.id)" class="btn btnPrimary"
                                        style="padding:.6rem .85rem;">
                                        <BookOpen :size="16" /> Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div v-else class="col-span-12">
                    <div class="card p-5">
                        <div class="muted font-extrabold">No Latin America stories found.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Global 360 -->
        <section class="space-y-4">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <div class="text-[1.1rem] font-black">Global 360</div>
                    <div class="muted font-extrabold text-[.9rem]">Only shows if you enabled Global</div>
                </div>
                <div class="pill">
                    <Globe2 :size="14" /> Global
                </div>
            </div>

            <div class="grid grid-cols-12 gap-3">
                <template v-if="prefs.regions.global">
                    <template v-if="globalStories.length">
                        <div v-for="story in globalStories" :key="story.id"
                            class="col-span-12 sm:col-span-6 lg:col-span-3">
                            <div class="card overflow-hidden h-full flex flex-col">
                                <div v-if="story.image_object"
                                    class="w-full h-100px bg-slate-100 border-b border-slate-100">
                                    <img :src="story.image_object" class="w-full h-full object-cover"
                                        style="height: 20rem; width:100%">
                                </div>
                                <div class="p-4 flex flex-col flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="pill"><span>{{ FLAG(story.countryCode) }}</span> {{
                                            story.countryCode
                                                === 'GLB' ? story.region : story.country }}</span>
                                        <button @click.stop="emit('toggleSave', story.id)" class="btn"
                                            style="padding:.55rem .7rem;">
                                            <BookmarkCheck v-if="isSaved(story.id)" :size="16" class="text-sky-500" />
                                            <Bookmark v-else :size="16" />
                                        </button>
                                    </div>
                                    <div class="mt-3 font-black text-[.95rem] leading-snug">{{ story.title }}</div>
                                    <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                        <button @click="emit('openArticle', story.id)" class="btn btnPrimary"
                                            style="padding:.6rem .85rem;">
                                            <BookOpen :size="16" /> Read
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="col-span-12">
                        <div class="card p-5">Global is enabled, but no stories match yet.</div>
                    </div>
                </template>
                <div v-else class="col-span-12">
                    <div class="card p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-black text-[1.05rem]">Global is off</div>
                                <div class="mt-1 font-extrabold muted">Turn it on in Preferences to see Global 360
                                    stories.
                                </div>
                            </div>
                            <button @click="emit('openPrefs')" class="btn btnPrimary">
                                <SlidersHorizontal :size="16" /> Preferences
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
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

.btnSoft {
    background: linear-gradient(135deg, rgba(14, 165, 233, .10), rgba(34, 197, 94, .10));
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
</style>