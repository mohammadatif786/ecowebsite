<script setup lang="ts">
// Step: "What languages do you speak?" The first pick is the primary language.
import { wizardIcons } from './icons';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ languages: string[] }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

function toggle(name: string) {
    const list = answer.value.languages;
    answer.value = { languages: list.includes(name) ? list.filter((l) => l !== name) : [...list, name] };
}

const tile = (on: boolean) =>
    'display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 66px; padding: 10px 16px; border-radius: 14px; font-family: inherit; cursor: pointer; text-align: left; ' +
    s.card(on);
</script>

<template>
    <div class="flex flex-col gap-2">
        <div role="group" aria-label="Languages" class="lu-grid2">
            <button
                v-for="l in options.languages"
                :key="l.name"
                type="button"
                :aria-pressed="answer.languages.includes(l.name) ? 'true' : 'false'"
                :style="tile(answer.languages.includes(l.name))"
                @click="toggle(l.name)"
            >
                <span class="flex flex-col items-start gap-0.5">
                    <span class="flex items-center gap-2 text-[15px] font-bold text-[#0B1530]">
                        {{ l.name }}
                        <span v-if="answer.languages[0] === l.name" class="rounded-full bg-[#D4E83A] px-2 py-0.5 text-[11px] font-extrabold uppercase text-[#0B1530]">Primary</span>
                    </span>
                    <span class="text-[13px] text-[#64748B]">{{ l.native }}</span>
                </span>
                <span :style="s.checkBox(answer.languages.includes(l.name))">
                    <svg v-if="answer.languages.includes(l.name)" aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="wizardIcons.check" />
                    </svg>
                </span>
            </button>
        </div>
        <p v-if="errors.languages" :style="s.fieldError" role="alert">{{ errors.languages }}</p>
    </div>
</template>
