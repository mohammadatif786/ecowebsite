<template>
    <Modal ref="modalRef" maxWidth="max-w-md">
        <div class="overflow-hidden rounded-[18px] border border-white/15 bg-[#111a45] text-white shadow-2xl">
            <header class="flex items-start justify-between border-b border-white/10 bg-[#101a43] px-4 py-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-rose-500">
                        <i data-lucide="radio" class="h-4 w-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-black leading-tight">Broadcast Settings</h3>
                        <p class="mt-0.5 text-[11px] font-bold text-slate-400">Set up your stream before you go live</p>
                    </div>
                </div>
                <button @click="close"
                    class="grid h-8 w-8 place-items-center rounded-full text-white transition hover:bg-white/10">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </header>

            <div class="max-h-[78vh] space-y-3 overflow-y-auto p-4 hide-scroll">
                <section class="rounded-xl border border-white/10 bg-white/[0.055] p-3">
                    <p class="mb-3 flex items-center gap-1.5 text-[10px] font-black uppercase text-slate-400">
                        <i data-lucide="type" class="h-3 w-3"></i>
                        Stream Basics
                    </p>

                    <label class="block text-[11px] font-black text-slate-300">Title</label>
                    <input v-model="form.title" placeholder="e.g. Carnival Warmup!"
                        class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-white/[0.07] px-3 text-xs font-bold text-white outline-none placeholder:text-slate-400 focus:border-blue-400"
                        :class="{ 'border-rose-500': errors.title }" />
                    <p v-if="errors.title" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.title }}</p>

                    <label class="mt-3 block text-[11px] font-black text-slate-300">Category</label>
                    <select v-model="form.cat"
                        class="mt-2 h-10 w-full rounded-lg border border-white/10 bg-[#202852] px-3 text-xs font-bold text-white outline-none focus:border-blue-400"
                        :class="{ 'border-rose-500': errors.cat }">
                        <option value="">Select category</option>
                        <option v-for="category in liveCategories" :key="category" :value="category">
                            {{ category }}
                        </option>
                    </select>

                    <p v-if="errors.cat" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.cat }}</p>

                    <label class="mt-4 block text-[11px] font-black text-slate-300">Location <span class="text-slate-500">(optional)</span></label>
                    <div class="mt-1 flex gap-2">
                        <input v-model="form.loc" @input="locationFeedback = null" placeholder="e.g. The Valley, Anguilla"
                            class="h-10 min-w-0 flex-1 rounded-lg border border-white/10 bg-white/[0.07] px-3 text-xs font-bold text-white outline-none placeholder:text-slate-400 focus:border-blue-400" />
                        <button type="button" @click="detectLocation" :disabled="isLocating"
                            class="grid h-10 w-10 place-items-center rounded-lg bg-white/10 text-base hover:bg-white/20 disabled:cursor-wait disabled:opacity-50"
                            :title="isLocating ? 'Detecting location…' : 'Use my broad location'"
                            :aria-label="isLocating ? 'Detecting location' : 'Use my broad location'">
                            <span v-if="isLocating" class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                            <span v-else>📍</span>
                        </button>
                    </div>
                    <p v-if="locationFeedback" class="mt-1.5 text-[10px] font-bold"
                        :class="locationFeedback.type === 'success' ? 'text-emerald-400' : locationFeedback.type === 'error' ? 'text-amber-300' : 'text-slate-400'">
                        {{ locationFeedback.message }}
                    </p>
                    <p v-if="errors.loc" class="mt-1 text-[10px] font-bold text-rose-500">{{ errors.loc }}</p>
                </section>

                <section class="rounded-xl border border-white/10 bg-white/[0.055] p-3">
                    <p class="mb-3 flex items-center gap-1.5 text-[10px] font-black uppercase text-slate-400">
                        <i data-lucide="eye" class="h-3 w-3"></i>
                        Visibility
                    </p>
                    <div class="grid grid-cols-2 rounded-full bg-white/[0.07] p-1">
                        <button v-for="visibility in ['Public', 'Private']" :key="visibility"
                            @click="form.visibility = visibility" :class="[
                                'h-8 rounded-full text-xs font-black transition',
                                form.visibility === visibility ? 'bg-gradient-to-r from-sky-400 to-blue-600 text-white shadow-lg shadow-blue-950/20' : 'text-slate-300'
                            ]">
                            {{ visibility }}
                        </button>
                    </div>

                    <p v-if="form.visibility === 'Private'" class="text-[11px] text-[#28a4ff] mt-2 leading-tight font-bold">
                        🔒 Private broadcasts are restricted to subscribers. Set a fee that viewers must pay to join your stream.
                    </p>

                    <div v-if="form.visibility === 'Private'" class="mt-4">
                        <label class="block text-[11px] font-black text-[#28a4ff]">Sub Fee ($)</label>
                        <input type="number" v-model="form.subscription_rate" placeholder="5.00" min="1"
                            class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-white/[0.07] px-3 text-xs font-bold text-white outline-none focus:border-blue-400" />
                        <div class="mt-2 text-[10px] font-bold text-slate-400">
                            You earn (50%): <span class="text-white">${{ (Number(form.subscription_rate || 0) * 0.5).toFixed(2) }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-white/10 bg-white/[0.055] p-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="flex items-center gap-1.5 text-[10px] font-black uppercase text-slate-400">
                            <i data-lucide="shopping-bag" class="h-3 w-3"></i>
                            Shop While You Stream
                        </p>
                        <button @click="openTagPicker"
                            class="rounded-full bg-blue-500 px-3 py-1.5 text-[10px] font-black text-white transition hover:bg-blue-600">
                            + Add Product
                        </button>
                    </div>

                    <div v-if="form.products.length" class="mt-3 space-y-2">
                        <div v-for="(product, index) in form.products" :key="productKey(product, index)"
                            class="flex items-center gap-3 rounded-2xl bg-white/5 border border-white/10 p-3">
                            <img :src="product.image || product.cover_image"
                                class="h-12 w-12 rounded-xl object-cover shadow-lg" alt="product" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-black leading-tight">{{ product.title }}</p>
                                <p class="text-[11px] font-bold text-slate-400 mt-0.5">${{
                                    Number(product.price).toFixed(2) }} · you earn {{ product.commission || 0 }}%</p>
                            </div>
                            <button @click="removeProduct(index)"
                                class="grid h-8 w-8 place-items-center rounded-full text-slate-400 transition hover:bg-rose-500/20 hover:text-rose-400">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <p v-else class="mt-3 text-[11px] font-semibold leading-relaxed text-slate-400">
                        Feature Marketplace products so viewers can shop while you talk - you earn commission on every
                        sale.
                    </p>
                </section>

                <section class="rounded-xl border border-white/10 bg-white/[0.055] p-3">
                    <p class="mb-3 flex items-center gap-1.5 text-[10px] font-black uppercase text-slate-400">
                        <i data-lucide="image" class="h-3 w-3"></i>
                        Cover Image
                    </p>
                    <label v-if="!coverPreview"
                        class="flex h-20 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-white/25 bg-white/[0.035] transition hover:bg-white/[0.07]">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-white/10">
                            <i data-lucide="camera" class="h-4 w-4 text-slate-300"></i>
                        </span>
                        <span class="mt-2 text-xs font-black text-slate-300">Click to upload</span>
                        <input type="file" class="hidden" accept="image/*" @change="handleImage" />
                    </label>
                    <div v-else class="relative h-40 rounded-lg overflow-hidden group">
                        <img :src="coverPreview" class="w-full h-full object-cover" />
                        <button @click="coverImage = null; coverPreview = null"
                            class="absolute top-2 right-2 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-white/10 bg-white/[0.055]">
                    <button @click="showVideoSettings = !showVideoSettings"
                        class="flex w-full items-center justify-between px-3 py-3 text-left">
                        <span class="flex items-center gap-1.5 text-[10px] font-black uppercase text-slate-400">
                            <i data-lucide="settings-2" class="h-3 w-3"></i>
                            Video Settings
                        </span>
                        <i data-lucide="chevron-down"
                            :class="['h-4 w-4 text-slate-400 transition', showVideoSettings ? 'rotate-180' : '']"></i>
                    </button>

                    <div v-if="showVideoSettings" class="grid gap-3 border-t border-white/10 p-3">
                        <label class="block text-[11px] font-black text-slate-300">
                            Base Resolution
                            <select v-model="form.base"
                                class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-[#202852] px-3 text-xs font-bold text-white outline-none">
                                <option v-for="resolution in BC_BASE" :key="resolution" :value="resolution">{{
                                    resolution }}</option>
                            </select>
                        </label>
                        <label class="block text-[11px] font-black text-slate-300">
                            Output Resolution
                            <select v-model="form.out"
                                class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-[#202852] px-3 text-xs font-bold text-white outline-none">
                                <option v-for="resolution in BC_OUT" :key="resolution" :value="resolution">{{ resolution
                                    }}</option>
                            </select>
                        </label>
                    </div>
                </section>

                <button @click="startBroadcast"
                    class="h-11 w-full rounded-xl bg-gradient-to-r from-red-500 to-pink-500 text-sm font-black text-white shadow-lg shadow-rose-950/20 transition hover:brightness-105">
                    ● Go Live
                </button>
            </div>
        </div>
    </Modal>

    <TagPickerModal ref="tagPickerRef" @pick="onTagPick" />
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '../ui/Modal.vue';
import TagPickerModal from './TagPickerModal.vue';

const modalRef = ref(null);
const tagPickerRef = ref(null);
const showVideoSettings = ref(false);
const coverImage = ref(null);
const coverPreview = ref(null);
const emit = defineEmits(['startBroadcast']);

const handleImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        coverImage.value = file;
        coverPreview.value = URL.createObjectURL(file);
    }
};

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

const LIVE_CATS = ['Private', 'Adult / Mature', 'Afrobeats & Amapiano', 'Carnival Mixers', 'News - Sports', 'Just Chatting', 'Comedy & Skits', 'Education & Growth', 'Business & Money'];
const BC_BASE = ['1920x1080', '1280x720', '2560x1440', '3840x2160'];
const BC_OUT = ['1280x720', '1920x1080', '854x480', '640x360'];
const BC_FILTER = ['Bicubic', 'Bilinear', 'Lanczos', 'Area'];

const defaultForm = () => ({
    title: '',
    cat: 'Just Chatting',
    loc: '',
    visibility: 'Public',
    subscription_rate: 5,
    base: '1920x1080',
    out: '1280x720',
    filter: BC_FILTER[0],
    products: []
});

const form = ref(defaultForm());
const errors = ref({});
const isLocating = ref(false);
const hasAutoDetectedLocation = ref(false);
const locationFeedback = ref(null);

const liveCategories = computed(() => {
    const rows = props.categories.map((category) => String(category || '').trim()).filter(Boolean);
    const merged = rows.length ? rows : LIVE_CATS;
    return [...new Set(merged.filter((category) => category !== 'All'))];
});

const validate = () => {
    errors.value = {};
    let isValid = true;

    if (!form.value.title || form.value.title.trim() === '') {
        errors.value.title = 'Stream title is required';
        isValid = false;
    } else if (form.value.title.length > 100) {
        errors.value.title = 'Title must be less than 100 characters';
        isValid = false;
    }

    if (!form.value.cat) {
        errors.value.cat = 'Please select a category';
        isValid = false;
    }

    return isValid;
};

const locationNotice = (message, type = 'info') => {
    locationFeedback.value = { message, type };
};

const detectLocation = () => {
    if (isLocating.value) return;

    if (!window.isSecureContext) {
        locationNotice('Secure connection required. Enter it manually.', 'error');
        return;
    }

    if (!navigator.geolocation) {
        locationNotice('Location unavailable. Enter it manually.', 'error');
        return;
    }

    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(async ({ coords }) => {
        try {
            // Coordinates remain only in this request and are never sent to Laravel.
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(coords.latitude)}&lon=${encodeURIComponent(coords.longitude)}&zoom=10`);
            if (!response.ok) throw new Error('Reverse geocoding failed');

            const data = await response.json();
            const address = data.address || {};
            const city = address.city || address.town || address.village || address.county;
            const broadLocation = [city, address.country].filter(Boolean).join(', ');

            if (broadLocation) {
                form.value.loc = broadLocation;
                errors.value.loc = null;
                locationNotice('Broad location detected. You can edit it.', 'success');
            } else {
                locationNotice('Town or city not found. Enter it manually.', 'error');
            }
        } catch {
            // Preserve anything the user has already typed.
            locationNotice('Location lookup failed. Enter it manually.', 'error');
        }
        finally { isLocating.value = false; }
    }, (error) => {
        isLocating.value = false;

        if (error.code === error.PERMISSION_DENIED) {
            locationNotice('Permission blocked. Allow it in Site settings.', 'error');
        } else if (error.code === error.TIMEOUT) {
            locationNotice('Location timed out. Try again or enter it.', 'error');
        } else {
            locationNotice('Location unavailable. Try again or enter it.', 'error');
        }
    }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
};

const refreshIcons = () => {
    nextTick(() => {
        if (window.lucide) window.lucide.createIcons();
    });
};

const productKey = (product, index) => `${product.kind || 'product'}-${product.id || index}`;

const openTagPicker = () => {
    tagPickerRef.value?.open?.();
};

const onTagPick = (item) => {
    if (!form.value.products.some((product) => product.id === item.id && product.kind === item.kind)) {
        // Add some random sales/commission data for the demo
        const p = {
            ...item,
            sold: Math.floor(Math.random() * 5),
            left: Math.floor(Math.random() * 10) + 2,
            commission: item.commission || 12,
            featured: true
        };
        form.value.products.push(p);
        if (window.toast) window.toast(item.title + ' tagged - now shoppable');
        refreshIcons();
    }
};

const removeProduct = (index) => {
    form.value.products.splice(index, 1);
    refreshIcons();
};

const open = () => {
    errors.value = {};
    locationFeedback.value = null;
    modalRef.value?.open?.();
    refreshIcons();

    // Ask once per page visit. The field remains optional and editable, and the
    // pin button always permits a manual retry.
    if (!form.value.loc && !hasAutoDetectedLocation.value) {
        hasAutoDetectedLocation.value = true;
        detectLocation();
    }
};

const close = () => {
    modalRef.value?.close?.();
};

const startBroadcast = async () => {
    if (!validate()) return;

    try {
        // We no longer call the backend here because LiveStreamComponent.vue
        // will call 'frontend.go-live.start-agora' when it joins as a host.
        // We just emit the form data to the parent Index.vue.
        emit('startBroadcast', { ...form.value, coverImage: coverImage.value });

        close();
        // Reset form after a delay to avoid UI flicker
        setTimeout(() => {
            form.value = defaultForm();
            coverImage.value = null;
            coverPreview.value = null;
            showVideoSettings.value = false;
        }, 500);

    } catch (error) {
        console.error('Failed to prepare broadcast:', error);
        if (window.toast) window.toast('Failed to start stream. Please check your settings.', 'error');
    }
};

defineExpose({ open, close });
</script>
