<template>
  <!-- A post's photos / videos. More than one: swipe with the arrows, with a counter and dots. -->
  <div v-if="media.length" class="relative">
    <img v-if="media.length === 1 && media[0].type === 'image'" :src="media[0].url" class="w-full h-56 object-cover" />
    <video v-else-if="media.length === 1 && media[0].type === 'video'" :src="media[0].url" class="w-full h-56 object-cover bg-black" controls />

    <template v-else>
      <div class="w-full h-56 overflow-hidden">
        <div class="flex h-56 transition-transform duration-300" :style="{ transform: `translateX(-${index * 100}%)` }">
          <div v-for="(m, i) in media" :key="m.id || i" class="w-full h-56 shrink-0">
            <img v-if="m.type === 'image'" :src="m.url" class="w-full h-56 object-cover" />
            <video v-else-if="m.type === 'video'" :src="m.url" class="w-full h-56 object-cover bg-black" controls />
          </div>
        </div>
      </div>
      <button type="button" @click="prev" aria-label="Previous photo"
              class="absolute left-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/50 text-white grid place-items-center">
        <ChevronLeft class="w-5 h-5" />
      </button>
      <button type="button" @click="next" aria-label="Next photo"
              class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full bg-black/50 text-white grid place-items-center">
        <ChevronRight class="w-5 h-5" />
      </button>
      <span class="absolute top-3 right-3 bg-black/55 text-white text-[10px] font-black px-2 py-0.5 rounded-full">{{ index + 1 }}/{{ media.length }}</span>
      <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
        <span v-for="(m, i) in media" :key="i" :class="['h-1.5 rounded-full', index === i ? 'bg-white w-3' : 'bg-white/50 w-1.5']"></span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({ media: { type: Array, default: () => [] } });

// Each post keeps its own place in its photos.
const index = ref(0);
const total = computed(() => props.media.length);
const prev = () => { if (index.value > 0) index.value -= 1; };
const next = () => { if (index.value < total.value - 1) index.value += 1; };
</script>
