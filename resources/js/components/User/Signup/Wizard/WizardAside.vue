<script setup lang="ts">
// The blue panel's words during the wizard: a pill, a big headline and a sentence, then
// (through the slot) the live profile card.
defineProps<{ tag: string; head: string; side: string }>();
</script>

<template>
    <div class="relative flex flex-col gap-4">
        <span class="self-start rounded-full bg-white/[0.14] px-3.5 py-2 text-[13px] font-bold uppercase tracking-[0.04em]">{{ tag }}</span>
        <h2 class="m-0 whitespace-pre-line text-[46px] font-extrabold leading-[1.05] tracking-[-0.03em] max-[900px]:text-[30px]">{{ head }}</h2>
        <p class="lu-aside-hide m-0 max-w-[420px] text-[17px] leading-[1.55] text-[#DBE6FF]">{{ side }}</p>
    </div>
    <slot />
</template>
