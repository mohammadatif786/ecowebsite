<script setup lang="ts">
// Step: "What do you do?" (optional). Free text, or a tap on a common field.
import * as s from './styles';
import type { WizardOptions } from './types';

const answer = defineModel<{ work: string }>({ required: true });
defineProps<{ options: WizardOptions; errors: Record<string, string> }>();
</script>

<template>
    <div class="flex flex-col gap-3.5">
        <div class="flex flex-col gap-1.5">
            <label for="f-work" :style="s.label">Job title or passion</label>
            <input
                id="f-work"
                type="text"
                maxlength="60"
                placeholder="e.g. Nurse, DJ, Software engineer, Small business owner"
                :value="answer.work"
                :style="s.textField"
                @input="answer = { work: ($event.target as HTMLInputElement).value.slice(0, 60) }"
            />
            <p v-if="errors.work" :style="s.fieldError" role="alert">{{ errors.work }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="f in options.workFields"
                :key="f"
                type="button"
                :aria-pressed="answer.work === f ? 'true' : 'false'"
                :style="s.chip(answer.work === f)"
                @click="answer = { work: answer.work === f ? '' : f }"
            >
                {{ f }}
            </button>
        </div>
    </div>
</template>
