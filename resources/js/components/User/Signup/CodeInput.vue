<script setup lang="ts">
// One numeric field for a one-time code (email code now, phone code in the wizard later).
// Matches the onboarding design: 56px high, 14px radius, wide letter spacing, blue focus ring.
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        length?: number;
        error?: string | null;
        disabled?: boolean;
        id?: string;
        label?: string;
        /** Keep the label for screen readers only (the surrounding panel already explains the field). */
        hideLabel?: boolean;
    }>(),
    { length: 6, error: null, disabled: false, id: 'signup-code', label: 'Verification code', hideLabel: false },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'complete', value: string): void;
}>();

const hasError = computed(() => !!props.error);

function onInput(event: Event) {
    const digits = (event.target as HTMLInputElement).value.replace(/\D/g, '').slice(0, props.length);
    (event.target as HTMLInputElement).value = digits;
    emit('update:modelValue', digits);
    if (digits.length === props.length) {
        emit('complete', digits);
    }
}
</script>

<template>
    <div class="flex min-w-0 flex-col gap-1.5">
        <label :for="id" :class="hideLabel ? 'sr-only' : 'text-[14px] font-bold text-[#1E293B]'">{{ label }}</label>
        <input
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :maxlength="length"
            :aria-invalid="hasError"
            :aria-describedby="hasError ? `${id}-error` : undefined"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            autocomplete="one-time-code"
            placeholder="••••••"
            class="box-border h-14 w-full min-w-0 rounded-[14px] border-[1.5px] bg-white px-4 text-center text-[24px] font-bold tracking-[0.5em] text-[#0B1530] disabled:cursor-not-allowed disabled:bg-[#F1F5F9]"
            :class="hasError ? 'border-[#DC2626]' : 'border-[#CBD5E1]'"
            @input="onInput"
        />
        <p v-if="hasError" :id="`${id}-error`" role="alert" class="m-0 text-[13px] font-semibold text-[#B91C1C]">{{ error }}</p>
    </div>
</template>
