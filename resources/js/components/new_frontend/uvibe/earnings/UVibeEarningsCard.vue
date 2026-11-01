<template>
  <!-- "My Earnings": what your tagged sales have earned you; tap for the Promote & Earn window (payouts to your wallet). -->
  <button type="button" @click="open" class="w-full rounded-2xl p-4 text-white text-left flex items-center justify-between"
          style="background:linear-gradient(135deg,#059669,#10b981)">
    <div>
      <p class="font-black text-sm flex items-center gap-1.5"><TrendingUp class="w-4 h-4" />My Earnings</p>
      <p class="text-[11px] text-white/80 mt-0.5">Commission from tagged sales</p>
    </div>
    <p class="text-xl font-black">{{ money(total) }}</p>
  </button>

  <AffiliateHubModal ref="hubRef" :items="items" :stats="stats" />
</template>

<script setup>
import { computed, ref } from 'vue';
import { TrendingUp } from 'lucide-vue-next';
import AffiliateHubModal from '../../modals/AffiliateHubModal.vue';
import { money } from '../../../../services/uvibeFormat';

// stats: the earnings summary the server sends with the page (pending / available / paid …).
// items: the products and events you can promote. Releasing or cashing out in the window reloads
// `earningsStats`, so the page must pass that same prop here for the number to follow along.
const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  items: { type: Array, default: () => [] },
});

const hubRef = ref(null);

// The same total the Promote & Earn window shows as "Total commission".
const total = computed(() => Number(props.stats?.total_commission || 0));

const open = () => hubRef.value?.open('earnings');

defineExpose({ open });
</script>
