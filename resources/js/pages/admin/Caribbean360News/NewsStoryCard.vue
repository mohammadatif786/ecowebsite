<script setup lang="ts">
import { computed } from 'vue';
import { Bookmark, BookmarkCheck, BookOpen, Eye, Flame, Pencil, Radio } from 'lucide-vue-next';
import { categoryStyle, numFmt, readTimeFor, type SponsorAd, type Story } from './types';

const props = defineProps<{
    story: Story;
    ad: SponsorAd | null;
    bookmarked: boolean;
}>();

const emit = defineEmits<{
    (event: 'open'): void;
    (event: 'edit'): void;
    (event: 'toggle-bookmark'): void;
}>();

const readTime = computed(() => readTimeFor(props.story));
const viewsLabel = computed(() => numFmt(props.story.views));
const locationLabel = computed(() => (props.story.region === 'Global' ? '🌐 Global' : `${props.story.flag} ${props.story.country}`));
</script>

<template>
    <article class="flex cursor-pointer flex-col overflow-hidden rounded-3xl bg-white shadow-sm" @click="emit('open')">
        <div class="h-44 overflow-hidden">
            <div
                class="h-full w-full bg-slate-100 bg-cover bg-center transition-transform duration-500 hover:scale-105"
                :style="{ backgroundImage: `url('${story.img}')` }"
            />
        </div>
        <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-2">
                <div class="flex flex-wrap gap-2">
                    <span v-if="story.breaking" class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1 text-xs font-black text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Breaking
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black" :class="categoryStyle(story.category)">
                        {{ story.category }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black"
                        :class="story.region === 'Global' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-700'"
                    >
                        {{ locationLabel }}
                    </span>
                </div>
                <div class="flex shrink-0 gap-1">
                    <button class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50" title="Edit" @click.stop="emit('edit')">
                        <Pencil class="h-4 w-4" />
                    </button>
                    <button
                        class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 hover:bg-slate-50"
                        :class="bookmarked ? 'text-sky-600' : 'text-slate-500'"
                        title="Bookmark"
                        @click.stop="emit('toggle-bookmark')"
                    >
                        <BookmarkCheck v-if="bookmarked" class="h-4 w-4" />
                        <Bookmark v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <h5 class="mt-3 font-black leading-snug text-slate-900">{{ story.title }}</h5>
            <p class="mt-2 flex-1 text-sm text-slate-500">{{ story.summary }}</p>

            <div v-if="ad" class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[11px] font-black tracking-widest text-slate-400">SPONSORED</span>
                    <span class="text-[11px] font-bold text-slate-400">{{ ad.sponsor }}</span>
                </div>
                <img v-if="ad.format === 'image'" :src="ad.media" class="h-32 w-full rounded-2xl bg-slate-100 object-cover" alt="" />
                <video v-else-if="ad.format === 'video'" :src="ad.media" class="h-32 w-full rounded-2xl bg-slate-900 object-cover" controls preload="none" @click.stop />
                <div v-else class="rounded-2xl bg-slate-100 p-3">
                    <div class="mb-2 flex items-center gap-2 text-slate-400"><Radio class="h-4 w-4" /><span class="text-xs font-black">AUDIO</span></div>
                    <audio :src="ad.media" class="w-full" controls preload="none" @click.stop />
                </div>
                <button class="mt-2 w-full rounded-xl bg-sky-500 px-3 py-2 text-xs font-black text-white" @click.stop="emit('open')">{{ ad.cta || 'Learn More' }}</button>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
                    <span class="flex items-center gap-1"><BookOpen class="h-3.5 w-3.5" /> {{ readTime }}</span>
                    <span class="flex items-center gap-1"><Eye class="h-3.5 w-3.5" /> {{ viewsLabel }}</span>
                    <span class="flex items-center gap-1"><Flame class="h-3.5 w-3.5" /> {{ story.fire }}</span>
                </div>
                <button class="flex items-center gap-2 rounded-2xl bg-sky-500 px-4 py-2 text-sm font-black text-white" @click.stop="emit('open')">
                    <BookOpen class="h-4 w-4" /> Read
                </button>
            </div>
        </div>
    </article>
</template>
