<script setup lang="ts">
import { BadgeDollarSign, Clock, Eye, Newspaper, Plus } from 'lucide-vue-next';

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type Stat = {
    icon: typeof Newspaper;
    iconBg: string;
    iconColor: string;
    value: string;
    label: string;
};

const stats: Stat[] = [
    { icon: Newspaper, iconBg: 'bg-sky-100', iconColor: 'text-sky-600', value: '142', label: 'Published Stories' },
    { icon: Clock, iconBg: 'bg-amber-100', iconColor: 'text-amber-600', value: '18', label: 'Pending / Review' },
    { icon: Eye, iconBg: 'bg-green-100', iconColor: 'text-green-600', value: '1.34M', label: 'Monthly Views' },
    { icon: BadgeDollarSign, iconBg: 'bg-purple-100', iconColor: 'text-purple-600', value: '$28,950', label: 'Sponsored Revenue' },
];

type TopStory = {
    title: string;
    meta: string;
    status: 'Top Story' | 'Published' | 'Review' | 'Scheduled';
};

const topStories: TopStory[] = [
    { title: 'Bahamas Independence Weekend Events', meta: 'Bahamas • Events • 86,420 views • Published', status: 'Top Story' },
    { title: 'Jamaica Carnival Travel Guide', meta: 'Jamaica • Travel • 74,850 views • Published', status: 'Published' },
    { title: 'Caribbean Startups to Watch', meta: 'Regional • Business • 63,190 views • Review', status: 'Review' },
    { title: 'Trinidad Soca Weekend Preview', meta: 'Trinidad & Tobago • Entertainment • 51,880 views • Scheduled', status: 'Scheduled' },
];

const statusStyle: Record<TopStory['status'], string> = {
    'Top Story': 'bg-green-50 text-green-700',
    Published: 'bg-green-50 text-green-700',
    Review: 'bg-amber-50 text-amber-700',
    Scheduled: 'bg-blue-50 text-blue-700',
};

const regionActivity = [
    { name: 'Bahamas', views: '312K views' },
    { name: 'Jamaica', views: '248K views' },
    { name: 'Trinidad & Tobago', views: '191K views' },
    { name: 'Barbados / OECS', views: '128K views' },
    { name: 'Caribbean Diaspora', views: '461K views' },
];

const editorialSnapshot = [
    { value: '23', label: 'Drafts', bg: 'bg-slate-50' },
    { value: '18', label: 'Review', bg: 'bg-amber-50' },
    { value: '9', label: 'Scheduled', bg: 'bg-blue-50' },
    { value: '142', label: 'Published', bg: 'bg-green-50' },
    { value: '12', label: 'Sponsored', bg: 'bg-purple-50' },
];
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">Caribbean 360 News Management</h3>
                <p class="text-slate-500">Manage Caribbean news, diaspora stories, island updates, categories, regions, authors, sponsored placements, and publishing workflow.</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="emit('view-changed', 'newsCreateCommand')">
                <Plus class="h-4 w-4" /> Create Story
            </button>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div v-for="stat in stats" :key="stat.label" class="flex items-center gap-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid h-12 w-12 place-items-center rounded-2xl" :class="[stat.iconBg, stat.iconColor]">
                    <component :is="stat.icon" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-3xl font-black">{{ stat.value }}</h3>
                    <p class="text-sm text-slate-500">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-xl font-black">Top Stories</h3>
                <div class="space-y-3">
                    <div v-for="story in topStories" :key="story.title" class="flex justify-between rounded-2xl border border-slate-100 p-4">
                        <div>
                            <b>{{ story.title }}</b>
                            <p class="text-sm text-slate-500">{{ story.meta }}</p>
                        </div>
                        <span class="h-fit rounded-full px-3 py-1 text-xs font-black" :class="statusStyle[story.status]">{{ story.status }}</span>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-xl font-black">Region Activity</h3>
                <div class="space-y-3">
                    <div v-for="region in regionActivity" :key="region.name" class="flex justify-between rounded-2xl bg-slate-50 p-4">
                        <span>{{ region.name }}</span>
                        <b>{{ region.views }}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-xl font-black">Editorial Workflow Snapshot</h3>
            <div class="grid grid-cols-1 gap-4 text-center md:grid-cols-5">
                <div v-for="item in editorialSnapshot" :key="item.label" class="rounded-2xl p-4" :class="item.bg">
                    <b>{{ item.value }}</b>
                    <p class="text-sm text-slate-500">{{ item.label }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
