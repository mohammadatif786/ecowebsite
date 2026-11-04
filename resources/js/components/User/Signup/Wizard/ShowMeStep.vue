<script setup lang="ts">
// Step: "Who would you like to see?"
import { showMeIcons } from './icons';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ show_me: string }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

const option = (on: boolean) =>
    'display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 24px 10px; border-radius: 18px; font-family: inherit; cursor: pointer; ' + s.card(on);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div role="radiogroup" aria-label="Show me" class="lu-grid3" style="gap: 12px">
            <button
                v-for="label in options.showMe"
                :key="label"
                type="button"
                role="radio"
                :aria-checked="answer.show_me === label ? 'true' : 'false'"
                :style="option(answer.show_me === label)"
                @click="answer = { show_me: label }"
            >
                <span :style="s.iconBox(answer.show_me === label, 60, '50%')">
                    <svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="showMeIcons[label]" />
                    </svg>
                </span>
                <span class="text-[17px] font-extrabold">{{ label }}</span>
            </button>
        </div>
        <p v-if="errors.show_me" :style="s.fieldError" role="alert">{{ errors.show_me }}</p>
    </div>
</template>
