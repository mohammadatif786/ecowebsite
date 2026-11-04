<script setup lang="ts">
// Step: "What's your gender?"
import Toggle from './Toggle.vue';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ gender: string; show_gender: boolean }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

const option = (on: boolean) =>
    'display: flex; align-items: center; gap: 14px; min-height: 72px; padding: 0 20px; border-radius: 16px; font-family: inherit; color: #0B1530; cursor: pointer; ' + s.card(on);
const dot = (on: boolean) =>
    'flex: none; width: 22px; height: 22px; box-sizing: border-box; border-radius: 50%; display: flex; align-items: center; justify-content: center; ' +
    (on ? 'border: 2px solid #1D4ED8;' : 'border: 2px solid #CBD5E1;');
const inner = (on: boolean) => 'width: 10px; height: 10px; border-radius: 50%; ' + (on ? 'background: #1D4ED8;' : 'background: transparent;');
</script>

<template>
    <div class="flex flex-col gap-3">
        <div role="radiogroup" aria-label="Gender" class="lu-grid2">
            <button
                v-for="label in options.genders"
                :key="label"
                type="button"
                role="radio"
                :aria-checked="answer.gender === label ? 'true' : 'false'"
                :style="option(answer.gender === label)"
                @click="answer = { ...answer, gender: label }"
            >
                <span :style="dot(answer.gender === label)"><span :style="inner(answer.gender === label)"></span></span>
                <span class="text-[18px] font-bold">{{ label }}</span>
            </button>
        </div>
        <p v-if="errors.gender" :style="s.fieldError" role="alert">{{ errors.gender }}</p>
        <Toggle v-model="answer.show_gender" label="Show my gender on my profile" />
    </div>
</template>
