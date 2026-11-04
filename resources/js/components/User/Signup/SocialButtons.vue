<script setup lang="ts">
// "or continue with Google / Facebook". Shown as in the design but switched off until those
// sign-ins exist, so nobody taps into a dead end.
const providers = [
    { name: 'Google', mark: 'G' },
    { name: 'Facebook', mark: 'f' },
];
</script>

<template>
    <div class="flex flex-col gap-[18px]">
        <div class="flex items-center gap-3.5 text-[12px] font-bold uppercase tracking-[0.06em] text-[#64748B]">
            <div class="h-px flex-1 bg-[#DCE3F0]"></div>
            or continue with
            <div class="h-px flex-1 bg-[#DCE3F0]"></div>
        </div>

        <div class="grid grid-cols-2 gap-2.5 max-[480px]:grid-cols-1">
            <button
                v-for="provider in providers"
                :key="provider.name"
                type="button"
                disabled
                :title="`${provider.name} sign-in is coming soon`"
                class="flex h-[52px] cursor-not-allowed items-center justify-center gap-2.5 rounded-[14px] border-[1.5px] border-[#DCE3F0] bg-white text-[16px] font-extrabold text-[#0B1530] opacity-60"
            >
                <span aria-hidden="true" class="flex h-[22px] w-[22px] items-center justify-center rounded-full border-2 border-[#0B1530] text-[12px] font-extrabold">{{ provider.mark }}</span>
                {{ provider.name }}
                <span class="rounded-full bg-[#EEF2F8] px-2 py-0.5 text-[11px] font-extrabold text-[#64748B]">Coming soon</span>
            </button>
        </div>
    </div>
</template>
