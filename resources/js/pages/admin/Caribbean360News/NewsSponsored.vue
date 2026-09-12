<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { BadgeDollarSign, Crosshair, Eye, Image as ImageIcon, Megaphone, Pencil, Plus, Radio, Trash2, Video, X } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const emit = defineEmits<{
    (event: 'view-changed', id: string): void;
}>();

type AdFormat = 'image' | 'video' | 'audio';
type TargetType = 'story' | 'category' | 'region' | 'all';
type AdStatus = 'Active' | 'Scheduled' | 'Paused';

type SponsorAd = {
    id: string;
    sponsor: string;
    title: string;
    body: string;
    format: AdFormat;
    media: string;
    cta: string;
    targetType: TargetType;
    target: string;
    package: string;
    value: number;
    status: AdStatus;
    impressions: number;
    clicks: number;
};

const storyOptions = [
    { value: 'st1', label: 'Bahamas Independence Weekend Events' },
    { value: 'st2', label: 'Jamaica Carnival Travel Guide' },
    { value: 'st3', label: 'Caribbean Startups to Watch' },
    { value: 'st4', label: 'Trinidad Soca Weekend Preview' },
    { value: 'st5', label: 'Barbados Crop Over Road March Watch' },
    { value: 'st6', label: 'Guyana Energy Corridor Update' },
];

const categoryOptions = ['Breaking News', 'Politics', 'Business & Economy', 'Entertainment', 'Sports', 'Culture', 'Technology', 'Faith', 'Events', 'Environment'];
const regionOptions = ['Caribbean', 'Latin America', 'Global'];

const ads = ref<SponsorAd[]>([
    { id: 'ad1', sponsor: 'Island Audio', title: 'Relaxing Caribbean Sounds', body: 'Let the soothing sounds of steel drums and ocean waves transport you to paradise. Perfect for meditation, work, or just unwinding.', format: 'audio', media: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', cta: 'Download App', targetType: 'story', target: 'st1', package: 'Featured Audio', value: 1800, status: 'Active', impressions: 184000, clicks: 4220 },
    { id: 'ad2', sponsor: 'Caribbean Travel Co.', title: 'Escape to the Islands', body: 'Book your dream Caribbean getaway with exclusive LinkUp member rates on flights and resorts.', format: 'image', media: 'https://picsum.photos/seed/adtravel/640/360', cta: 'Book Now', targetType: 'region', target: 'Caribbean', package: 'Banner Image', value: 3200, status: 'Active', impressions: 220000, clicks: 5880 },
    { id: 'ad3', sponsor: 'LinkUp Marketplace', title: 'Shop Local, Ship Global', body: 'Discover Caribbean sellers and products on the LinkUp Marketplace.', format: 'video', media: 'https://www.w3schools.com/html/mov_bbb.mp4', cta: 'Explore', targetType: 'category', target: 'Business & Economy', package: 'Sponsored Video', value: 2600, status: 'Active', impressions: 98000, clicks: 2110 },
    { id: 'ad4', sponsor: 'Scotiabank', title: 'Bank Smarter with Scotia', body: 'Manage your money across the Caribbean with the Scotiabank + LinkUp wallet.', format: 'image', media: 'https://picsum.photos/seed/adbank/640/360', cta: 'Learn More', targetType: 'region', target: 'Latin America', package: 'Category Takeover', value: 2500, status: 'Active', impressions: 310000, clicks: 8420 },
    { id: 'ad5', sponsor: 'Digicel', title: 'Stay Connected, Stay 360', body: 'Get the fastest Caribbean data plans and stream Caribbean 360 News anywhere.', format: 'image', media: 'https://picsum.photos/seed/addigicel/640/360', cta: 'Get the Plan', targetType: 'all', target: 'all', package: 'House Ad', value: 850, status: 'Active', impressions: 120000, clicks: 3100 },
]);

const formatChip: Record<AdFormat, { icon: typeof ImageIcon; class: string; label: string }> = {
    image: { icon: ImageIcon, class: 'bg-sky-50 text-sky-700', label: 'Image' },
    video: { icon: Video, class: 'bg-purple-50 text-purple-700', label: 'Video' },
    audio: { icon: Radio, class: 'bg-amber-50 text-amber-700', label: 'Audio' },
};

const statusStyle: Record<AdStatus, string> = {
    Active: 'bg-green-50 text-green-700',
    Scheduled: 'bg-blue-50 text-blue-700',
    Paused: 'bg-slate-100 text-slate-600',
};

const num = (value: number) => Math.round(value || 0).toLocaleString();
const fmt = (value: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0);

const targetLabel = (ad: SponsorAd) => {
    if (ad.targetType === 'story') {
        const story = storyOptions.find((option) => option.value === ad.target);
        return story ? `Story: ${story.label.length > 32 ? `${story.label.slice(0, 32)}…` : story.label}` : 'Story';
    }
    if (ad.targetType === 'all') return 'All Stories';
    return `${ad.targetType.charAt(0).toUpperCase()}${ad.targetType.slice(1)}: ${ad.target}`;
};

const kpis = computed(() => ({
    active: ads.value.filter((ad) => ad.status === 'Active').length,
    revenue: ads.value.reduce((sum, ad) => sum + ad.value, 0),
    impressions: ads.value.reduce((sum, ad) => sum + ad.impressions, 0),
    clicks: ads.value.reduce((sum, ad) => sum + ad.clicks, 0),
}));

const emptyForm = (): SponsorAd => ({
    id: '',
    sponsor: '',
    title: '',
    body: '',
    format: 'image',
    media: '',
    cta: 'Learn More',
    targetType: 'story',
    target: '',
    package: '',
    value: 0,
    status: 'Active',
    impressions: 0,
    clicks: 0,
});

const showModal = ref(false);
const editingId = ref<string | null>(null);
const form = ref<SponsorAd>(emptyForm());

const modalTitle = computed(() => (editingId.value ? 'Edit News Ad' : 'Add News Ad'));
const saveLabel = computed(() => (editingId.value ? 'Update Ad' : 'Add Ad'));

const targetOptions = computed(() => {
    if (form.value.targetType === 'story') return storyOptions.map((option) => ({ value: option.value, label: option.label }));
    if (form.value.targetType === 'category') return categoryOptions.map((category) => ({ value: category, label: category }));
    if (form.value.targetType === 'region') return regionOptions.map((region) => ({ value: region, label: region }));
    return [{ value: 'all', label: 'All Stories' }];
});

watch(
    () => form.value.targetType,
    () => {
        const first = targetOptions.value[0];
        if (first && !targetOptions.value.some((option) => option.value === form.value.target)) {
            form.value.target = first.value;
        }
    },
);

const openAddModal = () => {
    editingId.value = null;
    form.value = emptyForm();
    form.value.target = targetOptions.value[0]?.value || '';
    showModal.value = true;
};

const openEditModal = (ad: SponsorAd) => {
    editingId.value = ad.id;
    form.value = { ...ad };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveAd = () => {
    const sponsor = form.value.sponsor.trim();
    const title = form.value.title.trim();
    if (!sponsor || !title) {
        toast.error('Sponsor and ad title are required.');
        return;
    }

    if (editingId.value) {
        ads.value = ads.value.map((ad) => (ad.id === editingId.value ? { ...form.value, id: editingId.value as string } : ad));
        toast.success('Sponsored ad updated.');
    } else {
        ads.value.unshift({ ...form.value, id: `ad${Date.now()}` });
        toast.success('Sponsored ad added.');
    }
    closeModal();
};

const deleteAd = (ad: SponsorAd) => {
    if (!window.confirm(`Delete ad "${ad.title}"?`)) return;
    ads.value = ads.value.filter((item) => item.id !== ad.id);
    toast.success('Sponsored ad deleted.');
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h3 class="text-3xl font-black">Sponsored News &amp; Ads</h3>
                <p class="text-slate-500">Embedded image, video &amp; audio ads that appear inside Caribbean 360 stories — managed here, rendered in the reader feed.</p>
            </div>
            <div class="flex gap-2">
                <button class="flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 font-black" @click="emit('view-changed', 'newsFeedCommand')">
                    <Eye class="h-4 w-4" /> View in Feed
                </button>
                <button class="flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 font-black text-white" @click="openAddModal">
                    <Plus class="h-4 w-4" /> Add Ad
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"><p class="text-slate-500">Active Ads</p><h3 class="text-3xl font-black">{{ num(kpis.active) }}</h3></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"><p class="text-slate-500">Ad Revenue</p><h3 class="text-3xl font-black">{{ fmt(kpis.revenue) }}</h3></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"><p class="text-slate-500">Impressions</p><h3 class="text-3xl font-black">{{ num(kpis.impressions) }}</h3></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm"><p class="text-slate-500">Clicks</p><h3 class="text-3xl font-black">{{ num(kpis.clicks) }}</h3></div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-4">Sponsor</th>
                            <th>Ad Title</th>
                            <th>Format</th>
                            <th>Embedded In</th>
                            <th>Package</th>
                            <th>Value</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="ads.length">
                        <tr v-for="ad in ads" :key="ad.id" class="border-t border-slate-100">
                            <td class="p-4 font-black">{{ ad.sponsor }}</td>
                            <td>{{ ad.title }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black" :class="formatChip[ad.format].class">
                                    <component :is="formatChip[ad.format].icon" class="h-3 w-3" /> {{ formatChip[ad.format].label }}
                                </span>
                            </td>
                            <td>{{ targetLabel(ad) }}</td>
                            <td>{{ ad.package || '—' }}</td>
                            <td class="font-black">{{ fmt(ad.value) }}</td>
                            <td>{{ num(ad.impressions) }}</td>
                            <td>{{ num(ad.clicks) }}</td>
                            <td><span class="rounded-full px-3 py-1 text-xs font-black" :class="statusStyle[ad.status]">{{ ad.status }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button title="Edit" class="rounded-xl bg-slate-100 px-3 py-2" @click="openEditModal(ad)"><Pencil class="h-4 w-4" /></button>
                                    <button title="Delete" class="rounded-xl bg-slate-100 px-3 py-2 text-slate-500" @click="deleteAd(ad)"><Trash2 class="h-4 w-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr><td colspan="10" class="p-8 text-center font-bold text-slate-500">No ads yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-3">
            <div class="my-6 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center gap-4 bg-linear-to-r from-sky-500 to-cyan-500 p-6 text-white">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20"><BadgeDollarSign class="h-7 w-7" /></div>
                    <div class="flex-1">
                        <h3 class="text-3xl font-black">{{ modalTitle }}</h3>
                        <p class="text-sky-100">Embedded sponsor placement</p>
                    </div>
                    <button @click="closeModal"><X class="h-7 w-7" /></button>
                </div>
                <div class="max-h-[78vh] space-y-5 overflow-y-auto p-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-100 text-sky-600"><Megaphone class="h-5 w-5" /></span>
                            <h4 class="text-xl font-black">Ad Content</h4>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Sponsor</span>
                                <input v-model="form.sponsor" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Sponsor name" />
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Ad Title</span>
                                <input v-model="form.title" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Ad headline" />
                            </label>
                            <label class="block md:col-span-2">
                                <span class="text-sm font-bold text-slate-600">Body</span>
                                <textarea v-model="form.body" rows="2" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Ad copy..."></textarea>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">CTA Button</span>
                                <input v-model="form.cta" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Learn More" />
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Format</span>
                                <select v-model="form.format" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                    <option value="image">Image / JPEG</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                </select>
                            </label>
                            <label class="block md:col-span-2">
                                <span class="text-sm font-bold text-slate-600">Media URL (image, video, or audio)</span>
                                <input v-model="form.media" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="https://..." />
                            </label>
                        </div>
                        <div class="mt-4">
                            <p class="mb-2 text-xs font-black tracking-widest text-slate-400">PREVIEW</p>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                                <img v-if="form.media && form.format === 'image'" :src="form.media" class="h-40 w-full rounded-xl bg-slate-100 object-cover" alt="" />
                                <video v-else-if="form.media && form.format === 'video'" :src="form.media" class="h-40 w-full rounded-xl bg-slate-900 object-cover" controls preload="none" />
                                <audio v-else-if="form.media && form.format === 'audio'" :src="form.media" class="w-full" controls preload="none" />
                                <p v-else class="py-6 text-center text-sm font-bold text-slate-400">Enter a media URL to preview.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-green-100 text-green-600"><Crosshair class="h-5 w-5" /></span>
                            <h4 class="text-xl font-black">Placement &amp; Targeting</h4>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Embed In</span>
                                <select v-model="form.targetType" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                    <option value="story">Specific Story</option>
                                    <option value="category">Category</option>
                                    <option value="region">Region</option>
                                    <option value="all">All Stories</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Target</span>
                                <select v-model="form.target" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold" :disabled="form.targetType === 'all'">
                                    <option v-for="option in targetOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Package</span>
                                <input v-model="form.package" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Featured / Banner / Sponsored" />
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Value ($)</span>
                                <input v-model.number="form.value" type="number" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="0" />
                            </label>
                            <label class="block">
                                <span class="text-sm font-bold text-slate-600">Status</span>
                                <select v-model="form.status" class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-3 font-bold">
                                    <option>Active</option>
                                    <option>Scheduled</option>
                                    <option>Paused</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <button class="rounded-2xl bg-slate-100 px-6 py-3 font-black" @click="closeModal">Cancel</button>
                    <button class="rounded-2xl bg-sky-600 px-6 py-3 font-black text-white" @click="saveAd">{{ saveLabel }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
