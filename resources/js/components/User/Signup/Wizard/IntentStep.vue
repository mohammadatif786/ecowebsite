<script setup lang="ts">
// Step: "What brings you to LinkUp?" Any number of reasons, in two groups.
import { intentIcons, wizardIcons } from './icons';
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ intents: string[] }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();

function toggle(id: string) {
    const list = answer.value.intents;
    answer.value = { intents: list.includes(id) ? list.filter((i) => i !== id) : [...list, id] };
}

const tile = (on: boolean) =>
    'display: flex; align-items: center; gap: 12px; min-height: 62px; padding: 8px 12px; border-radius: 14px; font-family: inherit; color: #0B1530; cursor: pointer; ' + s.card(on);
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div v-for="g in options.intents" :key="g.title" class="flex flex-col gap-2">
            <span :style="s.groupTitle">{{ g.title }}</span>
            <div role="group" :aria-label="g.title" class="lu-grid2" style="gap: 8px">
                <button
                    v-for="o in g.items"
                    :key="o.id"
                    type="button"
                    :aria-pressed="answer.intents.includes(o.id) ? 'true' : 'false'"
                    :style="tile(answer.intents.includes(o.id))"
                    @click="toggle(o.id)"
                >
                    <span :style="s.iconBox(answer.intents.includes(o.id), 38, '11px')">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="intentIcons[o.id]" />
                        </svg>
                    </span>
                    <span class="flex flex-1 flex-col gap-0.5 text-left">
                        <span class="text-[14px] font-extrabold">{{ o.title }}</span>
                        <span class="text-[12px] leading-[1.35] text-[#475569]">{{ o.desc }}</span>
                    </span>
                    <span :style="s.checkBox(answer.intents.includes(o.id))">
                        <svg v-if="answer.intents.includes(o.id)" aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="wizardIcons.check" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
        <p v-if="errors.intents" :style="s.fieldError" role="alert">{{ errors.intents }}</p>
    </div>
</template>
