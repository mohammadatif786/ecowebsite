<script setup lang="ts">
// One labelled text field: 54px high, 14px radius, optional "@" prefix, optional show/hide eye
// for passwords. `error` is a server message (red); `hint` is a gentle in-page nudge (amber).
import { computed, ref } from 'vue';
import { icons } from './icons';

const props = withDefaults(
    defineProps<{
        id: string;
        label: string;
        modelValue: string;
        type?: string;
        placeholder?: string;
        autocomplete?: string;
        inputmode?: 'text' | 'email' | 'numeric' | 'tel';
        error?: string | null;
        hint?: string | null;
        prefix?: string;
        mono?: boolean;
        reveal?: boolean;
        maxlength?: number;
        disabled?: boolean;
    }>(),
    { type: 'text', placeholder: '', autocomplete: 'off', inputmode: undefined, error: null, hint: null, prefix: '', mono: false, reveal: false, maxlength: undefined, disabled: false },
);

const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();

const shown = ref(false);
const inputType = computed(() => (props.reveal ? (shown.value ? 'text' : 'password') : props.type));
const message = computed(() => props.error || props.hint);

function onInput(event: Event) {
    emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>

<template>
    <div class="flex min-w-0 flex-col gap-1.5">
        <div class="flex items-baseline justify-between gap-3">
            <label :for="id" class="text-[14px] font-bold text-[#1E293B]">{{ label }}</label>
            <slot name="label-aside" />
        </div>

        <div class="relative">
            <span v-if="prefix" aria-hidden="true" class="pointer-events-none absolute left-4 top-[15px] text-[16px] font-bold text-[#64748B]">{{ prefix }}</span>
            <input
                :id="id"
                :value="modelValue"
                :type="inputType"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :inputmode="inputmode"
                :maxlength="maxlength"
                :disabled="disabled"
                :aria-invalid="!!error"
                :aria-describedby="message ? `${id}-message` : undefined"
                autocapitalize="off"
                class="box-border h-[54px] w-full rounded-[14px] border-[1.5px] bg-white text-[16px] text-[#0B1530] disabled:cursor-not-allowed disabled:bg-[#F1F5F9]"
                :class="[error ? 'border-[#DC2626]' : 'border-[#CBD5E1]', prefix ? 'pl-9 pr-4' : reveal ? 'pl-4 pr-[52px]' : 'px-4', mono ? 'font-mono' : '']"
                @input="onInput"
            />
            <button
                v-if="reveal"
                type="button"
                :aria-label="shown ? 'Hide password' : 'Show password'"
                :aria-pressed="shown"
                class="absolute right-[5px] top-[5px] flex h-11 w-11 cursor-pointer items-center justify-center rounded-[10px] text-[#475569] hover:bg-[#F1F5F9]"
                @click="shown = !shown"
            >
                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="shown ? icons.eyeOff : icons.eye" />
                </svg>
            </button>
        </div>

        <slot />

        <p v-if="message" :id="`${id}-message`" :role="error ? 'alert' : undefined" class="m-0 text-[13px] font-semibold" :class="error ? 'text-[#B91C1C]' : 'text-[#B45309]'">
            {{ message }}
        </p>
    </div>
</template>
