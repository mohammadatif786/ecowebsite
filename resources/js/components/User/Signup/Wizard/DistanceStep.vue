<script setup lang="ts">
// Step: "How far will you link up?" 1–160 km, where the far end means Anywhere.
import { computed } from 'vue';
import * as s from './styles';

const answer = defineModel<{ km: number; unit: 'km' | 'mi' }>({ required: true });

const ANYWHERE = 160;
const miles = computed(() => answer.value.unit === 'mi');
const display = computed(() =>
    answer.value.km >= ANYWHERE ? 'Anywhere' : miles.value ? Math.round(answer.value.km * 0.621) + ' mi' : answer.value.km + ' km',
);
const presetLabel = (p: number) => (p === ANYWHERE ? 'Anywhere' : miles.value ? Math.round(p * 0.621) + ' mi' : p + ' km');
</script>

<template>
    <div class="flex flex-col gap-[18px]">
        <div style="display: flex; flex-direction: column; gap: 14px; padding: 22px; border-radius: 18px; background: #ffffff; border: 1.5px solid #dce3f0">
            <div class="flex items-end justify-between gap-3">
                <div class="flex flex-col gap-1">
                    <label for="f-dist" style="font-size: 14px; font-weight: 700; color: #475569">Distance preference</label>
                    <span class="text-[40px] font-extrabold leading-none tracking-[-0.03em]">{{ display }}</span>
                </div>
                <div role="group" aria-label="Units" class="flex rounded-[12px] bg-[#EEF2F8] p-1">
                    <button type="button" :aria-pressed="!miles ? 'true' : 'false'" :style="s.segment(!miles)" @click="answer = { ...answer, unit: 'km' }">km</button>
                    <button type="button" :aria-pressed="miles ? 'true' : 'false'" :style="s.segment(miles)" @click="answer = { ...answer, unit: 'mi' }">mi</button>
                </div>
            </div>
            <input
                id="f-dist"
                type="range"
                min="1"
                :max="ANYWHERE"
                step="1"
                :value="answer.km"
                :aria-valuetext="display"
                style="width: 100%; height: 28px; margin: 0; accent-color: #1d4ed8; cursor: pointer"
                @input="answer = { ...answer, km: Number(($event.target as HTMLInputElement).value) }"
            />
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="p in [10, 25, 50, 100, ANYWHERE]"
                :key="p"
                type="button"
                :aria-pressed="answer.km === p ? 'true' : 'false'"
                :style="s.chip(answer.km === p)"
                @click="answer = { ...answer, km: p }"
            >
                {{ presetLabel(p) }}
            </button>
        </div>
    </div>
</template>
