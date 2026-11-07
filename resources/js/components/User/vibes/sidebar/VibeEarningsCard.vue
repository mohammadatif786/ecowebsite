<template>
  <!-- "My Earnings": commission from tagged sales. Opens the affiliate hub. -->
  <button type="button" class="w-full rounded-2xl p-4 text-white text-left flex items-center justify-between hover:brightness-105 transition"
          style="background:linear-gradient(135deg,#059669,#10b981)" @click="$emit('open')">
    <span>
      <span class="font-black text-sm flex items-center gap-1.5"><TrendingUp class="w-4 h-4" />My Earnings</span>
      <span class="block text-[11px] text-white/80 mt-0.5">Commission from tagged sales</span>
    </span>
    <span class="text-xl font-black">{{ money(total) }}</span>
  </button>
</template>

<script setup>
import { TrendingUp } from 'lucide-vue-next';
import { money } from '../format';

defineProps({ total: { type: Number, default: 0 } });
defineEmits(['open']);
</script>
