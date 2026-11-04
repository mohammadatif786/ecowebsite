<template>
  <!-- Make the post shoppable: tag a Marketplace product or a ticketed event. -->
  <div class="rounded-2xl border border-slate-200 p-3">
    <div class="flex items-center justify-between">
      <span class="font-bold text-sm">🛍️ Tag a product or 🎟️ event</span>
      <button type="button" @click="picker?.open()" class="btn btn-primary px-3 py-1.5 text-xs">{{ modelValue ? 'Change' : 'Tag' }}</button>
    </div>

    <div class="mt-2">
      <div v-if="modelValue" class="flex items-center gap-2 rounded-xl bg-slate-50 p-2">
        <img :src="modelValue.image || 'https://picsum.photos/200'" class="h-10 w-10 rounded-lg object-cover" />
        <div class="flex-1 min-w-0">
          <p class="font-black text-sm truncate">{{ modelValue.kind === 'event' ? '🎟️ ' : '' }}{{ modelValue.title }}</p>
          <p class="text-[11px] text-slate-500">{{ modelValue.price ? money(modelValue.price) : 'Free' }} · {{ earning }}</p>
        </div>
        <button type="button" @click="$emit('update:modelValue', null)" class="text-rose-500" aria-label="Remove tag"><X class="w-4 h-4" /></button>
      </div>
      <p v-else class="text-[11px] text-slate-400">
        Make your U Vibe post shoppable — classmates buy the product or grab tickets right from it, and you earn commission, same as Vibes.
      </p>
    </div>

    <p class="text-[11px] text-slate-400 mt-2">
      Selling a textbook or something else?
      <button type="button" class="font-black text-lkblue underline" @click="goMarketplace">List it on the Marketplace</button>
      first, then tag it here.
    </p>

    <!-- Outside the composer so it is never cut off by it. -->
    <Teleport to="body">
      <VibeTagProductModal ref="picker" :items="items" @selected="(item) => $emit('update:modelValue', item)" />
    </Teleport>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import VibeTagProductModal from '../../modals/VibeTagProductModal.vue';

const props = defineProps({
  modelValue: { type: Object, default: null }, // the tagged product or event
  items: { type: Array, default: () => [] }, // products and events that can be tagged
});
defineEmits(['update:modelValue']);

const picker = ref(null);
const goMarketplace = () => router.visit(route('user.marketplace'));
const money = (n) => '$' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// What the poster earns when someone buys through the tag.
const earning = computed(() => {
  const t = props.modelValue;
  if (!t) return '';
  const mode = t.commMode || (t.commission > 0 ? 'pct' : 'none');
  if (mode === 'none') return 'no commission';
  if (mode === 'flat') return `you earn ${money(t.commFlat || 0)} per sale`;
  return `you earn ${t.commission || 0}%`;
});
</script>
