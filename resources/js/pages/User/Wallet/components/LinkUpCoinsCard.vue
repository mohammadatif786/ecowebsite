<template>
  <div class="rounded-3xl bg-gradient-to-r from-amber-400 to-amber-500 text-white p-6 relative overflow-hidden shadow-lg">
    <i data-lucide="gem" class="absolute -right-6 -bottom-6 w-32 h-32 opacity-10"></i>
    <p class="text-white/80 text-xs font-black tracking-wide flex items-center gap-2 relative z-10">
      <i data-lucide="gem" class="w-4 h-4"></i>
      LINK UP COINS
    </p>
    <p class="text-4xl font-black mt-2 relative z-10">{{ Number(coins || 0).toLocaleString() }}</p>
    <button
      @click="$emit('buy-more')"
      class="btn bg-white/90 hover:bg-white text-amber-700 mt-4 px-5 py-2.5 flex items-center gap-2 relative z-10 shadow transition"
    >
      <i data-lucide="shopping-bag" class="w-4 h-4"></i>
      Buy More
    </button>
  </div>
</template>

<script setup>
defineProps({
  coins: { type: Number, default: 0 },
});

defineEmits(['buy-more']);
</script>
