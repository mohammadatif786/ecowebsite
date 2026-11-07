<template>
  <div v-if="status !== 'approved'" :class="['card p-4 flex items-center gap-3 border-2', tone.border]">
    <span :class="['w-11 h-11 rounded-xl grid place-items-center shrink-0', tone.icon]"><i :data-lucide="tone.lucide" class="w-5 h-5"></i></span>
    <div class="flex-1 min-w-0">
      <p class="font-black text-sm">{{ tone.title }}</p>
      <p class="text-[12px] text-slate-500 font-bold leading-snug">{{ tone.text }}</p>
    </div>
    <button v-if="status !== 'pending'" @click="$emit('verify')" class="btn btn-primary px-4 py-2 text-sm shrink-0">{{ status === 'rejected' ? 'Resubmit' : 'Verify now' }}</button>
    <span v-else class="rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-[11px] font-black shrink-0">In review</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ status: { type: String, default: 'none' } });
defineEmits(['verify']);

const tone = computed(() => ({
  pending: { title: 'Identity check in review', text: 'Money features unlock as soon as you’re approved.', lucide: 'hourglass', icon: 'bg-amber-100 text-amber-600', border: 'border-amber-100' },
  rejected: { title: 'Identity check not approved', text: 'Please resubmit with clear photos of your ID.', lucide: 'shield-alert', icon: 'bg-rose-100 text-rose-600', border: 'border-rose-100' },
}[props.status] || { title: 'Verify your identity', text: 'A quick check unlocks Top Up, Send, Request, Coins and bank transfers.', lucide: 'shield-check', icon: 'bg-sky-100 text-lkblue2', border: 'border-sky-100' }));
</script>
