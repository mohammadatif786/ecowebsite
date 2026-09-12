<template>
  <button @click="openLive" class="card overflow-hidden w-full text-left group">
    <div class="h-32 relative overflow-hidden bg-slate-900">
      <img :src="live.thumb || fallbackImage" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-lg" aria-hidden="true" />
      <img :src="live.thumb || fallbackImage" class="relative h-full w-full object-contain" />
      <span class="absolute top-2 left-2 bg-red-600 text-white text-[11px] font-black px-2 py-0.5 rounded-full flex items-center gap-1">
        <span class="w-1.5 h-1.5 bg-white rounded-full"></span>LIVE
      </span>
      <span class="absolute bottom-2 left-2 bg-black/50 text-white text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
        <i data-lucide="users" class="w-3 h-3"></i>{{ num(live.viewers) }}
      </span>
    </div>
    <div class="p-3">
      <p class="font-black text-sm">{{ live.title }}</p>
      <p class="text-xs text-slate-500">{{ live.host }} · {{ live.category }}</p>
    </div>
  </button>
</template>

<script setup>
const fallbackImage = '/assets/images/default-image.png';
const props = defineProps({
  live: Object
});

const num = (n) => Number(n).toLocaleString();

const openLive = () => {
  const publicId = props.live?.public_id || props.live?.publicId;

  if (!publicId) {
    console.error('Live stream card is missing public_id.', props.live);
    window.toast?.('This stream link is unavailable. Please refresh and try again.', 'error');
    return;
  }

  window.location.href = route('new_frontend.live.watch', publicId);
};
</script>
