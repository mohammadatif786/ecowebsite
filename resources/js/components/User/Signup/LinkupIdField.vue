<script setup lang="ts">
// The LinkUp ID row: "@" + monospace field, a Regenerate button, and a live status badge.
// The ID is suggested from the first and last name until the member types their own.
// Availability comes from the server (reserved words, repeats and taken IDs are all "no").
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { icons } from './icons';
import type { LinkupIdState } from './types';

const props = defineProps<{
    modelValue: string;
    firstName: string;
    lastName: string;
    checkUrl: string;
    error?: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'update:state', value: LinkupIdState): void;
}>();

const MIN = 3;
const MAX = 20; // the server accepts 3 to 20 letters, numbers and underscores

const clean = (text: string) => text.toLowerCase().replace(/[^a-z0-9]/g, '');
const first = computed(() => clean(props.firstName));
const last = computed(() => clean(props.lastName));
const hasNames = computed(() => !!(first.value && last.value));

// Suggestions, in the order Regenerate cycles through them: aaliyahbrown, aaliyah_brown, then name + number.
const seed = ref(0);
const edited = ref(false);

const suggestion = computed(() => {
    if (!hasNames.value) return '';
    const joined = first.value + last.value;
    if (seed.value === 0) return joined.slice(0, MAX);
    if (seed.value === 1) return `${first.value}_${last.value}`.slice(0, MAX);
    const suffix = String(((seed.value * 37) % 900) + 100);
    return joined.slice(0, MAX - suffix.length) + suffix;
});

watch(
    suggestion,
    (value) => {
        if (!edited.value) emit('update:modelValue', value);
    },
    { immediate: true },
);

function regenerate() {
    if (!hasNames.value) return;
    seed.value += 1;
    edited.value = false;
    emit('update:modelValue', suggestion.value);
}

function onInput(event: Event) {
    const input = event.target as HTMLInputElement;
    const value = input.value.toLowerCase().replace(/[^a-z0-9_]/g, '').slice(0, MAX);
    input.value = value;
    edited.value = true;
    emit('update:modelValue', value);
}

// ---- availability -------------------------------------------------------------------------

interface Verdict {
    id: string;
    state: 'ok' | 'taken' | 'unknown';
    reason: string | null;
}

const verdict = ref<Verdict | null>(null);
let timer: number | undefined;
let controller: AbortController | undefined;

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function check(id: string) {
    controller?.abort();
    controller = new AbortController();
    try {
        const response = await fetch(props.checkUrl, {
            method: 'POST',
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            body: JSON.stringify({ linkup_id: id }),
        });
        if (response.ok) {
            const body = (await response.json()) as { available: boolean; reason: string | null };
            verdict.value = { id, state: body.available ? 'ok' : 'taken', reason: body.reason };
        } else if (response.status === 422) {
            verdict.value = { id, state: 'taken', reason: 'Use letters, numbers and underscores only.' };
        } else {
            verdict.value = { id, state: 'unknown', reason: null };
        }
    } catch (error) {
        if ((error as Error).name !== 'AbortError') verdict.value = { id, state: 'unknown', reason: null };
    }
}

watch(
    () => props.modelValue,
    (id) => {
        window.clearTimeout(timer);
        controller?.abort();
        if (id.length >= MIN) timer = window.setTimeout(() => check(id), 350);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    window.clearTimeout(timer);
    controller?.abort();
});

const state = computed<LinkupIdState>(() => {
    if (!props.modelValue) return 'waiting';
    if (props.modelValue.length < MIN) return 'short';
    if (verdict.value?.id === props.modelValue) return verdict.value.state;
    return 'checking';
});

watch(state, (value) => emit('update:state', value), { immediate: true });

const GENERIC_TAKEN = 'That LinkUp ID is taken.';

const badge = computed(() => {
    const id = props.modelValue;
    switch (state.value) {
        case 'short':
            return { text: 'Too short', bg: '#FFF4EC', fg: '#8A3B0B', note: 'Use at least 3 characters.' };
        case 'checking':
            return { text: 'Checking', bg: '#FEF3C7', fg: '#92400E', note: `Checking @${id}…` };
        case 'taken': {
            const reason = verdict.value?.reason;
            const note = reason && reason !== GENERIC_TAKEN ? `${reason} Tap Regenerate for another.` : `@${id} is taken. Tap Regenerate for another.`;
            return { text: 'Taken', bg: '#FFF4EC', fg: '#8A3B0B', note };
        }
        case 'ok':
            return { text: 'Available', bg: '#DCFCE7', fg: '#166534', note: `@${id} is available.` };
        case 'unknown':
            return { text: 'Not checked', bg: '#FEF3C7', fg: '#92400E', note: "We couldn't check just now. We'll confirm when you create your account." };
        default:
            return { text: 'Waiting', bg: '#FEF3C7', fg: '#92400E', note: 'Type your first and last name to generate an ID.' };
    }
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label for="a-id" class="text-[14px] font-bold text-[#1E293B]">LinkUp ID</label>

        <div class="flex gap-2.5">
            <div class="relative min-w-0 flex-1">
                <span aria-hidden="true" class="pointer-events-none absolute left-4 top-[15px] text-[16px] font-bold text-[#64748B]">@</span>
                <input
                    id="a-id"
                    :value="modelValue"
                    type="text"
                    autocomplete="username"
                    autocapitalize="off"
                    spellcheck="false"
                    placeholder="yourname"
                    :aria-invalid="!!error"
                    aria-describedby="a-id-status"
                    class="box-border h-[54px] w-full rounded-[14px] border-[1.5px] bg-white pl-9 pr-4 font-mono text-[16px] text-[#0B1530]"
                    :class="error ? 'border-[#DC2626]' : 'border-[#CBD5E1]'"
                    @input="onInput"
                />
            </div>
            <button
                type="button"
                :disabled="!hasNames"
                class="inline-flex h-[54px] flex-none items-center gap-2 rounded-[14px] border-[1.5px] border-[#DCE3F0] bg-white px-4 text-[15px] font-extrabold"
                :class="hasNames ? 'cursor-pointer text-[#0B1530] hover:bg-[#F8FAFC]' : 'cursor-not-allowed text-[#94A3B8]'"
                @click="regenerate"
            >
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="icons.refreshArc" />
                    <path :d="icons.refreshHead" />
                </svg>
                Regenerate
            </button>
        </div>

        <div id="a-id-status" role="status" class="flex flex-wrap items-center gap-2.5">
            <span class="flex-none rounded-full px-2.5 py-1 text-[12px] font-extrabold" :style="{ background: badge.bg, color: badge.fg }">{{ badge.text }}</span>
            <span class="text-[13px] text-[#475569]">{{ badge.note }}</span>
        </div>

        <p v-if="error" role="alert" class="m-0 text-[13px] font-semibold text-[#B91C1C]">{{ error }}</p>
    </div>
</template>
