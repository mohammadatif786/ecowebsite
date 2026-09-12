<template>
  <article class="card overflow-hidden">
    <div class="flex items-center gap-3 p-4">
      <img :src="p.avatar" class="w-11 h-11 rounded-full object-cover" />
      <div class="flex-1 min-w-0">
        <p class="font-black text-sm flex items-center gap-1.5">
          {{ p.handle }}
          <span v-if="p.org" class="text-[9px] font-black text-white px-1.5 py-0.5 rounded-full" style="background:var(--lk-blue)">
            {{ p.org.toUpperCase() }}
          </span>
        </p>
        <p class="text-xs text-slate-500 flex items-center gap-1">
          <i data-lucide="map-pin" class="w-3 h-3"></i>{{ p.location }}
          <template v-if="p.kind === 'reel'">
            · <span class="text-lkblue2 font-bold">Reel</span>
          </template>
        </p>
      </div>
      <span v-if="p.shoppable" class="text-xs font-black text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full flex items-center gap-1">
        <i data-lucide="shopping-bag" class="w-3 h-3"></i>Shop
      </span>
      <button @click="showToast('More')" class="text-slate-400"><i data-lucide="more-horizontal" class="w-5 h-5"></i></button>
    </div>
    
    <div class="relative bg-black">
      <video v-if="p.realVideo" :src="p.media" controls class="w-full max-h-[560px] object-contain bg-black"></video>
      <img v-else :src="p.media" class="w-full max-h-[560px] object-cover" />
      <span v-if="p.kind === 'reel' && !p.realVideo" class="absolute top-3 right-3 bg-black/50 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
        <i data-lucide="play" class="w-3 h-3"></i>Reel
      </span>
    </div>
    
    <div class="p-4">
      <div class="flex items-center gap-5 mb-2">
        <button class="flex items-center gap-1.5 font-black text-slate-700">
          <i data-lucide="heart" class="w-5 h-5"></i><span>{{ num(p.likes) }}</span>
        </button>
        <button @click="showToast('💬 Comments')" class="flex items-center gap-1.5 font-black text-slate-700">
          <i data-lucide="message-circle" class="w-5 h-5"></i><span>{{ num(p.comments) }}</span>
        </button>
        <button class="flex items-center gap-1.5 font-black text-slate-700">
          <i data-lucide="send" class="w-5 h-5"></i>
        </button>
        <button class="ml-auto flex items-center gap-1.5 text-white bg-gradient-to-r from-orange-500 to-amber-500 px-3 py-1.5 rounded-full text-sm font-black">
          <i data-lucide="zap" class="w-4 h-4"></i>Big Up {{ num(p.bigup || 0) }}
        </button>
        <button @click="showToast('🔖 Saved')" class="text-slate-500"><i data-lucide="bookmark" class="w-5 h-5"></i></button>
      </div>
      <p class="text-sm"><span class="font-black">{{ p.handle }}</span> {{ p.caption }}</p>
      <p v-if="p.sound" class="text-xs text-slate-500 mt-1 flex items-center gap-1">
        <i data-lucide="music" class="w-3 h-3"></i>{{ p.sound }}
      </p>
      
      <!-- VibeTagCard -->
      <button v-if="p.tag" @click="openVibeTag" class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 p-2 mt-3 text-left hover:border-lkblue transition">
        <div class="relative shrink-0">
          <img :src="p.tag.image" class="h-12 w-12 rounded-xl object-cover" />
          <span class="absolute -top-1 -left-1 text-white text-[9px] font-black px-1 py-0.5 rounded-full" :style="{ background: p.tag.kind === 'event' ? '#2563eb' : '#8b5cf6' }">
            {{ p.tag.kind === 'event' ? '🎟️' : '🛍️' }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-black text-sm truncate">{{ p.tag.title }}</p>
          <p class="text-[11px] text-slate-500 truncate">
            {{ p.tag.kind === 'event' ? `${p.tag.date} · ${p.tag.location || 'Location TBA'}` : (p.tag.seller || 'Marketplace') }}
          </p>
        </div>
        <span class="btn btn-primary px-3 py-2 text-xs shrink-0">
          {{ p.tag.kind === 'event' ? 'Get Tickets' : 'Shop' }} · {{ p.tag.price ? money(p.tag.price) : 'Free' }}
        </span>
      </button>
    </div>
    
    <VibeTagModal ref="vibeTagModalRef" />
  </article>
</template>

<script setup>
import { ref } from 'vue';
import VibeTagModal from '../modals/VibeTagModal.vue';
const props = defineProps({
  p: Object
});

const vibeTagModalRef = ref(null);

const num = (n) => Number(n).toLocaleString();
const money = (n) => '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const openVibeTag = () => {
  if (vibeTagModalRef.value && props.p.tag) {
    vibeTagModalRef.value.open(props.p.tag);
  }
};

const showToast = (msg) => {
  if (window.toast) window.toast(msg);
};
</script>
