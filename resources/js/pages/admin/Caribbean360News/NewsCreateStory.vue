<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Eraser, Paperclip, Send } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

type Region = 'Caribbean' | 'Latin America' | 'Global';

type Country = {
    key: string;
    label: string;
    region: Region;
};

const countries: Country[] = [
    { key: 'bahamas', label: 'Bahamas', region: 'Caribbean' },
    { key: 'barbados', label: 'Barbados', region: 'Caribbean' },
    { key: 'jamaica', label: 'Jamaica', region: 'Caribbean' },
    { key: 'trinidad', label: 'Trinidad & Tobago', region: 'Caribbean' },
    { key: 'guyana', label: 'Guyana', region: 'Caribbean' },
    { key: 'dominican', label: 'Dominican Republic', region: 'Caribbean' },
    { key: 'colombia', label: 'Colombia', region: 'Latin America' },
    { key: 'brazil', label: 'Brazil', region: 'Latin America' },
    { key: 'mexico', label: 'Mexico', region: 'Latin America' },
    { key: 'panama', label: 'Panama', region: 'Latin America' },
    { key: 'miami', label: 'Miami Diaspora', region: 'Global' },
    { key: 'new-york', label: 'New York Diaspora', region: 'Global' },
    { key: 'global', label: 'Global', region: 'Global' },
];

const categories = [
    'Breaking News',
    'Politics',
    'Business & Economy',
    'Entertainment',
    'Sports',
    'Culture',
    'Technology',
    'Faith',
    'Events',
    'Environment',
];

const authors = ['Caribbean Desk', 'Ramon Persaud', 'Sasha Baptiste', 'Lucia Mendez', 'Global Desk', 'Energy Desk'];

const imageInputRef = ref<HTMLInputElement | null>(null);
const videoInputRef = ref<HTMLInputElement | null>(null);
const audioInputRef = ref<HTMLInputElement | null>(null);

const emptyForm = () => ({
    headline: '',
    summary: '',
    body: '',
    region: 'Caribbean' as Region,
    country: 'Bahamas',
    category: 'Events',
    breaking: false,
    author: 'Caribbean Desk',
    source: 'LinkUp Caribbean 360 Desk',
    imageFile: null as File | null,
    videoFile: null as File | null,
    audioFile: null as File | null,
});

const form = ref(emptyForm());

const countryOptions = computed(() => countries.filter((country) => country.region === form.value.region));

const onFile = (field: 'imageFile' | 'videoFile' | 'audioFile', event: Event) => {
    const target = event.target as HTMLInputElement;
    form.value[field] = target.files?.[0] ?? null;
};

const clearForm = () => {
    form.value = emptyForm();
    if (imageInputRef.value) imageInputRef.value.value = '';
    if (videoInputRef.value) videoInputRef.value.value = '';
    if (audioInputRef.value) audioInputRef.value.value = '';
};

const publish = () => {
    const headline = form.value.headline.trim();
    if (!headline) {
        toast.error('Please enter a headline.');
        return;
    }

    const wasBreaking = form.value.breaking;
    toast.success(`Story published to the Caribbean 360 feed${wasBreaking ? ' (Breaking).' : '.'}`);
    clearForm();
};

watch(
    () => form.value.region,
    () => {
        const first = countryOptions.value[0];
        if (first && !countryOptions.value.some((country) => country.label === form.value.country)) {
            form.value.country = first.label;
        }
    },
);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4 rounded-3xl bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
            <div>
                <h3 class="text-2xl font-black">Create Story</h3>
                <p class="text-sm text-sky-100">Compose a Caribbean 360 story — same flow as Admin Upload + add media</p>
            </div>
            <button class="flex items-center gap-2 rounded-2xl bg-white px-5 py-2.5 font-black text-sky-600" @click="publish">
                <Send class="h-4 w-4" /> Publish
            </button>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Headline</span>
                        <input v-model="form.headline" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" placeholder="Write a strong headline..." />
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Summary (2–3 lines)</span>
                        <textarea v-model="form.summary" rows="4" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Short summary for the cards..."></textarea>
                    </label>
                </div>

                <div class="rounded-3xl bg-sky-50 p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="flex items-center gap-2 font-black"><Paperclip class="h-4 w-4" /> Attach Media</h4>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-black">Stored locally</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="block">
                            <span class="mb-1 block text-xs font-black text-slate-600">Images</span>
                            <input ref="imageInputRef" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onFile('imageFile', $event)" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-black text-slate-600">Videos</span>
                            <input ref="videoInputRef" type="file" accept="video/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onFile('videoFile', $event)" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-black text-slate-600">Audio</span>
                            <input ref="audioInputRef" type="file" accept="audio/*" class="w-full rounded-2xl border border-slate-200 bg-white px-2 py-2 text-xs" @change="onFile('audioFile', $event)" />
                        </label>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Full Story (Optional - uses template if empty)</span>
                        <textarea v-model="form.body" rows="8" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Write the full story."></textarea>
                    </label>
                </div>
            </div>

            <div class="space-y-4">
                <div class="space-y-4 rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Region</span>
                        <select v-model="form.region" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option>Caribbean</option>
                            <option>Latin America</option>
                            <option>Global</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Country</span>
                        <select v-model="form.country" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="country in countryOptions" :key="country.key">{{ country.label }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Category</span>
                        <select v-model="form.category" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="category in categories" :key="category">{{ category }}</option>
                        </select>
                    </label>
                    <div class="flex items-start justify-between gap-3 rounded-2xl bg-rose-50 p-4">
                        <div>
                            <h5 class="font-black">Mark as Breaking</h5>
                            <p class="mt-1 text-sm text-slate-500">Breaking appears in the top strip and shows regardless of filters.</p>
                        </div>
                        <input v-model="form.breaking" type="checkbox" class="mt-1 h-6 w-6 accent-rose-500" />
                    </div>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Author</span>
                        <select v-model="form.author" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                            <option v-for="author in authors" :key="author">{{ author }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-black text-slate-600">Source Name</span>
                        <input v-model="form.source" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" />
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 font-black" @click="clearForm">
                            <Eraser class="h-4 w-4" /> Clear
                        </button>
                        <button class="flex items-center justify-center gap-2 rounded-2xl bg-sky-600 px-4 py-3 font-black text-white" @click="publish">
                            <Send class="h-4 w-4" /> Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
