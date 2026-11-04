<script setup lang="ts">
// Step: "Add your photos". Six slots, the first is the main photo. Files stay in the browser
// until "Finish", which uploads them with the rest of the profile.
import { computed, ref } from 'vue';
import * as s from './styles';
import type { PickedPhoto } from './types';

const photos = defineModel<Array<PickedPhoto | null>>({ required: true });
defineProps<{ errors: Record<string, string> }>();

const MAX_BYTES = 5 * 1024 * 1024;
const TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const tints = ['#2B5FD9', '#0E8A7D', '#C2410C', '#7C3AED', '#0369A1', '#BE185D'];

const input = ref<HTMLInputElement | null>(null);
const target = ref(0);
const problem = ref('');

const count = computed(() => photos.value.filter(Boolean).length);

function choose(slot: number) {
    target.value = slot;
    problem.value = '';
    input.value?.click();
}

function onFiles(event: Event) {
    const el = event.target as HTMLInputElement;
    const files = Array.from(el.files ?? []);
    el.value = '';
    const next = [...photos.value];
    // The tapped slot first, then the empty ones in order.
    const slots = [target.value, ...next.map((p, i) => (p ? -1 : i)).filter((i) => i >= 0 && i !== target.value)];
    for (const file of files) {
        if (!TYPES.includes(file.type)) {
            problem.value = 'Use a JPG, PNG or WebP photo.';
            continue;
        }
        if (file.size > MAX_BYTES) {
            problem.value = 'Each photo must be 5 MB or smaller.';
            continue;
        }
        const slot = slots.shift();
        if (slot === undefined) break;
        next[slot] = { file, preview: URL.createObjectURL(file) };
    }
    photos.value = next;
}

function remove(slot: number) {
    const next = [...photos.value];
    if (next[slot]) URL.revokeObjectURL(next[slot]!.preview);
    next[slot] = null;
    photos.value = next;
}

const cell = (i: number) => 'position: relative; min-width: 0; ' + (i === 0 ? 'grid-column: span 2; grid-row: span 2;' : '');
const addStyle = (i: number) =>
    'width: 100%; height: 100%; border-radius: 16px; border: 2px dashed ' +
    (i === 0 ? '#1D4ED8' : '#BFD0F5') +
    '; background: ' +
    (i === 0 ? '#EEF4FF' : '#FFFFFF') +
    '; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; font-family: inherit; cursor: pointer;';
</script>

<template>
    <div class="flex flex-col gap-2.5">
        <div class="flex items-baseline justify-between">
            <span class="text-[14px] font-bold">Photos</span>
            <span class="text-[13px] font-bold text-[#475569]">{{ count }} of 6 added</span>
        </div>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onFiles" />
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-auto-rows: 120px; gap: 10px">
            <div v-for="(photo, i) in photos" :key="i" :style="cell(i)">
                <div
                    v-if="photo"
                    class="relative flex h-full w-full items-center justify-center overflow-hidden rounded-[16px] bg-cover bg-center"
                    :style="{ backgroundColor: tints[i], backgroundImage: `url(${photo.preview})` }"
                >
                    <span v-if="i === 0" class="absolute bottom-2.5 left-2.5 rounded-full bg-[#D4E83A] px-2.5 py-1 text-[12px] font-extrabold text-[#0B1530]">Main photo</span>
                    <button
                        type="button"
                        :aria-label="`Remove photo ${i + 1}`"
                        class="absolute right-2 top-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border-0 bg-[rgba(11,21,48,0.65)] text-white"
                        @click="remove(i)"
                    >
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
                <button v-else type="button" :aria-label="i === 0 ? 'Add main photo' : `Add photo ${i + 1}`" :style="addStyle(i)" @click="choose(i)">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1D4ED8] text-white">
                        <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </span>
                    <span v-if="i === 0" class="text-[14px] font-bold text-[#1D4ED8]">Add main photo</span>
                </button>
            </div>
        </div>
        <p v-if="problem" :style="s.fieldError" role="alert">{{ problem }}</p>
        <p v-for="(message, key) in errors" :key="key" :style="s.fieldError" role="alert">{{ message }}</p>
        <span class="text-[13px] text-[#475569]">Tip: lead with a clear photo of your face, then show your vibe.</span>
    </div>
</template>
