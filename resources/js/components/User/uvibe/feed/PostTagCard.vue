<template>
  <!-- The product or event the poster tagged. Tapping it opens the shop / ticket window. -->
  <button v-if="tag" type="button" @click="$emit('open', tag)"
          class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 p-2 mt-3 text-left hover:border-[#2f9bef] transition">
    <div class="relative shrink-0">
      <img v-if="tag.image" :src="tag.image" class="h-12 w-12 rounded-xl object-cover" />
      <div v-else class="h-12 w-12 rounded-xl bg-slate-100"></div>
      <span class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full"
            :style="{ background: isEvent ? '#2563eb' : '#8b5cf6' }">{{ isEvent ? '🎟️' : '🛍️' }}</span>
    </div>
    <div class="flex-1 min-w-0">
      <p class="font-black text-sm truncate">{{ tag.title }}</p>
      <p class="text-[11px] text-slate-500 truncate">
        <template v-if="isEvent">{{ fmtDate(tag.date) }} · {{ tag.location }}</template>
        <template v-else>{{ tag.seller }}</template>
      </p>
    </div>
    <span class="btn btn-primary px-3 py-2 text-xs shrink-0">{{ isEvent ? 'Get Tickets' : 'Shop' }} · {{ tag.price ? money(tag.price) : 'Free' }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { fmtDate, money } from '../../../../services/uvibeFormat';

const props = defineProps({ tag: { type: Object, default: null } });
defineEmits(['open']);

const isEvent = computed(() => props.tag?.kind === 'event');
</script>
