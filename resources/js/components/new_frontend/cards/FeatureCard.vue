<template>
  <Link :href="route('new_frontend.' + dest)"
    :class="['card overflow-hidden group cursor-pointer block h-full', variant === 'large' ? 'min-h-[340px]' : '']">

    <div :class="['relative overflow-hidden bg-slate-900', variant === 'large' ? 'h-110' : 'h-40']">
      <img :src="img || fallbackImage" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-45 blur-xl" aria-hidden="true" />
      <img :src="img || fallbackImage" class="relative h-full w-full object-contain transition duration-700 group-hover:scale-[1.02]" />
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

      <!-- Top Tag -->
      <span class="absolute top-4 left-4 text-white text-[11px] font-black px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-lg" :style="{ background: color }">
        <i v-if="tag === 'Featured Live'" data-lucide="radio" class="w-3 h-3"></i>
        <i v-else-if="tag === 'Trending Event'" data-lucide="flame" class="w-3 h-3"></i>
        <i v-else data-lucide="shopping-bag" class="w-3 h-3"></i>
        {{ tag }}
      </span>

      <div class="absolute bottom-6 left-6 right-6 text-white">
        <!-- Date for large variant -->
        <p v-if="variant === 'large' && date" class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">
          {{ date }}
        </p>

        <h3 :class="['font-black leading-tight drop-shadow-md', variant === 'large' ? 'text-3xl' : 'text-lg']">
          {{ title }}
        </h3>
        <p :class="['font-semibold opacity-90', variant === 'large' ? 'text-sm mt-1' : 'text-[11px]']">
          {{ sub }}
        </p>

        <!-- CTA for large variant -->
        <div v-if="variant === 'large'" class="mt-6 flex items-center gap-3">
          <span class="bg-lkblue2 hover:bg-lkblue transition text-white px-6 py-2.5 rounded-xl font-black text-sm flex items-center gap-2 shadow-lg">
            {{ cta }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </span>
        </div>
      </div>

      <!-- Small arrow for mini variants -->
      <div v-if="variant !== 'large'" class="absolute bottom-6 right-6 w-9 h-9 rounded-full bg-white/10 backdrop-blur-md border border-white/20 grid place-items-center text-white group-hover:bg-white group-hover:text-black transition duration-300">
        <i data-lucide="chevron-right" class="w-5 h-5"></i>
      </div>
    </div>

    <!-- Legacy CTA area - removed for clean overlay design per screenshot -->
    <!-- <div v-if="variant !== 'large'" class="p-3">
      <span class="btn btn-primary inline-block px-4 py-2 text-sm">{{ cta }} →</span>
    </div> -->
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const fallbackImage = '/assets/images/default-image.png';

defineProps({
  tag: String,
  title: String,
  sub: String,
  img: String,
  cta: String,
  dest: String,
  color: String,
  variant: { type: String, default: 'mini' },
  date: String
});

onMounted(() => {
  if (window.lucide) window.lucide.createIcons();
});
</script>
