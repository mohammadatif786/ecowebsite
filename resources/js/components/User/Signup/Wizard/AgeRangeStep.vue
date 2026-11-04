<script setup lang="ts">
// Step: "What age range are you interested in?" Two handles on one track, 18 to 80+.
import { computed } from 'vue';
import * as s from './styles';

const answer = defineModel<{ min: number; max: number }>({ required: true });
defineProps<{ errors: Record<string, string> }>();

const MIN = 18;
const MAX = 80;
const pct = (v: number) => ((v - MIN) / (MAX - MIN)) * 100;
const maxText = computed(() => (answer.value.max >= MAX ? '80+' : String(answer.value.max)));
const fill = computed(() => {
    const lo = pct(answer.value.min);
    const hi = 100 - pct(answer.value.max);
    return `position: absolute; top: 17px; height: 6px; border-radius: 3px; background: #1D4ED8; left: calc(${lo}% + ${15 - lo * 0.3}px); right: calc(${hi}% + ${15 - hi * 0.3}px);`;
});

function setMin(e: Event) {
    answer.value = { ...answer.value, min: Math.min(Number((e.target as HTMLInputElement).value), answer.value.max - 1) };
    (e.target as HTMLInputElement).value = String(answer.value.min);
}
function setMax(e: Event) {
    answer.value = { ...answer.value, max: Math.max(Number((e.target as HTMLInputElement).value), answer.value.min + 1) };
    (e.target as HTMLInputElement).value = String(answer.value.max);
}

const presets: Array<[number, number, string]> = [
    [18, 25, '18–25'],
    [25, 35, '25–35'],
    [35, 50, '35–50'],
    [50, 80, '50+'],
    [18, 80, 'Any age'],
];
</script>

<template>
    <div class="flex flex-col gap-[18px]">
        <div style="display: flex; flex-direction: column; gap: 14px; padding: 22px; border-radius: 18px; background: #ffffff; border: 1.5px solid #dce3f0">
            <div class="flex items-end justify-between">
                <span style="font-size: 14px; font-weight: 700; color: #475569">Age range preference</span>
                <span class="text-[36px] font-extrabold leading-none tracking-[-0.03em]">{{ answer.min }}–{{ maxText }}</span>
            </div>
            <div class="lu-dual">
                <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: 17px; height: 6px; border-radius: 3px; background: #dce3f0"></div>
                <div aria-hidden="true" :style="fill"></div>
                <input aria-label="Minimum age" type="range" :min="MIN" :max="MAX" step="1" :value="answer.min" @input="setMin" />
                <input aria-label="Maximum age" type="range" :min="MIN" :max="MAX" step="1" :value="answer.max" :aria-valuetext="maxText" @input="setMax" />
            </div>
            <div class="flex justify-between text-[12px] font-semibold text-[#64748B]"><span>18</span><span>80+</span></div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="[a, b, text] in presets"
                :key="text"
                type="button"
                :aria-pressed="answer.min === a && answer.max === b ? 'true' : 'false'"
                :style="s.chip(answer.min === a && answer.max === b)"
                @click="answer = { min: a, max: b }"
            >
                {{ text }}
            </button>
        </div>
        <p v-if="errors.max || errors.min" :style="s.fieldError" role="alert">{{ errors.max || errors.min }}</p>
    </div>
</template>
