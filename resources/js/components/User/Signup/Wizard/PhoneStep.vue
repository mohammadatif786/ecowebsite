<script setup lang="ts">
// Step: phone number, then the 6-digit code texted to it. The page runs the send/verify calls
// (so "Continue" can drive them); this component only shows the boxes.
import { computed } from 'vue';
import { phoneCodes } from './copy';
import * as s from './styles';

const code = defineModel<string>('code', { required: true });
const number = defineModel<string>('number', { required: true });
const otp = defineModel<string>('otp', { required: true });

defineProps<{
    sent: boolean;
    verified: boolean;
    error: string;
    notice: string;
    resendWait: number;
    busy: boolean;
}>();
const emit = defineEmits<{ resend: []; change: [] }>();

const example = computed(() => (phoneCodes.find((c) => c[0] === code.value) ?? phoneCodes[0])[2]);

// The design's codes first, then every other country A–Z by name (hover shows the name).
const POPULAR = 11;
const popularCodes = phoneCodes.slice(0, POPULAR);
const otherCodes = phoneCodes.slice(POPULAR);

const codeSelect =
    'appearance: none; -webkit-appearance: none; flex: none; width: 120px; height: 56px; padding: 0 12px; border-radius: 14px; border: 1.5px solid #CBD5E1; background: #FFFFFF; font-family: inherit; font-size: 16px; font-weight: 600; color: #0B1530; cursor: pointer;';
const linkButton =
    'min-height: 40px; border: none; background: none; padding: 0; font-family: inherit; font-size: 14px; font-weight: 700; color: #1D4ED8; cursor: pointer;';
</script>

<template>
    <div class="flex flex-col gap-2">
        <div v-if="!sent" class="flex flex-col gap-2">
            <label for="f-phone" :style="s.label">Phone number</label>
            <div class="flex gap-2.5">
                <label for="f-code" class="sr-only">Country code</label>
                <select id="f-code" v-model="code" :style="codeSelect">
                    <optgroup label="Popular">
                        <option v-for="[id, dial, , name] in popularCodes" :key="id" :value="id" :title="name">{{ id }}&nbsp;&nbsp;{{ dial }}</option>
                    </optgroup>
                    <optgroup label="All countries">
                        <option v-for="[id, dial, , name] in otherCodes" :key="id" :value="id" :title="name">{{ id }}&nbsp;&nbsp;{{ dial }}</option>
                    </optgroup>
                </select>
                <input
                    id="f-phone"
                    v-model="number"
                    type="tel"
                    inputmode="tel"
                    autocomplete="tel-national"
                    :placeholder="example"
                    :aria-invalid="!!error"
                    style="flex: 1; min-width: 0; height: 56px; box-sizing: border-box; padding: 0 16px; border-radius: 14px; background: #ffffff; font-family: inherit; font-size: 17px; color: #0b1530"
                    :style="{ border: '1.5px solid ' + (error ? '#DC2626' : verified ? '#16A34A' : '#CBD5E1') }"
                />
            </div>
            <span v-if="verified" class="text-[13px] font-bold text-[#166534]">This number is verified.</span>
            <span v-else class="text-[13px] text-[#64748B]">Never shown publicly. Message rates may apply.</span>
        </div>

        <div v-else class="flex flex-col gap-2">
            <label for="f-otp" :style="s.label">Verification code</label>
            <input
                id="f-otp"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                placeholder="••••••"
                :value="otp"
                :aria-invalid="!!error"
                style="height: 68px; box-sizing: border-box; padding: 0 20px; border-radius: 14px; background: #ffffff; font-family: inherit; font-size: 30px; font-weight: 700; letter-spacing: 0.6em; text-align: center; color: #0b1530"
                :style="{ border: '1.5px solid ' + (error ? '#DC2626' : '#CBD5E1') }"
                @input="otp = ($event.target as HTMLInputElement).value.replace(/\D/g, '').slice(0, 6)"
            />
            <div class="flex gap-[18px]">
                <button type="button" :style="linkButton" @click="emit('change')">Change number</button>
                <button
                    type="button"
                    :disabled="resendWait > 0 || busy"
                    :style="linkButton + (resendWait > 0 ? ' color: #94A3B8; cursor: default;' : '')"
                    @click="emit('resend')"
                >
                    {{ resendWait > 0 ? `Resend code in ${resendWait}s` : 'Resend code' }}
                </button>
            </div>
        </div>

        <p v-if="error" :style="s.fieldError" role="alert">{{ error }}</p>
        <p v-else-if="notice" role="status" class="m-0 text-[13px] font-semibold text-[#166534]">{{ notice }}</p>
    </div>
</template>
