<script setup lang="ts">
// Step: "What are you into?" (optional)
import { interestIcons } from './icons';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ interests: string[] }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

function toggle(name: string) {
    const list = answer.value.interests;
    answer.value = { interests: list.includes(name) ? list.filter((i) => i !== name) : [...list, name] };
}

const tile = (on: boolean) =>
    'display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; min-height: 96px; padding: 12px 8px; border-radius: 14px; font-family: inherit; cursor: pointer; ' +
    s.card(on);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div role="group" aria-label="Interests" class="lu-grid5">
            <button
                v-for="name in options.interests"
                :key="name"
                type="button"
                :aria-pressed="answer.interests.includes(name) ? 'true' : 'false'"
                :style="tile(answer.interests.includes(name))"
                @click="toggle(name)"
            >
                <span :style="s.iconBox(answer.interests.includes(name), 38, '50%')">
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="interestIcons[name]" />
                    </svg>
                </span>
                <span class="text-center text-[13px] font-bold leading-[1.25] text-[#0B1530]">{{ name }}</span>
            </button>
        </div>
        <p v-if="errors.interests" :style="s.fieldError" role="alert">{{ errors.interests }}</p>
    </div>
</template>
