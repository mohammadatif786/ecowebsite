<template>
  <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center p-4 z-50" role="dialog" aria-modal="true" @click.self="$emit('close')">
    <div class="bg-white w-full max-w-lg rounded-2xl p-4 shadow-glass">
      <div class="flex items-center gap-2 mb-2">
        <i data-lucide="coins" class="w-5 h-5"></i>
        <div class="font-semibold">Buy Coins</div>
        <button class="ml-auto p-1 rounded-lg hover:bg-slate-100" @click="$emit('close')">
          <i data-lucide="x"></i>
        </button>
      </div>
      <div class="space-y-4">
        <div class="grid sm:grid-cols-2 gap-3">
          <label
            v-for="pack in COIN.packs"
            :key="pack.id"
            class="group relative border rounded-2xl p-4 cursor-pointer hover:shadow-glass transition"
            :class="{ 'border-linkup-blue shadow-glass': selectedPack.id === pack.id }"
          >
            <span
              v-if="pack.best"
              class="absolute -top-2 right-3 text-[10px] px-2 py-0.5 rounded-full bg-linkup-blue text-white"
            >
              BEST
            </span>
            <input
              type="radio"
              name="pack"
              :value="pack.id"
              v-model="selectedPackId"
              class="sr-only"
            />
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-linkup-blue/10 flex items-center justify-center">
                <i data-lucide="coins" class="w-5 h-5 text-linkup-blue"></i>
              </div>
              <div class="min-w-0">
                <div class="font-semibold truncate">{{ sanitize(pack.name) }}</div>
                <div class="text-xs text-slate-500">{{ pack.coins.toLocaleString() }} WCO</div>
              </div>
              <div class="ml-auto text-right">
                <div class="font-semibold">{{ fmtLocalFromUSD(pack.priceCents, current().currency) }}</div>
                <div class="text-xs text-slate-500">
                  ~ {{ Math.round(pack.coins / (pack.priceCents / 100 / (FX[current().currency] || 1))).toLocaleString() }} coins per {{ current().currency }}
                </div>
              </div>
            </div>
          </label>
        </div>
        <div class="grid grid-cols-[auto_1fr_auto] items-center gap-2 border rounded-2xl p-2">
          <button class="px-3 py-2 rounded-xl bg-white border" @click="qty = Math.max(1, qty - 1)">−</button>
          <input
            v-model.number="qty"
            type="number"
            min="1"
            class="w-full text-center border rounded-xl py-2"
          />
          <button class="px-3 py-2 rounded-xl bg-white border" @click="qty++">+</button>
        </div>
        <div class="rounded-2xl p-4 bg-linkup-dark text-white">
          <div class="flex items-center gap-3">
            <i data-lucide="sparkles" class="w-5 h-5 text-white/80"></i>
            <div class="flex-1">
              <div class="text-sm">You’ll receive</div>
              <div class="text-xl font-bold">{{ (selectedPack.coins * qty).toLocaleString() }} WCO</div>
            </div>
            <div class="text-right">
              <div class="text-sm opacity-80">Total</div>
              <div class="text-xl font-bold">{{ fmtLocalFromUSD(selectedPack.priceCents * qty, current().currency) }}</div>
            </div>
          </div>
        </div>
        <button
          class="w-full px-4 py-3 rounded-2xl bg-linkup-lime text-linkup-dark font-semibold"
          @click="buyCoins(selectedPack, qty)"
        >
          Buy Now
        </button>
        <div class="text-xs text-slate-500 text-center">Secure checkout (demo) • Coins post instantly</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue';

const current = inject('current');
const sanitize = inject('sanitize');
const fmtLocalFromUSD = inject('fmtLocalFromUSD');
const buyCoins = inject('buyCoins');
const COIN = inject('COIN');
const FX = inject('FX');
defineEmits(['close']);

const selectedPackId = ref(COIN.packs.find((p) => p.best)?.id || COIN.packs[0].id);
const qty = ref(1);

const selectedPack = computed(() => COIN.packs.find((p) => p.id === selectedPackId.value) || COIN.packs[0]);

onMounted(() => {
  window.$lucide.createIcons();
});
</script>