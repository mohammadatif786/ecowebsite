<template>
  <!-- The event or product tagged on a post: tap for tickets / to shop. -->
  <button v-if="tag" type="button" class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 p-2 mt-3 text-left hover:border-lkblue transition" @click="$emit('open', tag)">
    <span class="relative shrink-0">
      <img :src="tag.image" alt="" class="h-12 w-12 rounded-xl object-cover bg-slate-100" />
      <span class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full" :style="{ background: isEvent ? '#2563eb' : '#8b5cf6' }">{{ isEvent ? '🎟️' : '🛍️' }}</span>
    </span>
    <span class="flex-1 min-w-0">
      <span class="block font-black text-sm truncate">{{ tag.title }}</span>
      <span class="block text-[11px] text-slate-500 truncate">{{ isEvent ? `${tag.date || 'Date TBA'} · ${tag.location || 'Location TBA'}` : (tag.seller || 'Marketplace') }}</span>
    </span>
    <span class="btn btn-primary px-3 py-2 text-xs shrink-0">{{ isEvent ? 'Get Tickets' : 'Shop' }} · {{ tag.price ? money(tag.price) : 'Free' }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { money } from '../format';

const props = defineProps({ tag: { type: Object, default: null } });
defineEmits(['open']);
const isEvent = computed(() => props.tag?.kind === 'event');
</script>
