<script setup lang="ts">
// Password and Confirm side by side, with the four-rule checklist and the strength meter underneath.
import { computed } from 'vue';
import FieldInput from './FieldInput.vue';
import { icons } from './icons';
import { passwordRules, passwordScore, strengthColors, strengthLabels } from './passwordRules';

const props = defineProps<{
    password: string;
    confirmation: string;
    passwordError?: string | null;
    confirmationError?: string | null;
}>();

defineEmits<{
    (e: 'update:password', value: string): void;
    (e: 'update:confirmation', value: string): void;
}>();

const rules = computed(() => passwordRules(props.password));
const score = computed(() => passwordScore(props.password));
const color = computed(() => strengthColors[score.value]);
const matches = computed(() => !!props.confirmation && props.confirmation === props.password);
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-2 items-start gap-[14px] max-[480px]:grid-cols-1">
            <FieldInput
                id="a-pw"
                label="Password"
                reveal
                autocomplete="new-password"
                placeholder="Create a password"
                :model-value="password"
                :error="passwordError"
                @update:model-value="$emit('update:password', $event)"
            >
                <div v-if="password" class="flex items-center gap-2">
                    <div aria-hidden="true" class="flex flex-1 gap-1">
                        <div v-for="n in 4" :key="n" class="h-[5px] flex-1 rounded-[3px]" :style="{ background: n <= Math.max(score, 1) ? color : '#DCE3F0' }"></div>
                    </div>
                    <span class="text-[12px] font-extrabold" :style="{ color }">{{ strengthLabels[score] }}</span>
                </div>
            </FieldInput>

            <FieldInput
                id="a-pw2"
                label="Confirm password"
                reveal
                autocomplete="new-password"
                placeholder="Type it again"
                :model-value="confirmation"
                :error="confirmationError"
                @update:model-value="$emit('update:confirmation', $event)"
            >
                <span v-if="confirmation" class="text-[12px] font-bold" :class="matches ? 'text-[#166534]' : 'text-[#B45309]'">
                    {{ matches ? 'Passwords match' : "Passwords don't match yet" }}
                </span>
            </FieldInput>
        </div>

        <ul class="m-0 -mt-1.5 flex list-none flex-wrap gap-x-4 gap-y-1.5 p-0">
            <li v-for="rule in rules" :key="rule.label" class="inline-flex items-center gap-1.5 text-[12px] font-bold" :class="rule.met ? 'text-[#166534]' : 'text-[#64748B]'">
                <svg aria-hidden="true" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="rule.met ? icons.check : icons.dot" />
                </svg>
                {{ rule.label }}
                <span class="sr-only">{{ rule.met ? '(met)' : '(not met yet)' }}</span>
            </li>
        </ul>
    </div>
</template>
